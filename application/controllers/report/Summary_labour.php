<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Summary_labour extends CI_Controller

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

            $username = $this->session->username;



            $data['button'] = $this->getbutton($this->id_menu());

            $data['user'] = $this->crud->read("users", [], ["username" => $username]);



            $this->load->view('template/header', $data);

            $this->load->view('report/summary_labour');

        } else {

            redirect('error_access');

        }

    }

    public function print($option = "")
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
            // Fungsi untuk format angka tanpa koma saat export Excel
            $formatNumber = function($number) {
                return $number;
            };
        } else {
            $html = '<html><head><title>Print Data</title><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px; vertical-align: middle;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>';
            // Fungsi untuk format angka dengan koma saat print biasa
            $formatNumber = function($number) {
                return number_format($number);
            };
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
            $filter_job_type = $this->input->get('filter_job_type');
            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            //Select Query untuk payrolls
            $this->db->select('
                a.division_name,
                a.departement_name,
                a.group_name,
                a.job_type,
                COUNT(a.employee_id) as employee, 
                SUM(a.net_income) as income');
            $this->db->from('payrolls a');
            $this->db->join('employees b', "a.employee_id = b.id"); // tetap join untuk filter
            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('a.group_name', $filter_group_ex);
            }
            $this->db->like('a.employee_id', $filter_employee);
            $this->db->like('a.division_name', $filter_division);
            $this->db->like('a.departement_name', $filter_departement);
            $this->db->like('a.job_type', $filter_job_type);
            $this->db->group_by(array("a.division_name", "a.departement_name", "a.group_name", "a.job_type"));
            $this->db->order_by('a.division_name', 'ASC');
            $this->db->order_by('a.departement_name', 'ASC');
            $this->db->order_by('a.job_type', 'ASC');
            $this->db->order_by('SUM(a.net_income)', 'ASC');
            //Get Data Array dari payrolls
            $payrolls = $this->db->get()->result_array();

            //Select Query untuk payroll_pkl dengan format period yang berbeda
            $this->db->select('
                v.name as division_name,
                c.name as departement_name,
                e.name as group_name,
                d.type as job_type,
                COUNT(a.employee_id) as employee, 
                SUM(a.total_income) as income');
            $this->db->from('payroll_pkl a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('divisions v', "b.division_id = v.id");
            $this->db->join('departements c', "b.departement_id = c.id");
            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
            $this->db->join('groups e', "b.group_id = e.id");
            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.period_start >=', $filter_from);
            $this->db->where('a.period_end <=', $filter_to);
            if($filter_group != ""){
                $this->db->where_in('e.name', $filter_group_ex);
            }
            $this->db->like('a.employee_id', $filter_employee);
            $this->db->like('v.name', $filter_division);
            $this->db->like('c.name', $filter_departement);
            $this->db->like('d.type', $filter_job_type);
            $this->db->group_by(array("v.name", "c.name", "e.name", "d.type"));
            $this->db->order_by('v.name', 'ASC');
            $this->db->order_by('c.name', 'ASC');
            $this->db->order_by('d.type', 'ASC');
            $this->db->order_by('SUM(a.total_income)', 'ASC');
            //Get Data Array dari payroll_pkl
            $payrolls_pkl = $this->db->get()->result_array();

            //Select Query untuk payroll_harian_lepas
            $this->db->select('
                a.division_name,
                a.departement_name,
                a.group_name,
                a.job_type,
                COUNT(a.employee_id) as employee, 
                SUM(a.net_income) as income');
            $this->db->from('payroll_harian_lepas a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.period_start =', $period_start);
            $this->db->where('a.period_end =', $period_end);
            if($filter_group != ""){
                $this->db->where_in('a.group_name', $filter_group_ex);
            }
            $this->db->like('a.employee_id', $filter_employee);
            $this->db->like('a.division_name', $filter_division);
            $this->db->like('a.departement_name', $filter_departement);
            $this->db->like('a.job_type', $filter_job_type);
            $this->db->group_by(array("a.division_name", "a.departement_name", "a.group_name", "a.job_type"));
            $this->db->order_by('a.division_name', 'ASC');
            $this->db->order_by('a.departement_name', 'ASC');
            $this->db->order_by('a.job_type', 'ASC');
            $this->db->order_by('SUM(a.net_income)', 'ASC');
            //Get Data Array dari payroll_harian_lepas
            $payrolls_harian_lepas = $this->db->get()->result_array();

            // Gabungkan data dari ketiga tabel
            $all_payrolls = array_merge($payrolls, $payrolls_pkl, $payrolls_harian_lepas);

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $no = 1;
            $grandtotalEmployee = 0;
            $grandtotalIncome = 0;

            // Grouping data: Division > Job Type > Departement
            $division_groups = array();
            $combined_data = array();
            
            // Gabungkan data yang memiliki key yang sama
            foreach ($all_payrolls as $rec) {
                $key = $rec['division_name'] . '|' . $rec['job_type'] . '|' . $rec['departement_name'] . '|' . $rec['group_name'];
                
                if (!isset($combined_data[$key])) {
                    $combined_data[$key] = $rec;
                } else {
                    // Jika key sudah ada, jumlahkan employee dan income
                    $combined_data[$key]['employee'] += $rec['employee'];
                    $combined_data[$key]['income'] += $rec['income'];
                }
            }
            
            // Grouping data yang sudah digabung
            foreach ($combined_data as $rec) {
                $division = $rec['division_name'];
                $job_type = $rec['job_type'];
                $departement = $rec['departement_name'];
                $division_groups[$division][$job_type][$departement][] = $rec;
            }

            $grandJobTypeTotals = array();
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
                                <th width="10%" style="text-align:center;">Job Type</th>
                                <th width="15%" style="text-align:center;">Departement</th>
                                <th width="15%" style="text-align:center;">Group</th>
                                <th width="5%" style="text-align:center;">Employee</th>
                                <th width="5%" style="text-align:center;">Pay Amount</th>
                            </tr>';
            $totalEmployee = 0;
            $totalIncome = 0;
            foreach ($division_groups as $division_name => $jobtype_groups) {
                // Hitung division_rowspan: jumlah baris data + jumlah subtotal job type
                $division_rowspan = 0;
                $jobtype_rowspans = array();
                foreach ($jobtype_groups as $job_type => $departement_groups) {
                    $jobtype_rowspan = 0;
                    foreach ($departement_groups as $group_rows) {
                        $jobtype_rowspan += count($group_rows);
                    }
                    $jobtype_rowspan += 1; // +1 untuk subtotal job type
                    $jobtype_rowspans[$job_type] = $jobtype_rowspan;
                    $division_rowspan += $jobtype_rowspan;
                }
                $first_division = true;
                $subtotalEmployee = 0;
                $subtotalIncome = 0;
                
                // Hitung total division terlebih dahulu untuk perhitungan persentase
                $division_total_employee = 0;
                $division_total_income = 0;
                foreach ($jobtype_groups as $job_type => $departement_groups) {
                    foreach ($departement_groups as $departement_name => $group_rows) {
                        foreach ($group_rows as $data) {
                            $division_total_employee += $data['employee'];
                            $division_total_income += $data['income'];
                        }
                    }
                }
                
                foreach ($jobtype_groups as $job_type => $departement_groups) {
                    $first_jobtype = true;
                    $jobtype_subtotalEmployee = 0;
                    $jobtype_subtotalIncome = 0;
                    foreach ($departement_groups as $departement_name => $group_rows) {
                        $departement_rowspan = count($group_rows);
                        $first_departement = true;
                        foreach ($group_rows as $data) {
                            $html .= '<tr>';
                            $html .= '<td style="text-align:center;">' . $no . '</td>';
                            if ($first_division) {
                                $html .= '<td rowspan="' . $division_rowspan . '" style="text-align:center; vertical-align:middle;">' . $division_name . '</td>';
                                $first_division = false;
                            }
                            if ($first_jobtype) {
                                $html .= '<td rowspan="' . $jobtype_rowspans[$job_type] . '" style="text-align:center; vertical-align:middle;">' . $job_type . '</td>';
                                $first_jobtype = false;
                            }
                            if ($first_departement) {
                                $html .= '<td rowspan="' . $departement_rowspan . '" style="text-align:center; vertical-align:middle;">' . $departement_name . '</td>';
                                $first_departement = false;
                            }
                            $html .= '<td>' . $data['group_name'] . '</td>';
                            $html .= '<td style="text-align:right;">' . $formatNumber($data['employee']) . '</td>';
                            $html .= '<td style="text-align:right;">' . $formatNumber($data['income']) . '</td>';
                            $html .= '</tr>';
                            $jobtype_subtotalEmployee += $data['employee'];
                            $jobtype_subtotalIncome += $data['income'];
                            $subtotalEmployee += $data['employee'];
                            $subtotalIncome += $data['income'];
                            $totalEmployee += $data['employee'];
                            $totalIncome += $data['income'];
                            $grandtotalEmployee += $data['employee'];
                            $grandtotalIncome += $data['income'];
                            $no++;
                            // Kumpulkan Grand Total per Job Type
                            if (!isset($grandJobTypeTotals[$job_type])) {
                                $grandJobTypeTotals[$job_type] = ['employee' => 0, 'income' => 0];
                            }
                            $grandJobTypeTotals[$job_type]['employee'] += $data['employee'];
                            $grandJobTypeTotals[$job_type]['income'] += $data['income'];
                        }
                    }
                    
                    // Hitung persentase untuk job type terhadap division
                    $jobtype_employee_percent = ($division_total_employee > 0) ? round(($jobtype_subtotalEmployee / $division_total_employee) * 100, 1) : 0;
                    $jobtype_income_percent = ($division_total_income > 0) ? round(($jobtype_subtotalIncome / $division_total_income) * 100, 1) : 0;
                    
                    // Cetak subtotal job type DI DALAM rowspan dengan persentase
                    $html .= '<tr style="background:#f0f0f0;font-weight:bold;">
                        <td colspan="3"></td>
                        <td style="text-align:center;">SUBTOTAL ' . strtoupper($job_type) . '</td>
                        <td style="text-align:right;">' . $jobtype_income_percent . '%</td>
                        <td style="text-align:right;">' . $formatNumber($jobtype_subtotalEmployee) . '</td>
                        <td style="text-align:right;">' . $formatNumber($jobtype_subtotalIncome) . '</td>
                    </tr>';
                }
                
                // Subtotal division tetap di luar dengan persentase (selalu 100%)
                $html .= '<tr style="background:#e6e6e6;font-weight:bold;">
                    <td colspan="4" style="text-align:center;">SUBTOTAL ' . strtoupper($division_name) . '</td>
                    <td style="text-align:right;">100%</td>
                    <td style="text-align:right;">' . $formatNumber($subtotalEmployee) . '</td>
                    <td style="text-align:right;">' . $formatNumber($subtotalIncome) . '</td>
                </tr>';
            }

            // Setelah seluruh data dan subtotal division dicetak, tampilkan Grand Total per Job Type
            foreach ($grandJobTypeTotals as $job_type => $totals) {
                // Hitung persentase untuk grand total job type terhadap grand total keseluruhan
                $grand_jobtype_employee_percent = ($grandtotalEmployee > 0) ? round(($totals['employee'] / $grandtotalEmployee) * 100, 1) : 0;
                $grand_jobtype_income_percent = ($grandtotalIncome > 0) ? round(($totals['income'] / $grandtotalIncome) * 100, 1) : 0;
                
                $html .= '<tr style="background:#d0d0d0;font-weight:bold;">
                    <td colspan="4" style="text-align:right;">Grand Total ' . strtoupper($job_type) . '</td>
                    <td style="text-align:right;">' . $grand_jobtype_income_percent . '%</td>
                    <td style="text-align:right;">' . $formatNumber($totals['employee']) . '</td>
                    <td style="text-align:right;">' . $formatNumber($totals['income']) . '</td>
                </tr>';
            }

            $html .= '  <tr>
                                <th style="text-align:right;" colspan="4">GRAND TOTAL</th>
                                <th style="text-align:right;">100%</th>
                                <th style="text-align:right;">' . $formatNumber($grandtotalEmployee) . '</th>
                                <th style="text-align:right;">' . $formatNumber($grandtotalIncome) . '</th>
                            </tr>';
            $html .= '</table>';

            // Tambahkan tabel tanda tangan dengan style untuk mencegah terpotong
            $html .= '<br><br><table id="customers" style="width:100%; page-break-inside: avoid;">
                <tr>
                    <td style="border:none;"></td>
                    <th style="text-align:center;" colspan="2">Disetujui</th>
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
                    <td style="text-align:center;"></td>
                </tr>
                <tr>
                    <td style="border:none;"></td>
                    <td style="text-align:center;">Kinenta Harsono</td>
                    <td style="text-align:center;">Amsyuharrasmi</td>
                    <td style="text-align:center;">Maya Evilia</td>
                    <td style="text-align:center;">Rizki Andri S</td>
                    <td style="text-align:center;">Nia Nurazizah</td>
                    <td style="text-align:center;">Jihan Maharani</td>
                </tr>
                <tr>
                    <td style="border:none;"></td>
                    <th style="text-align:center;">BOD</th>
                    <th style="text-align:center;">GENERAL MANAGER</th>
                    <th style="text-align:center;">Leader FAT Cost Control</th>
                    <th style="text-align:center;">Asst Manager HRD-GA</th>
                    <th style="text-align:center;">Leader HR</th>
                    <th style="text-align:center;">HR-Payroll</th>
                </tr>
            </table><br>';


            $html .= '</body></html>';
            echo $html;
        }
    }
}

