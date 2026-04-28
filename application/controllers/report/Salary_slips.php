<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Salary_slips extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->helper('url');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('session');

        $this->load->model('crud');

        $this->load->model('emails');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());



            $this->load->view('template/header', $data);

            $this->load->view('report/salary_slips');

        } else {

            redirect('error_access');

        }

    }



    public function datatables()

    {

        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            //Select Query for regular payrolls

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

                COUNT(b.id) as employee, 

                SUM(a.net_income) as income,

                "REGULAR" as payroll_type');

            $this->db->from('payrolls a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period_start =', $period_start);

            $this->db->where('a.period_end =', $period_end);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id"));

            $this->db->order_by('c.name', 'ASC');

            $this->db->order_by('SUM(a.net_income)', 'ASC');

            //Get Data Array

            $records_regular = $this->db->get()->result_array();



            //Select Query for daily workers (payroll_harian_lepas)

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

                COUNT(b.id) as employee, 

                SUM(a.net_income) as income,

                "DAILY" as payroll_type');

            $this->db->from('payroll_harian_lepas a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period_start =', $period_start);

            $this->db->where('a.period_end =', $period_end);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id"));

            $this->db->order_by('c.name', 'ASC');

            $this->db->order_by('SUM(a.net_income)', 'ASC');

            //Get Data Array

            $records_daily = $this->db->get()->result_array();



            // Combine both results

            $records = array_merge($records_regular, $records_daily);



            echo json_encode($records);

        }

    }



    public function getData()

    {

        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            // Query for regular payrolls

            $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'REGULAR' as payroll_type

            FROM payrolls a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN marital_categories d ON a.ter_number = d.number

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period_start = '$period_start'

            AND a.period_end = '$period_end'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY a.`name` ASC");

            $records_regular = $query->result_array();



            // Query for daily workers (payroll_harian_lepas)

            $query_daily = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'DAILY' as payroll_type

            FROM payroll_harian_lepas a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN marital_categories d ON a.ter_number = d.number

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period_start = '$period_start'

            AND a.period_end = '$period_end'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY a.`name` ASC");

            $records_daily = $query_daily->result_array();

            if (!empty($records_daily)) {
                $records_daily[0]["allowance"] = "{}";
                $records_daily[0]["bpjs_company"] = "{}";
                $records_daily[0]["bpjs_company_total"] = "0";
                $records_daily[0]["correction_plus"] = "0";
                $records_daily[0]["overtime_weekday"] = "0";
                $records_daily[0]["overtime_convert_weekday"] = "0";
                $records_daily[0]["overtime_amount_weekday"] = "0";
                $records_daily[0]["overtime_holiday"] = "0";
                $records_daily[0]["overtime_convert_holiday"] = "0";
                $records_daily[0]["overtime_amount_holiday"] = "0";
                $records_daily[0]["overtime_correction"] = "0";
                $records_daily[0]["overtime_convert_correction"] = "0";
                $records_daily[0]["overtime_amount_correction"] = "0";
                $records_daily[0]["total_overtime"] = "0";
                $records_daily[0]["total_overtime_convert"] = "0";
                $records_daily[0]["total_overtime_amount"] = "0";
                $records_daily[0]["total_all_allowance"] = "0";
                $records_daily[0]["deduction_number"] = "{}";
                $records_daily[0]["deduction_amount"] = "{}";
                $records_daily[0]["deduction_absence"] = "0";
                $records_daily[0]["deduction_absence_amount"] = "0";
                $records_daily[0]["correction_minus"] = "0";
                $records_daily[0]["total_all_deduction"] = "0";
            }

            // Combine both results

            $records = array_merge($records_regular, $records_daily);



            die(json_encode($records));

        }

    }



    public function sendMail_old()

    {

        error_reporting(0);

        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $record = $this->input->post('data');



        $total_correction = ($record['amount_plus_correction'] + $record['amount_plus_salary'] + $record['amount_plus_backup'] + $record['amount_plus_cc'] + $record['amount_plus_holiday']);

        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $html = '<html>

                <head>

                    <title>Print Data</title>

                </head>

                <style>

                    body {font-family: Arial, Helvetica, sans-serif;}

                    #customers {border-collapse: collapse;width: 100%;font-size: 14px;}

                    #customers th {border: 1px solid black;padding: 5px;}

                    #customers td {padding-left: 5px; padding-right:5px;}

                    #customers tr:hover {background-color: #ddd;}

                    #customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}

                </style>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

                <body>';



        //Correction

        //jika dia ada tunjuangan ambil field dan isinya

        $q_correction_plus = $this->db->query("SELECT employee_id, `trans_date`, correction_name, remarks, SUM(amount) as amount FROM corrections WHERE employee_id='$record[employee_id]' and trans_date between '$filter_from' and '$filter_to' and correction_type = 'PLUS' GROUP BY correction_name");

        $r_correction_plus = $q_correction_plus->result_array();



        $r_correction_total = 0;

        $html_correction_plus = "";

        foreach ($r_correction_plus as $r_correction_plus_data) {

            $r_correction_total += $r_correction_plus_data['amount'];

            $html_correction_plus .= '<tr>

                                        <td style="text-align:left;">' . $r_correction_plus_data['remarks'] . '</td>

                                        <td style="text-align:right;"><b>' . number_format($r_correction_plus_data['amount']) . '</b></td>

                                    </tr>';

        }



        //Deduction Amount

        $total_deduction_amount = 0;

        foreach (json_decode($record['deduction_amount'], true) as $deduction_amount => $val_deduction_amount) {

            $total_deduction_amount += $val_deduction_amount;

        }



        //Deduction Number

        $total_deduction_number = 0;

        foreach (json_decode($record['deduction_number'], true) as $deduction_number => $val_deduction_number) {

            if ($val_deduction_number == "") {

                $val_deduction_number = 0;

            }



            $total_deduction_number += $val_deduction_number;

        }



        //Allowance Amount

        //jika dia ada tunjuangan ambil field dan isinya

        // $r_allowance = json_decode($record['allowence'], true);

        // $arr_allowance_amount_total = 0;

        // foreach ($r_allowance as $allowance_data => $allowance_val) {

        //     $arr_allowance_amount_total += $allowance_val;

        // }



        $total_allowence = 0;

        $total_allowence_temp = 0;

        $total_allowence_fix = 0;

        $html_allowence_temp = "";

        $html_allowence_fix = "";

        foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {

            $allTemp = $this->crud->read("allowances", ["number" => $allowence], ["type" => "TEMPORARY"]);

            $allFix = $this->crud->read("allowances", ["number" => $allowence], ["type" => "FIX"]);

            

            if(!empty($allTemp)){

                $total_allowence_temp += (int) $val_allowence;

                if((int) $val_allowence > 0){

                    $html_allowence_temp .= '<tr>

                                                <td style="text-align:left;">'.$allTemp->name.'</td>

                                                <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                            </tr>';

                }

            }



            if(!empty($allFix)){

                $total_allowence_fix += (int) $val_allowence;

                if((int) $val_allowence > 0){

                    $html_allowence_fix .= '<tr>

                                                <td style="text-align:left;">'.$allFix->name.'</td>

                                                <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                            </tr>';

                }

            }



            $total_allowence += (int)$val_allowence;

        }

        //-------------------------------------------------------------------------------------------------------------------------------



        //Deduction Amount

        //Jika dia ada pemotongan gaji ambil field dan isinya

        $q_deduction = $this->db->query("SELECT b.number, b.name, SUM(a.amount) as amount

            FROM deductions b

            LEFT JOIN setup_deductions a ON a.deduction_id = b.id

            WHERE a.employee_id = '$record[employee_id]'

            GROUP BY b.number, b.name ORDER BY b.name asc");

        $r_deduction = $q_deduction->result_array();



        $arr_deduction_amount_total = 0;

        $html_deduction = "";

        foreach ($r_deduction as $deduction_data) {

            $arr_deduction_amount_total += $deduction_data['amount'];

            $html_deduction .= ' <tr>

                                        <td style="text-align:left;">' . $deduction_data['name'] . '</td>

                                        <td style="text-align:right;"><b>' . number_format($deduction_data['amount']) . '</b></td>

                                    </tr>';

        }

        //-------------------------------------------------------------------------------------------------------------------------------------------------------



        //BPJS Employee

        //Kalo dia mempunyai BPJS

        $bpjsKeys = json_decode($record['bpjs_employee'], true);

        $html_bpjs_emp = "";

        $arr_bpjs_emp_amount_total = 0;

        foreach ($bpjsKeys as $bpjs_employee => $bpjs_value) {

            $arr_bpjs = explode("_", $bpjs_employee);

            $r_bpjs_emp = $this->crud->read('bpjs', ['status' => 0], ["number" => $arr_bpjs[0]]);



            $arr_bpjs_emp_amount_total += !empty($bpjs_value) ? $bpjs_value : 0;

            if (!empty($r_bpjs_emp->employee)) {

                $html_bpjs_emp .= ' <tr>

                                        <td style="text-align:left;">' . $r_bpjs_emp->name . '<i style="font-size:10px;">('.$r_bpjs_emp->employee.'%)</i></td>

                                        <td style="text-align:right;"><b>' . number_format($bpjs_value) . '</b></td>

                                    </tr>';

            }

        }

        //-------------------------------------------------------------------------------------------------------------------------------------------------------



        //BPJS Company

        //Kalo dia mempunyai BPJS

        $bpjsComps = json_decode($record['bpjs_company'], true);

        $html_bpjs_com = "";

        $arr_bpjs_com_amount_total = 0;

        foreach ($bpjsComps as $bpjs_company => $bpjs_com_val) {

            $com_bpjs = explode("_", $bpjs_company);

            $r_bpjs_com = $this->crud->read('bpjs', ['status' => 0], ["number" => $com_bpjs[0]]);



            $arr_bpjs_com_amount_total += !empty($bpjs_com_val) ? $bpjs_com_val : 0;

            if (!empty($r_bpjs_com->company)) {

                $html_bpjs_com.= ' <tr>

                                        <td style="text-align:left;">' . $r_bpjs_com->name . ' <i style="font-size:10px;">('.$r_bpjs_com->company.'%)</i></td>

                                        <td style="text-align:right;"><b>' . number_format($bpjs_com_val) . '</b></td>

                                    </tr>';

            }

        }

        //-------------------------------------------------------------------------------------------------------------------------------------------------------



        $total_income = $record['salary'] + $total_allowence + $record['total_overtime_amount'] + $record['correction_plus'];

        $total_deduction = $record['deduction_absence_amount'] + $total_deduction_amount + $arr_deduction_amount_total + $record['loan_bank'] + $record['loan_cooperative'] + $record['loan_other'] + $record['correction_minus'];



        $html .= '  <center><div class="container" style="border:1px solid black; margin-bottom:20px; padding-top:10px; float:left;">

                <table style="width: 100%;">

                    <tr>

                        <td style="font-size: 16px; text-align: left; margin:2px;">

                            <b>' . $config->name . '</b>

                        </td>

                        <td style="font-size: 16px; text-align: right; margin:2px;">

                            SALARY SLIP

                        </td>

                    </tr>

                </table>

                <hr>

                <div style="float:left; width:100%;">

                    <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                        <tr>

                            <th style="text-align:left; width: 30%;">Cut Off Period</th>

                            <th style="text-align:left; width: 5%;">:</th>

                            <th style="text-align:left; width: 60%;">' . date("d M Y", strtotime($filter_from)) . ' to ' . date("d M Y", strtotime($filter_to)) . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Employee ID</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['number'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Employee Name</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['name'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Departement</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['departement_name'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Departement Sub</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['departement_sub_name'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left; width: 30%;">National ID</th>

                            <th style="text-align:left; width: 5%;">:</th>

                            <th style="text-align:left;">' . $record['national_id'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Tax ID</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['tax_id'] . '</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Martial Status</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">(' . $record['marital'] . ') <small>' . $record['marital_name'] . '<small></th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">TER Code</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">'.$record['ter_number'].'</th>

                        </tr>

                        <tr>

                            <th style="text-align:left;">Working Days</th>

                            <th style="text-align:left;">:</th>

                            <th style="text-align:left;">' . $record['attandance_wd'] . ' Days</th>

                        </tr>

                    </table>

                </div>

                <div style="float:left; width:100%;">

                    <div class="row">

                        <div class="col p-0">

                            <table id="customers" style="width:100%; border-right: 1px solid black;">

                                <tr>

                                    <th colspan="2" style="text-align:center">INCOME</th>

                                </tr>

                                <tr>

                                    <td style="text-align:left;" width="250">Basic Salary</td>

                                    <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>

                                </tr>

                                <tr>

                                    <td style="text-align:left;"><b>Allowance (Fix)</b></td>

                                    <td style="text-align:right;"></td>

                                </tr>

                                '.$html_allowence_fix.'

                                <tr>

                                    <td style="text-align:left;"><b>Allowance (Tmp)</b></td>

                                    <td style="text-align:right;"></td>

                                </tr>

                                '.$html_allowence_temp.'

                                <tr>

                                    <td style="text-align:left;">Overtime</td>

                                    <td style="text-align:right;">(<b>' . ($record['overtime_weekday'] + $record['overtime_holiday']) . '</b> Hour) <b>' . number_format(($record['overtime_amount_weekday'] + $record['overtime_amount_holiday'])) . '</b></td>

                                </tr>

                                <tr>

                                    <td style="text-align:left;">Correction Overtime</td>

                                    <td style="text-align:right;">(<b>' . $record['overtime_correction'] . '</b> Hour) <b>' . number_format(($record['overtime_amount_correction'])) . '</b></td>

                                </tr>

                                <tr>

                                    <td style="text-align:left;">Correction</td>

                                    <td style="text-align:right;">-</td>

                                </tr>

                                ' . $html_correction_plus . '

                                <tr>

                                    <th style="text-align:left;">TOTAL INCOME <i>(a)</i></th>

                                    <th style="text-align:right;"><b>' . number_format($total_income) . '</b></th>

                                </tr>

                                <tr>

                                    <th colspan="2" style="text-align:center">BPJS COMPANY</th>

                                </tr>

                                ' . $html_bpjs_com . '

                                <tr>

                                    <th style="text-align:left;">TOTAL BPJS COMPANY <i>(b)</i></th>

                                    <th style="text-align:right;"><b>' . number_format(($arr_bpjs_com_amount_total)) . '</b></th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">BRUTO INCOME <i>(c) = (a + b)</i></th>

                                    <th style="text-align:right;"><b>' . number_format($total_income + $arr_bpjs_com_amount_total) . '</b></th>

                                </tr>

                            </table>

                        </div>

                        <div class="col p-0">

                            <table id="customers" style="width:100%; border-left: 1px solid black;">

                                <tr>

                                    <th colspan="2" style="text-align:center">DEDUCTION</th>

                                </tr>

                                <tr>

                                    <td style="text-align:left;" width="250">Absence</td>

                                    <td style="text-align:right;" width="150">(<b>' . ($record['deduction_absence'] + $total_deduction_number) . '</b> Days) <b>' . number_format($record['deduction_absence_amount'] + $total_deduction_amount) . '</b></td>

                                </tr>

                                ' . @$html_deduction . '

                                <tr>

                                    <td style="text-align:left;">Loans</td>

                                    <td style="text-align:right;"><b>' . number_format($record['loan_other'] + $record['loan_cooperative'] + $record['loan_bank']) . '</b></td>

                                </tr>

                                <tr>

                                    <td style="text-align:left;">Correction Minus</td>

                                    <td style="text-align:right;"><b>' . number_format($record['correction_minus']) . '</b></td>

                                </tr>

                                <tr>

                                    <td style="text-align:left;">BPJS Company</td>

                                    <td style="text-align:right;"><b>' . number_format($arr_bpjs_com_amount_total) . '</b></td>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">TOTAL DEDUCTION <i>(d)</i></th>

                                    <th style="text-align:right;"><b>' . number_format($total_deduction + $arr_bpjs_com_amount_total) . '</b></th>

                                </tr>

                                <tr>

                                    <th colspan="2" style="text-align:center">BPJS EMPLOYEE</th>

                                </tr>

                                ' . $html_bpjs_emp . '

                                <tr>

                                    <th style="text-align:left;">TOTAL BPJS <i>(e)</i></th>

                                    <th style="text-align:right;"><b>' . number_format(($arr_bpjs_emp_amount_total)) . '</b></th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">TER ('.$record['ter_persentase'].') <i>(f)</i></th>

                                    <th style="text-align:right;"><b>' . number_format($record['ter']) . '</b></th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">NET INCOME <i>(c - d - e - f)</i></th>

                                    <th style="text-align:right;"><b>' . number_format($record['net_income']) . '</b></th>

                                </tr>

                                <tr>

                                    <th style="text-align:center;">Print By</th>

                                    <th style="text-align:center;">Accept By</th>

                                </tr>

                                <tr>

                                    <th style="text-align:center; height:70px;"></th>

                                    <th style="text-align:center; height:70px;"></th>

                                </tr>

                                <tr>

                                    <th style="text-align:center;">' . $this->session->name . '</th>

                                    <th style="text-align:center;">' . $record['name'] . '</th>

                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div></center>';

        $html .= '</body></html>';



        $email = $this->emails->emailSalarySlip($record['email'], $record['name'], $config->name, base64_encode($html));

        if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {

            echo json_encode(array("title" => "Not Registered", "message" => "Email not register in Employee List " . $record['name'], "theme" => "error"));

        } elseif ($email) {

            echo json_encode(array("title" => "Delivered", "message" => "Salary Slip successfully sent to " . $record['email'], "theme" => "success"));

        } else {

            echo json_encode(array("title" => "Failed", "message" => "Salary Slip unseccessfull sent to " . $record['email'], "theme" => "error"));

        }

    }


    public function sendMail($option = "")

    {
        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $filter_employee = $this->input->post('data')['employee_id'];

        $username = $this->session->username;



        $period_start = date("Y-m", strtotime($filter_from));

        $period_end = date("Y-m", strtotime($filter_to));



        // Query for regular payrolls

        $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'REGULAR' as payroll_type, b.jkn, b.jamsostek AS bpjs_tk

        FROM payrolls a

        JOIN employees b ON a.employee_id = b.id

        JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

        LEFT JOIN marital_categories d ON a.ter_number = d.number

        LEFT JOIN maritals i ON a.marital = i.number

        WHERE a.period_start = '$period_start'

        AND a.period_end = '$period_end'

        AND a.employee_id LIKE '%$filter_employee%'

        GROUP BY b.id

        ORDER BY a.`name` ASC");

        $records_regular = $query->result_array();



        // Query for daily workers (payroll_harian_lepas)

        $query_daily = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'DAILY' as payroll_type, b.jkn, b.jamsostek AS bpjs_tk

        FROM payroll_harian_lepas a

        JOIN employees b ON a.employee_id = b.id

        JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

        LEFT JOIN marital_categories d ON a.ter_number = d.number

        LEFT JOIN maritals i ON a.marital = i.number

        WHERE a.period_start = '$period_start'

        AND a.period_end = '$period_end'

        AND a.employee_id LIKE '%$filter_employee%'

        GROUP BY b.id

        ORDER BY a.`name` ASC");

        $records_daily = $query_daily->result_array();

        if (!empty($records_daily)) {
            $records_daily[0]["allowance"] = "{}";
            $records_daily[0]["bpjs_company"] = "{}";
            $records_daily[0]["bpjs_company_total"] = "0";
            $records_daily[0]["correction_plus"] = "0";
            $records_daily[0]["overtime_weekday"] = "0";
            $records_daily[0]["overtime_convert_weekday"] = "0";
            $records_daily[0]["overtime_amount_weekday"] = "0";
            $records_daily[0]["overtime_holiday"] = "0";
            $records_daily[0]["overtime_convert_holiday"] = "0";
            $records_daily[0]["overtime_amount_holiday"] = "0";
            $records_daily[0]["overtime_correction"] = "0";
            $records_daily[0]["overtime_convert_correction"] = "0";
            $records_daily[0]["overtime_amount_correction"] = "0";
            $records_daily[0]["total_overtime"] = "0";
            $records_daily[0]["total_overtime_convert"] = "0";
            $records_daily[0]["total_overtime_amount"] = "0";
            $records_daily[0]["total_all_allowance"] = "0";
            $records_daily[0]["deduction_number"] = "{}";
            $records_daily[0]["deduction_amount"] = "{}";
            $records_daily[0]["deduction_absence"] = "0";
            $records_daily[0]["deduction_absence_amount"] = "0";
            $records_daily[0]["correction_minus"] = "0";
            $records_daily[0]["total_all_deduction"] = "0";
        }

        // Combine both results

        $records = array_merge($records_regular, $records_daily);



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $html = '<html>

            <style>

                body {font-family: Arial, Helvetica, sans-serif;}

                #customers {border-collapse: collapse;width: 100%;font-size: 14px;}

                #customers th {border: 1px solid black;padding: 5px;}

                #customers td {padding-left: 5px; padding-right:5px;}

                #customers tr:hover {background-color: #ddd;}

                #customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}

            </style>

            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

            <body>';

        $no = 1;

        //Looping per Employee

        foreach ($records as $record) {

            //Correction

            //jika dia ada tunjuangan ambil field dan isinya

            $q_correction_plus = $this->db->query("SELECT employee_id, `trans_date`, correction_name, remarks, SUM(amount) as amount FROM corrections WHERE employee_id='$record[employee_id]' and trans_date between '$filter_from' and '$filter_to' and correction_type = 'PLUS' GROUP BY correction_name");

            $r_correction_plus = $q_correction_plus->result_array();



            $r_correction_total = 0;

            $html_correction_plus = "";

            foreach ($r_correction_plus as $r_correction_plus_data) {

                $r_correction_total += $r_correction_plus_data['amount'];

                $html_correction_plus .= '<tr>

                                            <td style="text-align:left;">' . $r_correction_plus_data['remarks'] . '</td>

                                            <td style="text-align:right;"><b>' . number_format($r_correction_plus_data['amount']) . '</b></td>

                                        </tr>';

            }



            //Deduction Amount

            $total_deduction_amount = 0;

            foreach (json_decode($record['deduction_amount'], true) as $deduction_amount => $val_deduction_amount) {

                $total_deduction_amount += $val_deduction_amount;

            }



            //Deduction Number

            $total_deduction_number = 0;

            foreach (json_decode($record['deduction_number'], true) as $deduction_number => $val_deduction_number) {

                if ($val_deduction_number == "") {

                    $val_deduction_number = 0;

                }



                $total_deduction_number += $val_deduction_number;

            }



            //Allowance Amount

            //jika dia ada tunjuangan ambil field dan isinya

            // $r_allowance = json_decode($record['allowence'], true);

            // $arr_allowance_amount_total = 0;

            // foreach ($r_allowance as $allowance_data => $allowance_val) {

            //     $arr_allowance_amount_total += $allowance_val;

            // }



            $total_allowence = 0;

            $total_allowence_temp = 0;

            $total_allowence_fix = 0;

            $html_allowence_temp = "";

            $html_allowence_fix = "";

            foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {

                $allTemp = $this->crud->read("allowances", ["number" => $allowence], ["type" => "TEMPORARY"]);

                $allFix = $this->crud->read("allowances", ["number" => $allowence], ["type" => "FIX"]);

                

                if(!empty($allTemp)){

                    $total_allowence_temp += (int) $val_allowence;

                    if((int) $val_allowence > 0){

                        $html_allowence_temp .= '<tr>

                                                    <td style="text-align:left;">'.$allTemp->name.'</td>

                                                    <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                                </tr>';

                    }

                }



                if(!empty($allFix)){

                    $total_allowence_fix += (int) $val_allowence;

                    if((int) $val_allowence > 0){

                        $html_allowence_fix .= '<tr>

                                                    <td style="text-align:left;">'.$allFix->name.'</td>

                                                    <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                                </tr>';

                    }

                }



                $total_allowence += (int)$val_allowence;

            }

            

            //Deduction Amount

            //Jika dia ada pemotongan gaji ambil field dan isinya

            $q_deduction = $this->db->query("SELECT b.number, b.name, SUM(a.amount) as amount

                FROM deductions b

                LEFT JOIN setup_deductions a ON a.deduction_id = b.id

                WHERE a.employee_id = '$record[employee_id]'

                GROUP BY b.number, b.name 

                HAVING SUM(a.amount) > 0

                ORDER BY b.name asc");

            $r_deduction = $q_deduction->result_array();



            $arr_deduction_amount_total = 0;

            $html_deduction = "";

            foreach ($r_deduction as $deduction_data) {

                $arr_deduction_amount_total += $deduction_data['amount'];

                $html_deduction .= ' <tr>

                                        <td style="text-align:left;">' . $deduction_data['name'] . '</td>

                                        <td style="text-align:right;"><b>' . number_format($deduction_data['amount']) . '</b></td>

                                    </tr>';

            }

            //-------------------------------------------------------------------------------------------------------------------------------------------------------



            //BPJS Employee

            //Kalo dia mempunyai BPJS

            $bpjsKeys = json_decode($record['bpjs_employee'], true);

            $html_bpjs_emp = "";

            $arr_bpjs_emp_amount_total = 0;

            foreach ($bpjsKeys as $bpjs_employee => $bpjs_value) {

                $arr_bpjs = explode("_", $bpjs_employee);

                $r_bpjs_emp = $this->crud->read('bpjs', ['status' => 0], ["number" => $arr_bpjs[0]]);



                $arr_bpjs_emp_amount_total += !empty($bpjs_value) ? $bpjs_value : 0;

                if (!empty($r_bpjs_emp->employee)) {

                    $html_bpjs_emp .= ' <tr>

                                            <td style="text-align:left;">' . $r_bpjs_emp->name . ' <i style="font-size:10px;">('.$r_bpjs_emp->employee.'%)</i></td>

                                            <td style="text-align:right;"><b>' . number_format($bpjs_value) . '</b></td>

                                        </tr>';

                }

            }

            //-------------------------------------------------------------------------------------------------------------------------------------------------------



            //BPJS Company

            //Kalo dia mempunyai BPJS

            $bpjsComps = json_decode($record['bpjs_company'], true);

            $html_bpjs_com = "";

            $arr_bpjs_com_amount_total = 0;

            foreach ($bpjsComps as $bpjs_company => $bpjs_com_val) {

                $com_bpjs = explode("_", $bpjs_company);

                $r_bpjs_com = $this->crud->read('bpjs', ['status' => 0], ["number" => $com_bpjs[0]]);



                $arr_bpjs_com_amount_total += !empty($bpjs_com_val) ? $bpjs_com_val : 0;

                if (!empty($r_bpjs_com->company)) {

                    $html_bpjs_com.= ' <tr>

                                            <td style="text-align:left;">' . $r_bpjs_com->name . ' <i style="font-size:10px;">('.$r_bpjs_com->company.'%)</i></td>

                                            <td style="text-align:right;"><b>' . number_format($bpjs_com_val) . '</b></td>

                                        </tr>';

                }

            }

            //-------------------------------------------------------------------------------------------------------------------------------------------------------



            $total_income = $record['salary'] + $total_allowence + $record['total_overtime_amount'] + $record['correction_plus'];

            $total_deduction = $record['deduction_absence_amount'] + $total_deduction_amount + $arr_deduction_amount_total + $record['loan_bank'] + $record['loan_cooperative'] + $record['loan_other'] + $record['correction_minus'];

            if ($no % 2 == 0) {

                $html .= '<div style="page-break-after:always;">';

            }



            $html .= '  <div class="container" style="border:1px solid black; margin-bottom:20px; padding-top:10px; float:left;">

                                <table style="width: 100%;">

                                    <tr>

                                        <td width="80" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                            <img src="' . $config->favicon . '" width="40">

                                        </td>

                                        <td style="font-size: 20px; text-align: left; margin:2px;">

                                            <b>' . $config->name . '</b>

                                        </td>

                                        <td style="font-size: 20px; text-align: right; margin:2px;">

                                            SALARY SLIP

                                        </td>

                                    </tr>

                                </table>

                                <hr>

                                <div style="float:left; width:50%;">

                                    <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                        <tr>

                                            <th style="text-align:left; width: 30%;">Cut Off Period</th>

                                            <th style="text-align:left; width: 5%;">:</th>

                                            <th style="text-align:left; width: 60%;">' . date("d M Y", strtotime($filter_from)) . ' to ' . date("d M Y", strtotime($filter_to)) . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Employee ID</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['number'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Employee Name</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['name'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Departement</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['departement_name'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Departement Sub</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['departement_sub_name'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left; width: 30%;">National ID</th>

                                            <th style="text-align:left; width: 5%;">:</th>

                                            <th style="text-align:left;">' . $record['national_id'] . '</th>

                                        </tr>

                                    </table>

                                </div>

                                <div style="float:left; width:50%;">

                                    <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                        <tr>

                                            <th style="text-align:left;">Tax ID</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['tax_id'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">JKN No</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['jkn'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">BPJS TK</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['bpjs_tk'] . '</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Martial Status</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">(' . $record['marital'] . ') <small>' . $record['marital_name'] . '<small></th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">TER Code</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">'.$record['ter_number'].'</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">Working Days</th>

                                            <th style="text-align:left;">:</th>

                                            <th style="text-align:left;">' . $record['attandance_wd'] . ' Days</th>

                                        </tr>

                                    </table>

                                </div>

                                <div style="float:left; width:100%;">

                                    <div class="row">

                                        <div class="col p-0">

                                            <table id="customers" style="width:100%; border-right: 1px solid black;">

                                                <tr>

                                                    <th colspan="2" style="text-align:center">INCOME</th>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;" width="250">Basic Salary</td>

                                                    <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;"><b>Allowance (Fix)</b></td>

                                                    <td style="text-align:right;"></td>

                                                </tr>

                                                '.$html_allowence_fix.'

                                                <tr>

                                                    <td style="text-align:left;"><b>Allowance (Tmp)</b></td>

                                                    <td style="text-align:right;"></td>

                                                </tr>

                                                '.$html_allowence_temp.'

                                                <tr>

                                                    <td style="text-align:left;">Overtime</td>

                                                    <td style="text-align:right;">(<b>' . ($record['overtime_weekday'] + $record['overtime_holiday']) . '</b> Hour) <b>' . number_format(($record['overtime_amount_weekday'] + $record['overtime_amount_holiday'])) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;">Correction Overtime</td>

                                                    <td style="text-align:right;">(<b>' . $record['overtime_correction'] . '</b> Hour) <b>' . number_format(($record['overtime_amount_correction'])) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;">Correction</td>

                                                    <td style="text-align:right;">-</td>

                                                </tr>

                                                ' . $html_correction_plus . '

                                                <tr>

                                                    <th style="text-align:left;">TOTAL INCOME <i>(a)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format($total_income) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th colspan="2" style="text-align:center">BPJS COMPANY</th>

                                                </tr>

                                                ' . $html_bpjs_com . '

                                                <tr>

                                                    <th style="text-align:left;">TOTAL BPJS COMPANY <i>(b)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format(($arr_bpjs_com_amount_total)) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:left;">BRUTO INCOME <i>(c) = (a + b)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format($total_income + $arr_bpjs_com_amount_total) . '</b></th>

                                                </tr>

                                            </table>

                                        </div>

                                        <div class="col p-0">

                                            <table id="customers" style="width:100%; border-left: 1px solid black;">

                                                <tr>

                                                    <th colspan="2" style="text-align:center">DEDUCTION</th>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;" width="250">Absence</td>

                                                    <td style="text-align:right;" width="150">(<b>' . ($record['deduction_absence'] + $total_deduction_number) . '</b> Days) <b>' . number_format($record['deduction_absence_amount'] + $total_deduction_amount) . '</b></td>

                                                </tr>

                                                ' . @$html_deduction . '

                                                <tr>

                                                    <td style="text-align:left;">Loans</td>

                                                    <td style="text-align:right;"><b>' . number_format($record['loan_other'] + $record['loan_cooperative'] + $record['loan_bank']) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;">Correction Minus</td>

                                                    <td style="text-align:right;"><b>' . number_format($record['correction_minus']) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <td style="text-align:left;">BPJS Company</td>

                                                    <td style="text-align:right;"><b>' . number_format($arr_bpjs_com_amount_total) . '</b></td>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:left;">TOTAL DEDUCTION <i>(d)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format($total_deduction + $arr_bpjs_com_amount_total) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th colspan="2" style="text-align:center">BPJS EMPLOYEE</th>

                                                </tr>

                                                ' . $html_bpjs_emp . '

                                                <tr>

                                                    <th style="text-align:left;">TOTAL BPJS <i>(e)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format(($arr_bpjs_emp_amount_total)) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:left;">TER ('.$record['ter_persentase'].') <i>(f)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format($record['ter']) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:left;">NET INCOME <i>(c - d - e - f)</i></th>

                                                    <th style="text-align:right;"><b>' . number_format($record['net_income']) . '</b></th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:center;">Print By</th>

                                                    <th style="text-align:center;">Accept By</th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:center; height:70px;"></th>

                                                    <th style="text-align:center; height:70px;"></th>

                                                </tr>

                                                <tr>

                                                    <th style="text-align:center;">' . $this->session->name . '</th>

                                                    <th style="text-align:center;">' . $record['name'] . '</th>

                                                </tr>

                                            </table>

                                        </div>

                                    </div>

                                </div>

                            </div>';



            if ($no % 2 == 0) {

                $html .= '</div>';

            }

            $no++;

        }


        $html .= '</table>';

        $html .= '</body></html>';

        // echo $html;
        $email = $this->emails->emailSalarySlip($record['email'], $record['name'], $config->name, base64_encode($html));

        if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {

            echo json_encode(array("title" => "Not Registered", "message" => "Email not register in Employee List " . $record['name'], "theme" => "error"));

        } elseif ($email) {

            echo json_encode(array("title" => "Delivered", "message" => "Salary Slip successfully sent to " . $record['email'], "theme" => "success"));

        } else {

            echo json_encode(array("title" => "Failed", "message" => "Salary Slip unseccessfull sent to " . $record['email'], "theme" => "error"));

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=salary_slip_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            // Query for regular payrolls

            $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'REGULAR' as payroll_type, b.jkn, b.jamsostek AS bpjs_tk

            FROM payrolls a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN marital_categories d ON a.ter_number = d.number

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period_start = '$period_start'

            AND a.period_end = '$period_end'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY a.`name` ASC");

            $records_regular = $query->result_array();



            // Query for daily workers (payroll_harian_lepas)

            $query_daily = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email, d.ter as ter_persentase, 'DAILY' as payroll_type, b.jkn, b.jamsostek AS bpjs_tk

            FROM payroll_harian_lepas a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN marital_categories d ON a.ter_number = d.number

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period_start = '$period_start'

            AND a.period_end = '$period_end'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY a.`name` ASC");

            $records_daily = $query_daily->result_array();

            if (!empty($records_daily)) {
                $records_daily[0]["allowance"] = "{}";
                $records_daily[0]["bpjs_company"] = "{}";
                $records_daily[0]["bpjs_company_total"] = "0";
                $records_daily[0]["correction_plus"] = "0";
                $records_daily[0]["overtime_weekday"] = "0";
                $records_daily[0]["overtime_convert_weekday"] = "0";
                $records_daily[0]["overtime_amount_weekday"] = "0";
                $records_daily[0]["overtime_holiday"] = "0";
                $records_daily[0]["overtime_convert_holiday"] = "0";
                $records_daily[0]["overtime_amount_holiday"] = "0";
                $records_daily[0]["overtime_correction"] = "0";
                $records_daily[0]["overtime_convert_correction"] = "0";
                $records_daily[0]["overtime_amount_correction"] = "0";
                $records_daily[0]["total_overtime"] = "0";
                $records_daily[0]["total_overtime_convert"] = "0";
                $records_daily[0]["total_overtime_amount"] = "0";
                $records_daily[0]["total_all_allowance"] = "0";
                $records_daily[0]["deduction_number"] = "{}";
                $records_daily[0]["deduction_amount"] = "{}";
                $records_daily[0]["deduction_absence"] = "0";
                $records_daily[0]["deduction_absence_amount"] = "0";
                $records_daily[0]["correction_minus"] = "0";
                $records_daily[0]["total_all_deduction"] = "0";
            }

            // Combine both results
            // die(json_encode($records_daily));
            $records = array_merge($records_regular, $records_daily);



            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html>

                <head>

                    <title>Print Data</title>

                </head>

                <style>

                    body {font-family: Arial, Helvetica, sans-serif;}

                    #customers {border-collapse: collapse;width: 100%;font-size: 14px;}

                    #customers th {border: 1px solid black;padding: 5px;}

                    #customers td {padding-left: 5px; padding-right:5px;}

                    #customers tr:hover {background-color: #ddd;}

                    #customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}

                </style>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

                <body>';

            $no = 1;

            //Looping per Employee

            foreach ($records as $record) {

                //Correction

                //jika dia ada tunjuangan ambil field dan isinya

                $q_correction_plus = $this->db->query("SELECT employee_id, `trans_date`, correction_name, remarks, SUM(amount) as amount FROM corrections WHERE employee_id='$record[employee_id]' and trans_date between '$filter_from' and '$filter_to' and correction_type = 'PLUS' GROUP BY correction_name");

                $r_correction_plus = $q_correction_plus->result_array();



                $r_correction_total = 0;

                $html_correction_plus = "";

                foreach ($r_correction_plus as $r_correction_plus_data) {

                    $r_correction_total += $r_correction_plus_data['amount'];

                    $html_correction_plus .= '<tr>

                                                <td style="text-align:left;">' . $r_correction_plus_data['remarks'] . '</td>

                                                <td style="text-align:right;"><b>' . number_format($r_correction_plus_data['amount']) . '</b></td>

                                            </tr>';

                }



                //Deduction Amount

                $total_deduction_amount = 0;

                foreach (json_decode($record['deduction_amount'], true) as $deduction_amount => $val_deduction_amount) {

                    $total_deduction_amount += $val_deduction_amount;

                }



                //Deduction Number

                $total_deduction_number = 0;

                foreach (json_decode($record['deduction_number'], true) as $deduction_number => $val_deduction_number) {

                    if ($val_deduction_number == "") {

                        $val_deduction_number = 0;

                    }



                    $total_deduction_number += $val_deduction_number;

                }



                //Allowance Amount

                //jika dia ada tunjuangan ambil field dan isinya

                // $r_allowance = json_decode($record['allowence'], true);

                // $arr_allowance_amount_total = 0;

                // foreach ($r_allowance as $allowance_data => $allowance_val) {

                //     $arr_allowance_amount_total += $allowance_val;

                // }



                $total_allowence = 0;

                $total_allowence_temp = 0;

                $total_allowence_fix = 0;

                $html_allowence_temp = "";

                $html_allowence_fix = "";

                foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {

                    $allTemp = $this->crud->read("allowances", ["number" => $allowence], ["type" => "TEMPORARY"]);

                    $allFix = $this->crud->read("allowances", ["number" => $allowence], ["type" => "FIX"]);

                    

                    if(!empty($allTemp)){

                        $total_allowence_temp += (int) $val_allowence;

                        if((int) $val_allowence > 0){

                            $html_allowence_temp .= '<tr>

                                                        <td style="text-align:left;">'.$allTemp->name.'</td>

                                                        <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                                    </tr>';

                        }

                    }



                    if(!empty($allFix)){

                        $total_allowence_fix += (int) $val_allowence;

                        if((int) $val_allowence > 0){

                            $html_allowence_fix .= '<tr>

                                                        <td style="text-align:left;">'.$allFix->name.'</td>

                                                        <td style="text-align:right;"><b>'.number_format((int) $val_allowence).'</b></td>

                                                    </tr>';

                        }

                    }



                    $total_allowence += (int)$val_allowence;

                }

                

                //Deduction Amount

                //Jika dia ada pemotongan gaji ambil field dan isinya

                $q_deduction = $this->db->query("SELECT b.number, b.name, SUM(a.amount) as amount

                    FROM deductions b

                    LEFT JOIN setup_deductions a ON a.deduction_id = b.id

                    WHERE a.employee_id = '$record[employee_id]'

                    GROUP BY b.number, b.name 

                    HAVING SUM(a.amount) > 0

                    ORDER BY b.name asc");

                $r_deduction = $q_deduction->result_array();



                $arr_deduction_amount_total = 0;

                $html_deduction = "";

                foreach ($r_deduction as $deduction_data) {

                    $arr_deduction_amount_total += $deduction_data['amount'];

                    $html_deduction .= ' <tr>

                                            <td style="text-align:left;">' . $deduction_data['name'] . '</td>

                                            <td style="text-align:right;"><b>' . number_format($deduction_data['amount']) . '</b></td>

                                        </tr>';

                }

                //-------------------------------------------------------------------------------------------------------------------------------------------------------



                //BPJS Employee

                //Kalo dia mempunyai BPJS

                $bpjsKeys = json_decode($record['bpjs_employee'], true);

                $html_bpjs_emp = "";

                $arr_bpjs_emp_amount_total = 0;

                foreach ($bpjsKeys as $bpjs_employee => $bpjs_value) {

                    $arr_bpjs = explode("_", $bpjs_employee);

                    $r_bpjs_emp = $this->crud->read('bpjs', ['status' => 0], ["number" => $arr_bpjs[0]]);



                    $arr_bpjs_emp_amount_total += !empty($bpjs_value) ? $bpjs_value : 0;

                    if (!empty($r_bpjs_emp->employee)) {

                        $html_bpjs_emp .= ' <tr>

                                                <td style="text-align:left;">' . $r_bpjs_emp->name . ' <i style="font-size:10px;">('.$r_bpjs_emp->employee.'%)</i></td>

                                                <td style="text-align:right;"><b>' . number_format($bpjs_value) . '</b></td>

                                            </tr>';

                    }

                }

                //-------------------------------------------------------------------------------------------------------------------------------------------------------



                //BPJS Company

                //Kalo dia mempunyai BPJS

                $bpjsComps = json_decode($record['bpjs_company'], true);

                $html_bpjs_com = "";

                $arr_bpjs_com_amount_total = 0;

                foreach ($bpjsComps as $bpjs_company => $bpjs_com_val) {

                    $com_bpjs = explode("_", $bpjs_company);

                    $r_bpjs_com = $this->crud->read('bpjs', ['status' => 0], ["number" => $com_bpjs[0]]);



                    $arr_bpjs_com_amount_total += !empty($bpjs_com_val) ? $bpjs_com_val : 0;

                    if (!empty($r_bpjs_com->company)) {

                        $html_bpjs_com.= ' <tr>

                                                <td style="text-align:left;">' . $r_bpjs_com->name . ' <i style="font-size:10px;">('.$r_bpjs_com->company.'%)</i></td>

                                                <td style="text-align:right;"><b>' . number_format($bpjs_com_val) . '</b></td>

                                            </tr>';

                    }

                }

                //-------------------------------------------------------------------------------------------------------------------------------------------------------



                $total_income = $record['salary'] + $total_allowence + $record['total_overtime_amount'] + $record['correction_plus'];

                $total_deduction = $record['deduction_absence_amount'] + $total_deduction_amount + $arr_deduction_amount_total + $record['loan_bank'] + $record['loan_cooperative'] + $record['loan_other'] + $record['correction_minus'];

                if ($no % 2 == 0) {

                    $html .= '<div style="page-break-after:always;">';

                }



                $html .= '  <div class="container" style="border:1px solid black; margin-bottom:20px; padding-top:10px; float:left;">

                                    <table style="width: 100%;">

                                        <tr>

                                            <td width="80" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                                <img src="' . $config->favicon . '" width="40">

                                            </td>

                                            <td style="font-size: 20px; text-align: left; margin:2px;">

                                                <b>' . $config->name . '</b>

                                            </td>

                                            <td style="font-size: 20px; text-align: right; margin:2px;">

                                                SALARY SLIP

                                            </td>

                                        </tr>

                                    </table>

                                    <hr>

                                    <div style="float:left; width:50%;">

                                        <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                            <tr>

                                                <th style="text-align:left; width: 30%;">Cut Off Period</th>

                                                <th style="text-align:left; width: 5%;">:</th>

                                                <th style="text-align:left; width: 60%;">' . date("d M Y", strtotime($filter_from)) . ' to ' . date("d M Y", strtotime($filter_to)) . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Employee ID</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['number'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Employee Name</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['name'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Departement</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['departement_name'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Departement Sub</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['departement_sub_name'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left; width: 30%;">National ID</th>

                                                <th style="text-align:left; width: 5%;">:</th>

                                                <th style="text-align:left;">' . $record['national_id'] . '</th>

                                            </tr>

                                        </table>

                                    </div>

                                    <div style="float:left; width:50%;">

                                        <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                            <tr>

                                                <th style="text-align:left;">Tax ID</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['tax_id'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">JKN No</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['jkn'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">BPJS TK</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['bpjs_tk'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Martial Status</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">(' . $record['marital'] . ') <small>' . $record['marital_name'] . '<small></th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">TER Code</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">'.$record['ter_number'].'</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Working Days</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['attandance_wd'] . ' Days</th>

                                            </tr>

                                        </table>

                                    </div>

                                    <div style="float:left; width:100%;">

                                        <div class="row">

                                            <div class="col p-0">

                                                <table id="customers" style="width:100%; border-right: 1px solid black;">

                                                    <tr>

                                                        <th colspan="2" style="text-align:center">INCOME</th>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;" width="250">Basic Salary</td>

                                                        <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;"><b>Allowance (Fix)</b></td>

                                                        <td style="text-align:right;"></td>

                                                    </tr>

                                                    '.$html_allowence_fix.'

                                                    <tr>

                                                        <td style="text-align:left;"><b>Allowance (Tmp)</b></td>

                                                        <td style="text-align:right;"></td>

                                                    </tr>

                                                    '.$html_allowence_temp.'

                                                    <tr>

                                                        <td style="text-align:left;">Overtime</td>

                                                        <td style="text-align:right;">(<b>' . ($record['overtime_weekday'] + $record['overtime_holiday']) . '</b> Hour) <b>' . number_format(($record['overtime_amount_weekday'] + $record['overtime_amount_holiday'])) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;">Correction Overtime</td>

                                                        <td style="text-align:right;">(<b>' . $record['overtime_correction'] . '</b> Hour) <b>' . number_format(($record['overtime_amount_correction'])) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;">Correction</td>

                                                        <td style="text-align:right;">-</td>

                                                    </tr>

                                                    ' . $html_correction_plus . '

                                                    <tr>

                                                        <th style="text-align:left;">TOTAL INCOME <i>(a)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($total_income) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th colspan="2" style="text-align:center">BPJS COMPANY</th>

                                                    </tr>

                                                    ' . $html_bpjs_com . '

                                                    <tr>

                                                        <th style="text-align:left;">TOTAL BPJS COMPANY <i>(b)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format(($arr_bpjs_com_amount_total)) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">BRUTO INCOME <i>(c) = (a + b)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($total_income + $arr_bpjs_com_amount_total) . '</b></th>

                                                    </tr>

                                                </table>

                                            </div>

                                            <div class="col p-0">

                                                <table id="customers" style="width:100%; border-left: 1px solid black;">

                                                    <tr>

                                                        <th colspan="2" style="text-align:center">DEDUCTION</th>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;" width="250">Absence</td>

                                                        <td style="text-align:right;" width="150">(<b>' . ($record['deduction_absence'] + $total_deduction_number) . '</b> Days) <b>' . number_format($record['deduction_absence_amount'] + $total_deduction_amount) . '</b></td>

                                                    </tr>

                                                    ' . @$html_deduction . '

                                                    <tr>

                                                        <td style="text-align:left;">Loans</td>

                                                        <td style="text-align:right;"><b>' . number_format($record['loan_other'] + $record['loan_cooperative'] + $record['loan_bank']) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;">Correction Minus</td>

                                                        <td style="text-align:right;"><b>' . number_format($record['correction_minus']) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;">BPJS Company</td>

                                                        <td style="text-align:right;"><b>' . number_format($arr_bpjs_com_amount_total) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">TOTAL DEDUCTION <i>(d)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($total_deduction + $arr_bpjs_com_amount_total) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th colspan="2" style="text-align:center">BPJS EMPLOYEE</th>

                                                    </tr>

                                                    ' . $html_bpjs_emp . '

                                                    <tr>

                                                        <th style="text-align:left;">TOTAL BPJS <i>(e)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format(($arr_bpjs_emp_amount_total)) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">TER ('.$record['ter_persentase'].') <i>(f)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['ter']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">NET INCOME <i>(c - d - e - f)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['net_income']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center;">Print By</th>

                                                        <th style="text-align:center;">Accept By</th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center; height:70px;"></th>

                                                        <th style="text-align:center; height:70px;"></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center;">' . $this->session->name . '</th>

                                                        <th style="text-align:center;">' . $record['name'] . '</th>

                                                    </tr>

                                                </table>

                                            </div>

                                        </div>

                                    </div>

                                </div>';



                if ($no % 2 == 0) {

                    $html .= '</div>';

                }

                $no++;

            }



            $html .= '</body></html>';

            $html .= '</table>';

            echo $html;
        }

    }



    public function print_recap($option = "")

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

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            //Select Query for regular payrolls

            $this->db->select('

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                c.name as departement_name, 

                d.name as departement_sub_name, 

                e.name as group_name, 

                COUNT(b.id) as employee, 

                SUM(a.net_income) as income,

                "REGULAR" as payroll_type');

            $this->db->from('payrolls a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period_start =', $period_start);

            $this->db->where('a.period_end =', $period_end);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id", "e.id"));

            $this->db->order_by('c.name', 'ASC');

            $this->db->order_by('SUM(a.net_income)', 'ASC');

            //Get Data Array

            $payrolls_regular = $this->db->get()->result_array();



            //Select Query for daily workers (payroll_harian_lepas)

            $this->db->select('

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                c.name as departement_name, 

                d.name as departement_sub_name, 

                e.name as group_name, 

                COUNT(b.id) as employee, 

                SUM(a.net_income) as income,

                "DAILY" as payroll_type');

            $this->db->from('payroll_harian_lepas a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period_start =', $period_start);

            $this->db->where('a.period_end =', $period_end);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id", "e.id"));

            $this->db->order_by('c.name', 'ASC');

            $this->db->order_by('SUM(a.net_income)', 'ASC');

            //Get Data Array

            $payrolls_daily = $this->db->get()->result_array();



            // Combine both results

            $payrolls = array_merge($payrolls_regular, $payrolls_daily);



            //Config Page

            $rows = 40;

            $page = ceil(count($payrolls) / $rows);



            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>';

            $no = 1;

            $hal = 1;

            for ($i = 0; $i < $page; $i++) {

                //Select Query for regular payrolls

                $this->db->select('

                    b.departement_id,

                    b.departement_sub_id,

                    b.group_id,

                    c.name as departement_name, 

                    d.name as departement_sub_name, 

                    e.name as group_name, 

                    COUNT(b.id) as employee, 

                    SUM(a.net_income) as income,
                    "REGULAR" as payroll_type');

                $this->db->from('payrolls a');

                $this->db->join('employees b', "a.employee_id = b.id");

                $this->db->join('departements c', "b.departement_id = c.id");

                $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

                $this->db->join('groups e', "b.group_id = e.id");

                $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

                $this->db->where('a.deleted', 0);

                $this->db->where('a.period_start =', $period_start);

                $this->db->where('a.period_end =', $period_end);

                $this->db->like('b.id', $filter_employee);

                $this->db->like('b.division_id', $filter_division);

                $this->db->like('b.departement_id', $filter_departement);

                $this->db->like('b.departement_sub_id', $filter_departement_sub);

                $this->db->like('b.group_id', $filter_group);

                $this->db->group_by(array("c.id", "d.id"));

                $this->db->order_by('c.name', 'ASC');

                $this->db->order_by('SUM(a.net_income)', 'ASC');

                $this->db->limit(50, ($i * 50));

                //Get Data Array

                $records_regular = $this->db->get()->result_array();

                //Select Query for daily workers (payroll_harian_lepas)
                $this->db->select('
                    b.departement_id,
                    b.departement_sub_id,
                    b.group_id,
                    c.name as departement_name, 
                    d.name as departement_sub_name, 
                    e.name as group_name, 
                    COUNT(b.id) as employee, 
                    SUM(a.net_income) as income,
                    "DAILY" as payroll_type');
                $this->db->from('payroll_harian_lepas a');
                $this->db->join('employees b', "a.employee_id = b.id");
                $this->db->join('departements c', "b.departement_id = c.id");
                $this->db->join('departement_subs d', "b.departement_sub_id = d.id");
                $this->db->join('groups e', "b.group_id = e.id");
                $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");
                $this->db->where('a.deleted', 0);
                $this->db->where('a.period_start =', $period_start);
                $this->db->where('a.period_end =', $period_end);
                $this->db->like('b.id', $filter_employee);
                $this->db->like('b.division_id', $filter_division);
                $this->db->like('b.departement_id', $filter_departement);
                $this->db->like('b.departement_sub_id', $filter_departement_sub);
                $this->db->like('b.group_id', $filter_group);
                $this->db->group_by(array("c.id", "d.id"));
                $this->db->order_by('c.name', 'ASC');
                $this->db->order_by('SUM(a.net_income)', 'ASC');
                $this->db->limit(50, ($i * 50));
                //Get Data Array
                $records_daily = $this->db->get()->result_array();

                // Combine both results
                $records = array_merge($records_regular, $records_daily);



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

                            <h3 style="margin:0;">REPORT RECAP SALARY SLIP</h3>

                            <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>

                            <br>

                        </center>

                        

                        <table id="customers" border="1">

                            <tr>

                                <th width="20" style="text-align:center;">No</th>

                                <th style="text-align:center;">Departement</th>

                                <th style="text-align:center;">Departement Sub</th>

                                <th style="text-align:center;">Employee</th>

                            </tr>';

                $totalEmployee = 0;

                $totalIncome = 0;

                foreach ($records as $data) {

                    $html .= '  <tr>

                                    <td style="text-align:center;">' . $no . '</td>

                                    <td>' . $data['departement_name'] . '</td>

                                    <td>' . $data['departement_sub_name'] . '</td>

                                    <td style="text-align:right;">' . number_format($data['employee']) . '</td>

                                </tr>';

                    $totalEmployee += $data['employee'];

                    $totalIncome += $data['income'];

                    $no++;

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

}

