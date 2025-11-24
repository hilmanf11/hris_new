<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Student_payrolls extends CI_Controller

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

            $this->load->view('report/student_payrolls');

        } else {

            redirect('error_access');

        }

    }



    public function readService($dateSign = "", $dateout = "")

    {

        if ($dateSign == "") {

            $date = $this->input->post('date');

        } else {

            $date = $dateSign;

        }



        if ($dateout == "") {

            $end = date_create(); // waktu sekarang

        } else {

            $end = date_create($dateout);

        }



        $start  = date_create($date);

        $diff  = date_diff($start, $end);

        $d = $diff->d . ' Days ';



        if ($diff->y == 0) {

            $y = '';

        } else {

            $y = $diff->y . ' Years, ';

        }



        if ($diff->m == 0) {

            $m = '';

        } else {

            $m = $diff->m . ' Month, ';

        }



        if ($dateSign == "") {

            echo $y . $m . $d;

        } else {

            return $y . $m . $d;

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=payroll_report_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_group = $this->input->get('filter_group');

            $filter_source = $this->input->get('filter_source');

            $filter_employee = $this->input->get('filter_employee');



            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>

            <style> .str{ mso-number-format:\@; } </style>

            <body>';
            $this->db->select('a.id as group_id, a.name as group_name, b.id as source_id, b.name as source_name');
            $this->db->from('groups a');
            $this->db->join('sources b', 'a.id = b.group_id');
            $this->db->where('a.id', $filter_group);
            // if ($filter_group != "") {
            //     $this->db->where('a.id', $filter_group);
            // }
            if ($filter_source != "") {
                $this->db->like('b.id', $filter_source);
            }
            $this->db->group_by('b.id');
            $this->db->order_by('a.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            if (@$records[0]['group_name'] == "MAGANG") {
                $grandtotal = 0;
                $autoid = 0;
                foreach ($records as $record) {
                    $autoid++;
                    $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id FROM payrolls a
                        JOIN employees b ON a.employee_id = b.id
                        LEFT JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                        WHERE a.period_start = '$period_start' and a.period_end = '$period_end'
                        AND b.status = 0
                        AND b.group_id = '$record[group_id]'
                        AND b.source_id = '$record[source_id]'
                        AND b.id like '%$filter_employee%'
                        ORDER BY a.`name` ASC");
                    $recordEmployees = $query->result_array();

                    //Config Page
                    $rows = 20;
                    $page = ceil(count($recordEmployees) / $rows);

                    $html .= '<div style="page-break-after:always;">';
                    $no = 1;
                    $hal = 1;
                    $totalgroup = 0;
                    for ($i = 0; $i < $page; $i++) {
                        $offset = ($i * 20);
                        $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id FROM payrolls a
                            JOIN employees b ON a.employee_id = b.id
                            LEFT JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                            WHERE a.period_start = '$period_start' and a.period_end = '$period_end'
                            AND b.status = 0
                            AND b.group_id = '$record[group_id]'
                            AND b.source_id = '$record[source_id]'
                            AND b.id like '%$filter_employee%'
                            ORDER BY a.`name` ASC LIMIT 20 OFFSET $offset");
                        $employees = $query->result_array();

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
                                            Print By ' . $this->session->username . '  
                                        </div>
                                    </center><br><br><br>
                        <center>
                            <h3 style="margin:0;">PAYROLL REPORT ' . $record['group_name'] . ' ' . $record['source_name'] . '</h3>
                            <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>
                            <br>
                        </center>
                        <table id="customers" border="1">
                            <tr>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2" style="text-align:center;">Employee ID</th>
                                <th rowspan="2" style="text-align:center;">National ID</th>
                                <th rowspan="2" style="text-align:center;">Employee Name</th>
                                <th rowspan="2" style="text-align:center;">WD</th>
                                <th rowspan="2" style="text-align:center;">Salary</th>
                                <th colspan="2" style="text-align:center;">Allowance</th>
                                <th rowspan="2" style="text-align:center;">Correction <br> Plus</th>
                                <th rowspan="2" style="text-align:center;">Total <br> Income</th>
                                <th colspan="4" style="text-align:center;">Deduction</th>
                                <th rowspan="2" style="text-align:center;">Loan</th>
                                <th rowspan="2" style="text-align:center;">Correction <br> Minus</th>
                                <th rowspan="2" style="text-align:center;">TER</th>
                                <th rowspan="2" style="text-align:center;">Total<br>Deduction</th>
                                <th rowspan="2" style="text-align:center;">Nett Income</th>
                                <th rowspan="2" width="50" style="text-align:center;">Sign</th>
                            </tr>
                            <tr>
                                <th style="text-align:center;">Allowance</th>
                                <th style="text-align:center;">BPJS</th>
                                <th style="text-align:center;">BPJS</th>
                                <th style="text-align:center;">Deduction</th>
                                <th style="text-align:center;">ABS (DAY)</th>
                                <th style="text-align:center;">ABS (AMT)</th>
                            </tr>';

                        $total = 0;
                        foreach ($employees as $employee) {

                            $total_allowence = 0;
                            foreach (json_decode($employee['allowence'], true) as $allowence => $val_allowence) {
                                $total_allowence += (int)$val_allowence;
                            }

                            $total_deduction = 0;
                            foreach (json_decode($employee['deduction'], true) as $deduction => $val_deduction) {
                                $total_deduction += (int)$val_deduction;
                            }

                            $total_ip = 0;
                            foreach (json_decode($employee['deduction_number'], true) as $ip => $val_ip) {
                                $total_ip += (int)$val_ip;
                            }

                            $total_ip_amount = 0;
                            foreach (json_decode($employee['deduction_amount'], true) as $ip_amount => $val_ip_amount) {
                                $total_ip_amount += (int)$val_ip_amount;
                            }

                            $html .= '<tr>
                                        <td>' . $no . '</td>
                                        <td style="mso-number-format:\@;">' . $employee['number'] . '</td>
                                        <td style="mso-number-format:\@;">' . $employee['national_id'] . '</td>
                                        <td>' . $employee['name'] . '</td>
                                        <td>' . $employee['attandance_wd'] . '</td>
                                        <td style="text-align:right;">' . number_format($employee['salary']) . '</td>
                                        <td style="text-align:right;">' . number_format(($total_allowence)) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['bpjs_company_total'])) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['correction_plus'])) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['salary'] + $total_allowence + $employee['correction_plus'] + $employee['bpjs_company_total'])) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['bpjs_employee_total'] + $employee['bpjs_company_total'])) . '</td>
                                        <td style="text-align:right;">' . number_format($total_deduction) . '</td>
                                        <td style="text-align:right;">' . number_format($employee['deduction_absence'] + $total_ip) . '</td>
                                        <td style="text-align:right;">' . number_format($employee['deduction_absence_amount'] + $total_ip_amount) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['loan_cooperative'] + $employee['loan_bank'] + $employee['loan_other'])) . '</td>
                                        <td style="text-align:right;">' . number_format($employee['correction_minus']) . '</td>
                                        <td style="text-align:right;">' . number_format($employee['pph']) . '</td>
                                        <td style="text-align:right;">' . number_format($total_deduction + $employee['deduction_absence_amount'] + $employee['bpjs_employee_total'] + $employee['bpjs_company_total'] + $employee['loan_cooperative'] + $employee['loan_bank'] + $employee['loan_other'] + $employee['correction_minus'] + $total_ip_amount) . '</td>
                                        <td style="text-align:right;">' . number_format(($employee['net_income'])) . '</td>
                                        <td style="vertical-align: text-top; height:30px;">'.$no.'.</td>
                                    </tr>';
                            $total += $employee['net_income'];
                            $no++;
                        }



                        $totalgroup += $total;

                        $grandtotal += $total;



                        $html .= '  <tr>
                                        <th style="text-align:right;" colspan="18">TOTAL SUB</th>
                                        <th style="text-align:right;">' . number_format($total) . '</th>
                                        <th style="text-align:right;"></th>
                                    </tr>';



                        if (($i + 1) == $page) {

                            $html .= '  <tr>
                                            <th style="text-align:right;" colspan="18">TOTAL '.$record['source_name'].'</th>
                                            <th style="text-align:right;">' . number_format($totalgroup) . '</th>
                                            <th style="text-align:right;"></th>
                                        </tr>';

                        }



                        if($autoid == count($records) && ($i + 1) == $page){

                            $html .= '  <tr>
                                            <th style="text-align:right;" colspan="18">GRAND TOTAL ALL '.$record['group_name'].'</th>
                                            <th style="text-align:right;">' . number_format($grandtotal) . '</th>
                                            <th style="text-align:right;"></th>
                                        </tr>';

                        }



                        $html .= '</table><br>';

                        // Add signature table at the end of each source's last page
                        if (($i + 1) == $page) {
                            $html .= '<center>
                                        <table id="customers" style="width:70%;">
                                            <tr>
                                                <th rowspan="2" colspan="2" style="text-align:center;">APPROVED</th>
                                                <th colspan="3" style="text-align:center;">CONFIRM OK</th>
                                                <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                                            </tr>
                                            <tr>
                                                <th width="100" style="text-align:center;">COST CONTROL</th>
                                                <th width="100" style="text-align:center;">HRD-GA</th>
                                                <th width="100" style="text-align:center;">HR</th>
                                            </tr>
                                            <tr>
                                                <td style="height:60px;" width="100"></td>
                                                <td style="height:60px;" width="100"></td>
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
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:center;">BOD</th>
                                                <th style="text-align:center;">GENERAL MANAGER</th>
                                                <th style="text-align:center;">LEADER</th>
                                                <th style="text-align:center;">ASSISTANT MANAGER</th>
                                                <th style="text-align:center;">LEADER</th>
                                                <th style="text-align:center;">PAYROLL STAFF</th>
                                            </tr>
                                        </table>
                                    </center>';
                        }

                        $hal++;

                        if (($i + 1) != $page) {

                            $html .= '<div style="page-break-after:always;"></div>';

                        }

                    }

                    $html .= "</div>";
                }

                $html .= '</body></html>';
                echo $html;
            } else {
                $html2 = '  <html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                        <style> .str{ mso-number-format:\@; } </style>
                        <body>';
                
                $autoid = 1;     
                $grandtotal = 0;   
                foreach ($records as $record) {
                    $autoid++;

                    $allowance_1 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "1", "type" => "SALARY"]);
                    $allowance_2 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "2", "type" => "SALARY"]);
                    $allowance_3 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "3", "type" => "SALARY"]);
                    $payrolls = $this->crud->reads("payroll_pkl", [], ["period_start" => $filter_from, "period_end" => $filter_to, "source_id" => $record['source_id']]);

                    //Config Page
                    $rows = 20;
                    $page = ceil(count($payrolls) / $rows);

                    $html2 .= '<div style="page-break-after:always;">';
                    $no = 1;
                    $hal = 1;
                    $grandgroup=0;
                    for ($i = 0; $i < $page; $i++) {
                        $offset = ($i * 20);
                        $html2   .= '  <center>
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
                                            Print By ' . $this->session->username . '  
                                        </div>
                                    </center><br><br><br>
                                    <center>
                                        <h3 style="margin:0;">PAYROLL REPORT ' . $record['group_name'] . ' ' . $record['source_name'] . '</h3>
                                        <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>
                                        <br>
                                    </center>
                                    <table id="customers">
                                        <tr>
                                            <th rowspan="2" width="20" style="text-align:center;">No</th>
                                            <th rowspan="2" style="text-align:center;">Employee ID</th>
                                            <th rowspan="2" style="text-align:center;">Employee Name</th>
                                            <th rowspan="2" style="text-align:center;">Join Date</th>
                                            <th rowspan="2" style="text-align:center;">Fit of Service</th>
                                            <th rowspan="2" style="text-align:center;">Departement</th>
                                            <th rowspan="2" style="text-align:center;">Departement Sub</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Attandance</th>
                                            <th colspan="3" style="text-align:center;">Allowance Amount Type</th>
                                            <th rowspan="2" style="text-align:center;">Allowance</th>
                                            <th rowspan="2" style="text-align:center;">Intern<br>Fee</th>
                                            <th rowspan="2" style="text-align:center;">Boarding<br>Fee</th>
                                            <th colspan="2" style="text-align:center;">Correction</th>
                                            <th rowspan="2" style="text-align:center;">Total</th>
                                            <th rowspan="2" width="50" style="text-align:center;">Sign</th>
                                        </tr>
                                        <tr>
                                            <th width="50" style="text-align:center;">Month I <br>(' . @number_format($allowance_1->amount) . ')</th>
                                            <th width="50" style="text-align:center;">Month II <br>(' . @number_format($allowance_2->amount) . ')</th>
                                            <th width="50" style="text-align:center;">Month III <br>(' . @number_format($allowance_3->amount) . ')</th>
                                            <th width="50" style="text-align:center;">PLUS</th>
                                            <th width="50" style="text-align:center;">MINUS</th>
                                        </tr>';

                        $this->db->select("*");
                        $this->db->from("payroll_pkl");
                        $this->db->where('source_id', $record['source_id']);
                        $this->db->where('period_start', $filter_from);
                        $this->db->where('period_end', $filter_to);
                        $this->db->like('employee_id', $filter_employee);
                        $this->db->order_by('employee_name', 'asc');
                        $this->db->limit(20, ($i * 20));
                        $employees = $this->db->get()->result_array();
                        $total = 0;

                        foreach ($employees as $employee) {
                            $html2 .= '  <tr>
                                            <td style="text-align:center;">' . $no . '</td>
                                            <td class="str">' . $employee['employee_number'] . '</td>
                                            <td>' . $employee['employee_name'] . '</td>
                                            <td>' . date("d F Y", strtotime($employee['date_sign'])) . '</td>
                                            <td>' . $employee['services'] . '</td>
                                            <td>' . $employee['departement_name'] . '</td>
                                            <td>' . $employee['departement_sub_name'] . '</td>
                                            <td style="text-align:center;">' . $employee['attandance'] . '</td>
                                            <td style="text-align:center;">' . $employee['month_1'] . '</td>
                                            <td style="text-align:center;">' . $employee['month_2'] . '</td>
                                            <td style="text-align:center;">' . $employee['month_3'] . '</td>
                                            <td style="text-align:right;">' . number_format($employee['allowence']) . '</td>
                                            <td style="text-align:right;">' . number_format($employee['intern_fee']) . '</td>
                                            <td style="text-align:right;">' . number_format($employee['boarding']) . '</td>
                                            <td style="text-align:right;">' . number_format($employee['correction_plus']) . '</td>
                                            <td style="text-align:right;">' . number_format($employee['correction_minus']) . '</td>
                                            <td style="text-align:right;">' . @number_format($employee['total_income']) . '</td>
                                            <td style="vertical-align: text-top; height:30px;">'.$no.'.</td>
                                        </tr>';
                            $total += $employee['total_income'];
                            $no++;
                        }

                        $grandgroup += $total;
                        $grandtotal += $total;

                        $html2 .= '  <tr>
                                        <th style="text-align:right;" colspan="16">TOTAL SUB</th>
                                        <th style="text-align:right;">' . number_format($total) . '</th>
                                        <th style="text-align:right;"></th>
                                    </tr>';

                        if (($i + 1) == $page) {
                            $html2 .= '  <tr>
                                            <th style="text-align:right;" colspan="16">TOTAL '.$record['source_name'].'</th>
                                            <th style="text-align:right;">' . number_format($grandgroup) . '</th>
                                            <th style="text-align:right;"></th>
                                        </tr>';
                        }
                        
                        if($autoid == count($records)){
                            $html2 .= '  <tr>
                                            <th style="text-align:right;" colspan="16">GRAND TOTAL ALL '.$record['group_name'].'</th>
                                            <th style="text-align:right;">' . number_format($grandtotal) . '</th>
                                            <th style="text-align:right;"></th>
                                        </tr>';
                        }

                        $html2 .= '</table><br>';

                        // Add signature table at the end of each source's last page
                        if (($i + 1) == $page) {
                            $html2 .= '<center>
                                <table id="customers" style="width:70%;">
                                    <tr>
                                        <th rowspan="2" colspan="2" style="text-align:center;">APPROVED</th>
                                        <th colspan="3" style="text-align:center;">CONFIRM OK</th>
                                        <th rowspan="2" width="100" style="text-align:center;">PREPARED</th>
                                    </tr>
                                    <tr>
                                        <th width="100" style="text-align:center;">COST CONTROL</th>
                                        <th width="100" style="text-align:center;">HRD-GA</th>
                                        <th width="100" style="text-align:center;">HR</th>
                                    </tr>
                                    <tr>
                                        <td style="height:60px;" width="100"></td>
                                        <td style="height:60px;" width="100"></td>
                                        <td style="height:60px;"></td>
                                        <td style="height:60px;"></td>
                                        <td style="height:60px;"></td>
                                        <td style="height:60px;"></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">BOD</th>
                                        <th style="text-align:center;">GENERAL MANAGER</th>
                                        <th style="text-align:center;">LEADER</th>
                                        <th style="text-align:center;">ASSISTANT MANAGER</th>
                                        <th style="text-align:center;">LEADER</th>
                                        <th style="text-align:center;">PAYROLL STAFF</th>
                                    </tr>
                                </table>
                            </center>';
                        }

                        $hal++;
                    }

                    if(count($payrolls) > 0){
                        $html2 .= '<div style="page-break-after:always;"></div>';
                    }
                }
                
                $html2 .= '</body></html>';

                echo $html2;
            }
        }
    }
}

