<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Summary_payrolls extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('report/summary_payrolls');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_group = base64_decode($this->input->get('filter_group'));
        $filter_position = $this->input->get('filter_position');
        $username = $this->session->username;
        $filter_group_ex = explode(",", trim($filter_group));

        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        // Query untuk payrolls reguler
        $this->db->select('
            a.approved,
            a.approved_to,
            a.approved_by,
            a.approved_date,
            b.departement_id,
            b.departement_sub_id,
            b.group_id,
            c.name as departement_name, 
            d.name as departement_sub_name, 
            e.name as group_name,
            g.name as position_name,
            COUNT(b.id) as employee, 
            SUM(a.net_income) as income,
            "REGULER" as payroll_type');
        $this->db->from('payrolls a');
        $this->db->join('employees b', "a.employee_id = b.id");
        $this->db->join('departements c', "b.departement_id = c.id");
        $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
        $this->db->join('groups e', "b.group_id = e.id");
        $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
        $this->db->join('positions g', "b.position_id = g.id");
        $this->db->where('a.deleted', 0);
        $this->db->where('a.period_start =', $period_start);
        $this->db->where('a.period_end =', $period_end);
        if($filter_group != ""){
            $this->db->where_in('e.name', $filter_group_ex);
        }
        $this->db->like('b.id', $filter_employee);
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        
        if($filter_position && $filter_position != '' && $filter_position != 'undefined') {
            $this->db->where('b.position_id', $filter_position);
        }
        
        $this->db->group_by(array("c.id", "d.id", "e.id", "g.id"));
        
        $query1 = $this->db->get_compiled_select();
        
        // Reset query builder
        $this->db->reset_query();
        
        // Query untuk payroll_harian_lepas
        $this->db->select('
            a.approved,
            a.approved_to,
            a.approved_by,
            a.approved_date,
            b.departement_id,
            b.departement_sub_id,
            b.group_id,
            c.name as departement_name, 
            d.name as departement_sub_name, 
            e.name as group_name,
            g.name as position_name,
            COUNT(b.id) as employee, 
            SUM(a.net_income) as income,
            "HARIAN_LEPAS" as payroll_type');
        $this->db->from('payroll_harian_lepas a');
        $this->db->join('employees b', "a.employee_id = b.id");
        $this->db->join('departements c', "b.departement_id = c.id");
        $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
        $this->db->join('groups e', "b.group_id = e.id");
        $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
        $this->db->join('positions g', "b.position_id = g.id");
        $this->db->where('a.period_start =', $period_start);
        $this->db->where('a.period_end =', $period_end);
        if($filter_group != ""){
            $this->db->where_in('e.name', $filter_group_ex);
        }
        $this->db->like('b.id', $filter_employee);
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        
        if($filter_position && $filter_position != '' && $filter_position != 'undefined') {
            $this->db->where('b.position_id', $filter_position);
        }
        
        $this->db->group_by(array("c.id", "d.id", "e.id", "g.id"));
        
        $query2 = $this->db->get_compiled_select();
        
        // Reset query builder
        $this->db->reset_query();
        
        // Gabungkan kedua query dengan UNION
        $union_query = "($query1) UNION ALL ($query2)";
        
        // Query final untuk menggabungkan dan mengelompokkan hasil
        $final_query = "SELECT 
            approved,
            approved_to,
            approved_by,
            approved_date,
            departement_id,
            departement_sub_id,
            group_id,
            departement_name, 
            departement_sub_name, 
            group_name,
            position_name,
            SUM(employee) as employee, 
            SUM(income) as income,
            GROUP_CONCAT(DISTINCT payroll_type ORDER BY payroll_type SEPARATOR ', ') as payroll_type
        FROM ($union_query) as combined_data
        GROUP BY departement_id, departement_sub_id, group_id, position_name
        ORDER BY departement_name ASC, departement_sub_name ASC, position_name ASC, SUM(income) ASC";
        
        $records = $this->db->query($final_query)->result_array();

        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_summary_payroll_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_position = $this->input->get('filter_position');
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            // Query untuk payrolls reguler
            $this->db->select('a.*, b.bank_branch, b.bank_no, b.national_id, "REGULER" as payroll_type');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('privilege_groups c', "b.group_id = c.group_id and c.username = '$username' and c.status = '1'");
            $this->db->where('a.period_start', $period_start);
            $this->db->where('a.period_end', $period_end);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.employee_id', $filter_employee);
            
            if($filter_position && $filter_position != 'undefined') {
                $this->db->where('b.position_id', $filter_position);
            }
            
            $this->db->order_by('a.name', 'ASC');
            $payrolls_reguler = $this->db->get()->result_array();
            
            // Reset query builder
            $this->db->reset_query();
            
            // Query untuk payroll_harian_lepas
            $this->db->select('a.*, b.bank_branch, b.bank_no, b.national_id, "HARIAN_LEPAS" as payroll_type');
            $this->db->from('payroll_harian_lepas a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('privilege_groups c', "b.group_id = c.group_id and c.username = '$username' and c.status = '1'");
            $this->db->where('a.period_start', $period_start);
            $this->db->where('a.period_end', $period_end);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.employee_id', $filter_employee);
            
            if($filter_position && $filter_position != 'undefined') {
                $this->db->where('b.position_id', $filter_position);
            }
            
            $this->db->order_by('a.name', 'ASC');
            $payrolls_harian_lepas = $this->db->get()->result_array();
            // die($this->db->last_query());
            
            // Gabungkan kedua array
            $payrolls = array_merge($payrolls_reguler, $payrolls_harian_lepas);
            
            // Urutkan berdasarkan nama
            usort($payrolls, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $bpjsComs = $this->crud->reads('bpjs', ['status' => 0]);

            //Config Page
            $rows = 25;
            $page = ceil(count($payrolls) / $rows);

            $division = $this->crud->read("divisions", [], ["id" => $filter_division]);
            $departement = $this->crud->read("departements", [], ["id" => $filter_departement]);
            $departement_sub = $this->crud->read("departement_subs", [], ["id" => $filter_departement_sub]);
            $position = $this->crud->read("positions", [], ["id" => $filter_position]);

            if (empty($division->id)) {
                $division_name = "ALL";
            } else {
                $division_name = $division->name;
            }

            if (empty($departement->id)) {
                $departement_name = "ALL";
            } else {
                $departement_name = $departement->name;
            }

            if (empty($departement_sub->id)) {
                $departement_sub_name = "ALL";
            } else {
                $departement_sub_name = $departement_sub->name;
            }

            if (empty($position->id)) {
                $position_name = "ALL";
            } else {
                $position_name = $position->name;
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                    <body>';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            $grand_total = 0;
            for ($i = 0; $i < $page; $i++) {
                $offset = ($i * $rows);
                $records = array_slice($payrolls, $offset, $rows);

                $htmlBpjsHeader = "";
                foreach ($bpjsComs as $bpjsCom) {
                    $htmlBpjsHeader .= '<th style="text-align:center;">'.$bpjsCom->number.'</th>';
                }

                $html .= '  <center>
                                <div style="float: left; font-size: 12px; text-align: left;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                                <img src="' . $config->favicon . '" width="30">
                                            </td>
                                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                                <b>' . $config->name . '</b><br>
                                                <small>' . $config->description . '</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float: right; font-size: 12px; text-align: right;">
                                    Print Date ' . date("d M Y H:i:s") . ' <br>
                                    Print By ' . $this->session->username . ' <br>
                                    page ' . $hal . '/' . $page . '
                                </div>
                            </center><br><br><br>
                            <center>
                                <h3 style="margin:0;">REPORT SUMMARY PAYROLL BY DEPARTEMENT & GROUP</h3>
                            </center><br>
                            <table style="font-size:10px;">
                                <tr>
                                    <td width="100">Period</td>
                                    <td width="20">:</td>
                                    <td width="300"><b>' . $filter_from . ' to ' . $filter_to . '</b></td>
                                </tr>
                                <tr>
                                    <td width="100">Plant</td>
                                    <td width="20">:</td>
                                    <td width="300"><b>' . $division_name . '</b></td>
                                </tr>
                                <tr>
                                    <td>Departement</td>
                                    <td>:</td>
                                    <td><b>' . $departement_name . '</b></td>
                                </tr>
                                <tr>
                                    <td>Departement Sub</td>
                                    <td>:</td>
                                    <td><b>' . $departement_sub_name . '</b></td>
                                </tr>
                            </table>
                            <br>
                            <table id="customers" border="1">
                                <tr>
                                    <th rowspan="2" width="20">No</th>
                                    <th rowspan="2" style="text-align:center;">Employee ID</th>
                                    <th rowspan="2" style="text-align:center;">National ID</th>
                                    <th rowspan="2" style="text-align:center;">Employee Name</th>
                                    <th rowspan="2" style="text-align:center;">Position</th>
                                    <th rowspan="2" style="text-align:center;">Group</th>
                                    <th rowspan="2" style="text-align:center;">WD</th>
                                    <th rowspan="2" style="text-align:center;">HKW</th>
                                    <th rowspan="2" style="text-align:center;">Salary</th>
                                    <th colspan="7" style="text-align:center;">Allowance</th>
                                    <th rowspan="2" style="text-align:center;">Correction <br> Plus</th>
                                    <th rowspan="2" style="text-align:center;">Total <br> Income</th>
                                    <th colspan="8" style="text-align:center;">Deduction</th>
                                    <th rowspan="2" style="text-align:center;">Loan</th>
                                    <th rowspan="2" style="text-align:center;">Correction <br> Minus</th>
                                    <th rowspan="2" style="text-align:center;">Total<br>Deduction</th>
                                    <th rowspan="2" style="text-align:center;">TER</th>
                                    <th rowspan="2" style="text-align:center;">Nett Income</th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">FIX</th>
                                    <th style="text-align:center;">TMP</th>';
                                    $html .= $htmlBpjsHeader;
                                    $html .= $htmlBpjsHeader;
                        $html .= '  <th style="text-align:center;">Deduction</th>
                                    <th style="text-align:center;">ABS (DAY)</th>
                                    <th style="text-align:center;">ABS (AMT)</th>
                                </tr>';

                $total = 0;
                foreach ($records as $record) {

                    $html_bpjs_company = "";
                    $total_bpjs_company = 0;
                    foreach (json_decode($record['bpjs_company'], true) as $bpjs_company => $val_bpjs_company) {
                        $html_bpjs_company .= '<td style="text-align:right;">' . number_format($val_bpjs_company) . '</td>';
                        $total_bpjs_company += $val_bpjs_company;
                    }

                    $html_bpjs_employee = "";
                    $total_bpjs_tk = 0;
                    $total_bpjs_kes = 0;
                    foreach (json_decode($record['bpjs_employee'], true) as $bpjs_employee => $val_bpjs_employee) {
                        if ($bpjs_employee != "bpjs_employee") {
                            $total_bpjs_tk += $val_bpjs_employee;
                        } else {
                            $total_bpjs_kes += $val_bpjs_employee;
                        }

                        $html_bpjs_employee .= '<td style="text-align:right;">' . number_format($val_bpjs_employee) . '</td>';
                    }

                    $total_allowence = 0;
                    $total_allowence_temp = 0;
                    $total_allowence_fix = 0;
                    foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {
                        $allTemp = $this->crud->read("allowances", ["number" => $allowence], ["type" => "TEMPORARY"]);
                        $allFix = $this->crud->read("allowances", ["number" => $allowence], ["type" => "FIX"]);
                        
                        if(!empty($allTemp)){
                            $total_allowence_temp += (int) $val_allowence;
                        }

                        if(!empty($allFix)){
                            $total_allowence_fix += (int) $val_allowence;
                        }

                        $total_allowence += (int)$val_allowence;
                    }

                    $total_deduction = 0;
                    foreach (json_decode($record['deduction'], true) as $deduction => $val_deduction) {
                        $total_deduction += (int)$val_deduction;
                    }

                    $total_ip = 0;
                    foreach (json_decode($record['deduction_number'], true) as $ip => $val_ip) {
                        $total_ip += (int)$val_ip;
                    }

                    $total_ip_amount = 0;
                    foreach (json_decode($record['deduction_amount'], true) as $ip_amount => $val_ip_amount) {
                        $total_ip_amount += (int)$val_ip_amount;
                    }

                    $total_income = $record['salary'] + $total_allowence + $record['correction_plus'] + $record['bpjs_company_total'];

                    if ($record['group_name'] == 'HARIAN LEPAS' && $record['contract_name'] != 'HARIAN LEPAS KHUSUS') {
                        $record['deduction_absence'] = '0';
                        $record['deduction_absence_amount'] = '0';
                        $total_income = (floatval($record['attandance_wd']) * floatval($record['salary'])) + floatval($record['correction_plus']);
                    }

                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record['number'] . '</td>
                                <td style="mso-number-format:\@;">' . $record['national_id'] . '</td>
                                <td>' . $record['name'] . '</td>
                                <td>' . $record['position_name'] . '</td>
                                <td>' . $record['group_name'] . '</td>
                                <td>' . $record['attandance_wd'] . '</td>
                                <td>' . $record['working_day'] . '</td>
                                <td style="text-align:right;">' . number_format($record['salary']) . '</td>
                                <td style="text-align:right;">' . number_format($total_allowence_fix) . '</td>
                                <td style="text-align:right;">' . number_format($total_allowence_temp) . '</td>
                                ' . $html_bpjs_company . '
                                <td style="text-align:right;">' . number_format($record['correction_plus']) . '</td>
                                <td style="text-align:right;">' . number_format($total_income) . '</td>
                                ' . $html_bpjs_employee . '
                                <td style="text-align:right;">' . number_format($total_deduction) . '</td>
                                <td style="text-align:right;">' . number_format($record['deduction_absence'] + $total_ip) . '</td>
                                <td style="text-align:right;">' . number_format($record['deduction_absence_amount'] + $total_ip_amount) . '</td>
                                <td style="text-align:right;">' . number_format(($record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'])) . '</td>
                                <td style="text-align:right;">' . number_format($record['correction_minus']) . '</td>
                                <td style="text-align:right;">' . number_format($record['deduction_absence_amount'] + $record['bpjs_employee_total'] + $record['bpjs_company_total'] + $record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'] + $record['correction_minus'] + $total_ip_amount + $total_deduction) . '</td>
                                <td style="text-align:right;">' . number_format($record['ter']) . '</td>
                                <td style="text-align:right;">' . number_format(($record['net_income'])) . '</td>
                            </tr>';
                    $total += $record['net_income'];
                    $no++;
                }

                $grand_total += $total;

                $html .= '  <tr>
                                <th style="text-align:right;" colspan="29">TOTAL SUB</th>
                                <th style="text-align:right;">' . number_format($total) . '</th>
                            </tr>';
                // Jika halaman terakhir, tambahkan GRAND TOTAL
                if (($i + 1) == $page) {
                    $html .= '  <tr>
                                    <th style="text-align:right;" colspan="29">GRAND TOTAL</th>
                                    <th style="text-align:right;">' . number_format($grand_total) . '</th>
                                </tr>';
                }
                $html .= '</table><br>';
                            
                $hal++;
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
            }

            $html .= '  <br>
                <center>
                    <table id="customers" style="width:70%;">
                        <tr>
                            <th rowspan="2" width="100" style="text-align:center;">APPROVED</th>
                            <th colspan="3" style="text-align:center;">CONFIRM OK</th>
                            <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                        </tr>
                        <tr>
                            <th width="100" style="text-align:center;">COST CONTROL</th>
                            <th width="100" style="text-align:center;">HRD-GA</th>
                            <th width="100" style="text-align:center;">HR</th>
                        </tr>
                        <tr>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                            <td style="height:60px;"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">LEADER</th>
                            <th style="text-align:center;">ASSISTANT MANAGER</th>
                            <th style="text-align:center;">LEADER</th>
                            <th style="text-align:center;">PAYROLL STAFF</th>
                        </tr>
                    </table>
                </center><br><br><br>';

            $html .= '</body></html>';
            echo $html;
        }
    }

    public function print_recap($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_summary_payroll_$format.xls");
            $html = '<html><head><title>Print Data</title>
            <style>
                #customers {border-collapse: collapse; width: 100%; font-size: 12px;}
                #customers td, #customers th {border: 0.5pt solid #000000; padding: 2px; vertical-align: middle;}
                #customers th {background: #f2f2f2;}
            </style>
            <body>';
        } else {
            $html = '<html><head><title>Print Data</title><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px; vertical-align: middle;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>';
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_group = base64_decode($this->input->get('filter_group'));
            $filter_group_ex = explode(",", trim($filter_group));
            $filter_position = $this->input->get('filter_position');
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            // Query untuk payrolls reguler
            $this->db->select('
                b.division_id,
                b.departement_id,
                b.departement_sub_id,
                b.group_id,
                b.position_id,
                v.name as division_name,
                c.name as departement_name, 
                d.name as departement_sub_name, 
                e.name as group_name,
                g.name as position_name,
                COUNT(b.id) as employee, 
                SUM(a.net_income) as income,
                "REGULER" as payroll_type');
            $this->db->from('payrolls a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('divisions v', "b.division_id = v.id");
            $this->db->join('departements c', "b.departement_id = c.id");
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
            $this->db->join('groups e', "b.group_id = e.id");
            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
            $this->db->join('positions g', "b.position_id = g.id");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('e.name', $filter_group_ex);
            }
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            if($filter_position && $filter_position != ''){
                $this->db->where('b.position_id', $filter_position);
            }
            $this->db->group_by(array("v.id", "c.id", "d.id", "e.id", "g.id"));
            
            $query1 = $this->db->get_compiled_select();
            
            // Reset query builder
            $this->db->reset_query();
            
            // Query untuk payroll_harian_lepas
            $this->db->select('
                b.division_id,
                b.departement_id,
                b.departement_sub_id,
                b.group_id,
                b.position_id,
                v.name as division_name,
                c.name as departement_name, 
                d.name as departement_sub_name, 
                e.name as group_name,
                g.name as position_name,
                COUNT(b.id) as employee, 
                SUM(a.net_income) as income,
                "HARIAN_LEPAS" as payroll_type');
            $this->db->from('payroll_harian_lepas a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('divisions v', "b.division_id = v.id");
            $this->db->join('departements c', "b.departement_id = c.id");
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
            $this->db->join('groups e', "b.group_id = e.id");
            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
	    $this->db->join('positions g', "b.position_id = g.id");
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('e.name', $filter_group_ex);
            }
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            if($filter_position && $filter_position != ''){
                $this->db->where('b.position_id', $filter_position);
            }
            $this->db->group_by(array("v.id", "c.id", "d.id", "e.id", "g.id"));
            
            $query2 = $this->db->get_compiled_select();
            
            // Reset query builder
            $this->db->reset_query();
            
            // Gabungkan kedua query dengan UNION
            $union_query = "($query1) UNION ALL ($query2)";
            
            // Query final untuk menggabungkan dan mengelompokkan hasil
            $final_query = "SELECT 
                division_id,
                departement_id,
                departement_sub_id,
                group_id,
                position_id,
                division_name,
                departement_name, 
                departement_sub_name, 
                group_name,
                position_name,
                SUM(employee) as employee, 
                SUM(income) as income,
                GROUP_CONCAT(DISTINCT payroll_type ORDER BY payroll_type SEPARATOR ', ') as payroll_type
            FROM ($union_query) as combined_data
            GROUP BY division_id, departement_id, departement_sub_id, group_id, position_id
            ORDER BY division_name ASC, departement_name ASC, SUM(income) ASC";
            
            $payrolls = $this->db->query($final_query)->result_array();

            //Config Page
            $rows = 30;
            $page = ceil(count($payrolls) / $rows);

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            //$html = '<html><head><title>Print Data</title><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px; vertical-align: middle;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>';
            $no = 1;
            $hal = 1;

            $grandtotalEmployee = 0;
            $grandtotalIncome = 0;
            for ($i = 0; $i < $page; $i++) {
                $offset = ($i * $rows);
                // Ambil data per halaman dari $payrolls
                $records = array_slice($payrolls, $offset, $rows);

                // Grouping data per halaman
                $division_groups = array();
                foreach ($records as $rec) {
                    $division_groups[$rec['division_name']][] = $rec;
                }

                $html .= '  <center>
                            <div style="float: left; font-size: 12px; text-align: left;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                            <img src="' . $config->favicon . '" width="30">
                                        </td>
                                        <td style="font-size: 14px; text-align: left; margin:2px;">
                                            <b>' . $config->name . '</b><br>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="float: right; font-size: 12px; text-align: right;">
                                Print Date ' . date("d M Y H:m:s") . ' <br>
                                Print By ' . $this->session->username . ' <br>
                                Page ' . $hal . '/' . $page . '
                            </div>
                        </center>
                        <br><br><br>
                        <center>
                            <h3 style="margin:0;">REPORT SUMMARY PAYROLL</h3>
                            <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>
                            <br>
                        </center>
                        
                        <table id="customers" border="1">
                            <tr>
                                <th width="2%" style="text-align:center;">No</th>
                                <th width="10%" style="text-align:center;">Division</th>
                                <th width="15%" style="text-align:center;">Departement</th>
                                <th width="15%" style="text-align:center;">Departement Sub</th>
                                <th width="15%" style="text-align:center;">Group</th>
                                <th width="10%" style="text-align:center;">Position</th>
                                <th width="5%" style="text-align:center;">Employee</th>
                                <th width="5%" style="text-align:center;">Pay Amount</th>
                            </tr>';
                $totalEmployee = 0;
                $totalIncome = 0;
                foreach ($division_groups as $division_name => $division_rows) {
                    $rowspan = count($division_rows);
                    $first = true;
                    foreach ($division_rows as $data) {
                        $html .= '  <tr>';
                        $html .= '<td style="text-align:center;">' . $no . '</td>';
                        $html .= $first ? '<td rowspan="'.$rowspan.'" style="text-align:center; vertical-align:middle;">' . $division_name . '</td>' : '';
                        $html .= '<td>' . $data['departement_name'] . '</td>';
                        $html .= '<td>' . $data['departement_sub_name'] . '</td>';
                        $html .= '<td>' . $data['group_name'] . '</td>';
                        $html .= '<td>' . $data['position_name'] . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($data['employee']) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($data['income']) . '</td>';
                        $html .= '</tr>';
                        $totalEmployee += $data['employee'];
                        $totalIncome += $data['income'];
                        $no++;
                        $first = false;
                    }
                }

                $html .= '  <tr>
                                <th style="text-align:right;" colspan="6">TOTAL SUB</th>
                                <th style="text-align:right;">' . number_format($totalEmployee) . '</th>
                                <th style="text-align:right;">' . number_format($totalIncome) . '</th>
                            </tr>';
                $grandtotalEmployee += $totalEmployee;
                $grandtotalIncome += $totalIncome;
                // Jika halaman terakhir, tambahkan baris TOTAL (grand total seluruh halaman)
                if (($i + 1) == $page) {
                    $html .= '  <tr>
                                    <th style="text-align:right;" colspan="6">GRAND TOTAL</th>
                                    <th style="text-align:right;">' . number_format($grandtotalEmployee) . '</th>
                                    <th style="text-align:right;">' . number_format($grandtotalIncome) . '</th>
                                </tr>';
                    $html .= '</table>';

                    // Tambahkan tabel tanda tangan dengan style untuk mencegah terpotong
                    $html .= '<br><br><table id="customers" style="width:100%; page-break-inside: avoid;">
                        <tr>
                            <td style="border:none;"></td>
                            <th style="text-align:center;">Disetujui</th>
                            <th style="text-align:center;">Diketahui</th>
                            <th style="text-align:center;" colspan="2">Diperiksa</th>
                            <th style="text-align:center;">Dibuat</th>
                        </tr>
                        <tr style="height:60px;">
                            <td style="border:none;"></td>
                            <td style="text-align:center;"></td>
                            <td style="text-align:center;"></td>
                            <td style="text-align:center;"></td>
                            <td style="text-align:center;"></td>
                            <td style="text-align:center;"></td>
                        </tr>
                        <tr>
                            <td style="border:none;"></td>
                            <td style="text-align:center;">Kinenta Harsono</td>
                            <td style="text-align:center;">Maya Evilia</td>
                            <td style="text-align:center;">Rizki Andri S</td>
                            <td style="text-align:center;">Nia Nurazizah</td>
                            <td style="text-align:center;">Jihan Maharani</td>
                        </tr>
                        <tr>
                            <td style="border:none;"></td>
                            <th style="text-align:center;">BOD</th>
                            <th style="text-align:center;">Leader FAT Cost Control</th>
                            <th style="text-align:center;">Asst Manager HRD-GA</th>
                            <th style="text-align:center;">Leader HR</th>
                            <th style="text-align:center;">HR-Payroll</th>
                        </tr>
                    </table><br>';
                } else {
                    $html .= '</table>';
                }

                $hal++;
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
            }


            $html .= '</body></html>';
            echo $html;
        }
    }

    public function print_detail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_summary_payroll_detail_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_group = base64_decode($this->input->get('filter_group'));
            $filter_group_ex = explode(",", $filter_group);
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $division = $this->crud->read("divisions", [], ["id" => $filter_division]);
            $departement = $this->crud->read("departements", [], ["id" => $filter_departement]);
            $departement_sub = $this->crud->read("departement_subs", [], ["id" => $filter_departement_sub]);

            if (empty($division->id)) {
                $division_name = "ALL";
            } else {
                $division_name = $division->name;
            }

            if (empty($departement->id)) {
                $departement_name = "ALL";
            } else {
                $departement_name = $departement->name;
            }

            if (empty($departement_sub->id)) {
                $departement_sub_name = "ALL";
            } else {
                $departement_sub_name = $departement_sub->name;
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $bpjsComs = $this->crud->reads('bpjs', ['status' => 0]);
            $htmlBpjsHeader = "";
            foreach ($bpjsComs as $bpjsCom) {
                $htmlBpjsHeader .= '<th style="text-align:center;">'.$bpjsCom->number.'</th>';
            }

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                    <body>';
            $no = 1;
            $html .= '  <center>
                                <div style="float: left; font-size: 12px; text-align: left;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                                <img src="' . $config->favicon . '" width="30">
                                            </td>
                                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                                <b>' . $config->name . '</b><br>
                                                <small>' . $config->description . '</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float: right; font-size: 12px; text-align: right;">
                                    Print Date ' . date("d M Y H:i:s") . ' <br>
                                    Print By ' . $this->session->username . ' <br>
                                </div>
                            </center><br><br><br>
                            <center>
                                <h3 style="margin:0;">REPORT SUMMARY PAYROLL DETAIL</h3>
                            </center><br>
                            <table style="font-size:10px;">
                                <tr>
                                    <td width="100">Period</td>
                                    <td width="20">:</td>
                                    <td width="300"><b>' . $filter_from . ' to ' . $filter_to . '</b></td>
                                </tr>
                                <tr>
                                    <td width="100">Plant</td>
                                    <td width="20">:</td>
                                    <td width="300"><b>' . $division_name . '</b></td>
                                </tr>
                                <tr>
                                    <td>Departement</td>
                                    <td>:</td>
                                    <td><b>' . $departement_name . '</b></td>
                                </tr>
                                <tr>
                                    <td>Departement Sub</td>
                                    <td>:</td>
                                    <td><b>' . $departement_sub_name . '</b></td>
                                </tr>
                            </table>
                            <br>
                            <table id="customers" border="1">
                                <tr>
                                    <th rowspan="2" width="20">No</th>
                                    <th rowspan="2" style="text-align:center;">Employee ID</th>
                                    <th rowspan="2" style="text-align:center;">National ID</th>
                                    <th rowspan="2" style="text-align:center;">Employee Name</th>
                                    <th rowspan="2" style="text-align:center;">Payroll Type</th>
                                    <th rowspan="2" style="text-align:center;">NPWP</th>
                                    <th rowspan="2" style="text-align:center;">Position</th>
                                    <th rowspan="2" style="text-align:center;">Marital</th>
                                    <th rowspan="2" style="text-align:center;">WD</th>
                                    <th rowspan="2" style="text-align:center;">HKW</th>
                                    <th rowspan="2" style="text-align:center;">Salary</th>
                                    <th colspan="6" style="text-align:center;">Allowance</th>
                                    <th rowspan="2" style="text-align:center;">Correction <br> Plus</th>
                                    <th rowspan="2" style="text-align:center;">Total <br> Income</th>
                                    <th colspan="8" style="text-align:center;">Deduction</th>
                                    <th rowspan="2" style="text-align:center;">BPJS<br>Company</th>
                                    <th rowspan="2" style="text-align:center;">Loan</th>
                                    <th rowspan="2" style="text-align:center;">Correction <br> Minus</th>
                                    <th rowspan="2" style="text-align:center;">TER</th>
                                    <th rowspan="2" style="text-align:center;">Total<br>Deduction</th>
                                    <th rowspan="2" style="text-align:center;">BPJS TK</th>
                                    <th rowspan="2" style="text-align:center;">BPJS KES</th>
                                    <th rowspan="2" style="text-align:center;">Bruto<br>Income</th>
                                    <th rowspan="2" style="text-align:center;">Nett Income</th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">Allowence</th>';
                                    $html .= $htmlBpjsHeader;
                                    $html .= $htmlBpjsHeader;
                        $html .= '  <th style="text-align:center;">Deduction</th>
                                    <th style="text-align:center;">ABS (DAY)</th>
                                    <th style="text-align:center;">ABS (AMT)</th>
                                </tr>';

            // Query untuk payrolls reguler
            $this->db->select('a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id as npwp, "REGULER" as payroll_type');
            $this->db->from('payrolls a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('privilege_groups c', "b.group_id = c.group_id and c.username = '$username' and c.status = '1'");
            $this->db->join('groups d', "b.group_id = d.id");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('d.name', $filter_group_ex);
            }
            $this->db->like('a.employee_id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->order_by('a.name', 'ASC');
            $records_reguler = $this->db->get()->result_array();
            
            // Reset query builder
            $this->db->reset_query();
            
            // Query untuk payroll_harian_lepas
            $this->db->select('a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id as npwp, "HARIAN_LEPAS" as payroll_type');
            $this->db->from('payroll_harian_lepas a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('privilege_groups c', "b.group_id = c.group_id and c.username = '$username' and c.status = '1'");
            $this->db->join('groups d', "b.group_id = d.id");
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('d.name', $filter_group_ex);
            }
            $this->db->like('a.employee_id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->order_by('a.name', 'ASC');
            $records_harian_lepas = $this->db->get()->result_array();
            
            // Gabungkan kedua array
            $records = array_merge($records_reguler, $records_harian_lepas);
            
            // Urutkan berdasarkan nama
            usort($records, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $no = 1;
            $total = 0;
            foreach ($records as $record) {

                $html_bpjs_company = "";
                $total_bpjs_company = 0;
                foreach (json_decode($record['bpjs_company'], true) as $bpjs_company => $val_bpjs_company) {
                    $html_bpjs_company .= '<td style="text-align:right;">' . number_format($val_bpjs_company) . '</td>';
                    $total_bpjs_company += $val_bpjs_company;
                }

                $html_bpjs_employee = "";
                $total_bpjs_tk = 0;
                $total_bpjs_kes = 0;
                foreach (json_decode($record['bpjs_employee'], true) as $bpjs_employee => $val_bpjs_employee) {
                    if ($bpjs_employee != "bpjs_employee") {
                        $total_bpjs_tk += $val_bpjs_employee;
                    } else {
                        $total_bpjs_kes += $val_bpjs_employee;
                    }

                    $html_bpjs_employee .= '<td style="text-align:right;">' . number_format($val_bpjs_employee) . '</td>';
                }

                $total_allowence = 0;
                foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {
                    $total_allowence += (int)$val_allowence;
                }

                $total_deduction = 0;
                foreach (json_decode($record['deduction'], true) as $deduction => $val_deduction) {
                    $total_deduction += (int)$val_deduction;
                }

                $total_ip = 0;
                foreach (json_decode($record['deduction_number'], true) as $ip => $val_ip) {
                    $total_ip += (int)$val_ip;
                }

                $total_ip_amount = 0;
                foreach (json_decode($record['deduction_amount'], true) as $ip_amount => $val_ip_amount) {
                    $total_ip_amount += (int)$val_ip_amount;
                }

                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record['number'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['national_id'] . '</td>
                            <td>' . $record['name'] . '</td>
                            <td style="text-align:center;">' . $record['payroll_type'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['npwp'] . '</td>
                            <td>' . $record['position_name'] . '</td>
                            <td>' . $record['marital'] . '</td>
                            <td>' . $record['attandance_wd'] . '</td>
                            <td>' . $record['working_day'] . '</td>
                            <td style="text-align:right;">' . number_format($record['salary']) . '</td>
                            <td style="text-align:right;">' . number_format($total_allowence) . '</td>
                            ' . $html_bpjs_company . '
                            <td style="text-align:right;">' . number_format($record['correction_plus']) . '</td>
                            <td style="text-align:right;">' . number_format(($record['salary'] + $total_allowence + $record['correction_plus'] + $record['bpjs_company_total'])) . '</td>
                            ' . $html_bpjs_employee . '
                            <td style="text-align:right;">' . number_format($total_deduction) . '</td>
                            <td style="text-align:right;">' . number_format($record['deduction_absence'] + $total_ip) . '</td>
                            <td style="text-align:right;">' . number_format($record['deduction_absence_amount'] + $total_ip_amount) . '</td>
                            <td style="text-align:right;">' . number_format($total_bpjs_company) . '</td>
                            <td style="text-align:right;">' . number_format(($record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'])) . '</td>
                            <td style="text-align:right;">' . number_format($record['correction_minus']) . '</td>
                            <td style="text-align:right;">' . number_format($record['ter']) . '</td>
                            <td style="text-align:right;">' . number_format($record['deduction_absence_amount'] + $record['bpjs_employee_total'] + $record['bpjs_company_total'] + $record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'] + $record['correction_minus'] + $total_ip_amount + $total_deduction) . '</td>
                            <td style="text-align:right;">' . number_format(($total_bpjs_tk)) . '</td>
                            <td style="text-align:right;">' . number_format(($total_bpjs_kes)) . '</td>
                            <td style="text-align:right;">' . number_format(($record['salary'] + $total_allowence)) . '</td>
                            <td style="text-align:right;">' . number_format(($record['net_income'])) . '</td>
                        </tr>';
                $total += $record['net_income'];
                $no++;
            }

            $html .= '  <tr>
                            <th style="text-align:right;" colspan="34">GRAND TOTAL</th>
                            <th style="text-align:right;">' . number_format($total) . '</th>
                        </tr>';
            $html .= '</body></html>';
            echo $html;
        }
    }
}
