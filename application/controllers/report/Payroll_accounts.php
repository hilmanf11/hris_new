<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Payroll_accounts extends CI_Controller

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

            $this->load->view('report/payroll_accounts');

        } else {

            redirect('error_access');

        }

    }



    public function readYears()

    {

        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));

        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));

        for ($i = $tahun_next; $i >= $tahun_before; $i--) {

            $arr[] = array("id" => $i, "name" => $i);

        }



        echo json_encode($arr);

    }



    public function readMonths()

    {

        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');

        foreach ($months as $key => $value) {

            $arr[] = array("id" => $key, "name" => $value);

        }



        echo json_encode($arr);

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=payroll_records_$format.xls");

        }



        if ($this->input->get()) {

            $filter_month_start = $this->input->get('filter_month_start');

            $filter_year_start = $this->input->get('filter_year_start');

            $filter_month_end = $this->input->get('filter_month_end');

            $filter_year_end = $this->input->get('filter_year_end');



            $period_start2 = date("F Y", strtotime($filter_year_start."-".$filter_month_start."-01"));

            $period_end2 = date("F Y", strtotime($filter_year_end."-".$filter_month_end."-01"));



            $period_start = date("F Y", strtotime($filter_year_start."-".$filter_month_start."-01"));

            $period_end = date("F Y", strtotime($filter_year_end."-".$filter_month_end."-01"));



            $divisions = $this->crud->reads("divisions", [], [], "", "name", "asc");



            $countMonth = 0;

            $htmlDivision = "";

            $htmlMp = "";

            $width = 100;

            $width2 = 100;

            $nextWidth = 0;

            $nextWidth2 = 0;

            $colspan = 5;

            while (strtotime($period_start) <= strtotime($period_end)) {

                $monthName = date("F Y", strtotime($period_start));

                $htmlDivision .= "<th colspan='2' style='text-align:center;'>".$monthName."</th>";



                $htmlMp .= "<th style='text-align:center;' width='80'>MP</th>

                            <th style='text-align:center;' width='80'>Amount</th>";



                $period_start = date("F Y", strtotime("+1 month", strtotime($period_start)));

                $countMonth++;

                $width += $nextWidth;

                $width2 += $nextWidth2;

                $colspan += 2;

                $nextWidth = 10;

                $nextWidth2 = 5;

            }



            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: '.$width.'%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>

                    <style> .str{ mso-number-format:\@; } </style>

                    <body>

                    <center>

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

                        <h3 style="margin:0;">Payroll Accounts</h3>

                        <p style="margin:0;">Period ' . $period_start2 . ' to ' . $period_end2 . '</p>

                    </center>

                    <br>';



//----------------------------------------------------- Detail By Division Karyawan

        $html .= '  <table id="customers" border="1">

                        <tr>

                            <th rowspan="2" style="text-align:center;" width="50">No</th>

                            <th rowspan="2" style="text-align:center;" width="200">Division, Job Type, Account No</th>

                            <th rowspan="2" style="text-align:center;" width="200">Departement</th>

                            <th rowspan="2" style="text-align:center;" width="150">Position</th>

                            '.$htmlDivision.'

                            <th colspan="2" style="text-align:center;" width="100">Total</th>

                        </tr>

                        <tr>

                            '.$htmlMp.'

                            <th style="text-align:center;" width="80">MP</th>

                            <th style="text-align:center;" width="80">Amount</th>

                        </tr>';

        $no = 1;

        foreach ($divisions as $division) {

            $jobtypes = $this->crud->query("SELECT DISTINCT job_type FROM account_coa WHERE division_id = '$division->id'");



            $html .= '  <tr style="background:#c6ffbf;">

                            <td>'.$no.'</td>

                            <td colspan="'.$colspan.'" style="font-weight:bold;">'.$division->name.'</td>

                        </tr>';

            $nojob = 1;

            foreach ($jobtypes as $jobtype) {

                $accounts = $this->crud->query("SELECT DISTINCT b.number as account_no, b.id as account_id FROM account_coa a JOIN accounts b ON a.account_id = b.id WHERE a.division_id = '$division->id' and a.job_type = '$jobtype->job_type' and b.category = 'payroll'");



                $html .= '  <tr style="background:#bfedff;">

                                <td>'.$no.'.'.$nojob.'</td>

                                <td colspan="'.$colspan.'" style="font-weight:bold; padding-left:20px;">'.$jobtype->job_type.'</td>

                            </tr>';



                foreach ($accounts as $account) {

                    $records = $this->crud->query("SELECT

                        a.*,

                        c.name as departement_name,

                        e.name as position_name,

                        f.name as contract_name,

                        g.number as account_number,

                        g.name as account_name

                    FROM account_coa a

                    JOIN departements c ON a.division_id = c.division_id and a.departement_id = c.id

                    JOIN positions e ON a.position_id = e.id

                    JOIN contracts f ON a.contract_id = f.id

                    JOIN accounts g ON a.account_id = g.id and g.category = 'payroll'

                    WHERE a.division_id = '$division->id' and a.job_type = '$jobtype->job_type' and g.number = '$account->account_no'

                    GROUP BY c.id, e.id

                    ORDER BY c.name, e.name asc");

                    

                    $norec = 1;

                    foreach ($records as $record) {

                        $start = date("Y-m", strtotime($filter_year_start."-".$filter_month_start."-01"));

                        $end = date("Y-m", strtotime($filter_year_end."-".$filter_month_end."-01"));



                        $contentMp = "";

                        $totalMp = 0;

                        $totalAmount = 0;

                        while (strtotime($start) <= strtotime($end)) {

                            $employee = $this->crud->query("SELECT
                                (COUNT(i.id) + COUNT(j.id) + COUNT(hl.id)) as mp,
                                (COALESCE(SUM(i.net_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.net_income), 0)) as amount
                            FROM account_coa a
                            JOIN accounts b ON a.account_id = b.id and b.category = 'payroll'
                            LEFT JOIN employees h ON a.division_id = h.division_id and a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
                            LEFT JOIN payrolls i ON h.id = i.employee_id and DATE_FORMAT(STR_TO_DATE(i.period_start, '%Y-%m'), '%Y-%m') = '$start'
                            LEFT JOIN payroll_pkl j ON h.id = j.employee_id and DATE_FORMAT(STR_TO_DATE(j.period_start, '%Y-%m'), '%Y-%m') = '$start'
                            LEFT JOIN payroll_harian_lepas hl ON h.id = hl.employee_id and DATE_FORMAT(STR_TO_DATE(hl.period_start, '%Y-%m'), '%Y-%m') = '$start'
                            WHERE a.division_id = '$division->id' and a.job_type = '$jobtype->job_type' and a.account_id = '$record->account_id' 
                            and a.departement_id = '$record->departement_id' and a.position_id = '$record->position_id'");

            

                            $contentMp .= "<th style='text-align:right;'>".@$employee[0]->mp."</th>

                                        <th style='text-align:right;'>".number_format(@$employee[0]->amount)."</th>";

                            

                            $totalMp += @$employee[0]->mp;

                            $totalAmount += @$employee[0]->amount;

                            $start = date("Y-m", strtotime("+1 month", strtotime($start)));

                        }



                        $html .= '  <tr>

                                        <td>'.$no.'.'.$nojob.'.'.$norec.'</td>

                                        <td style="padding-left:40px;">'.$record->account_number.'</td>

                                        <td>'.$record->departement_name.'</td>

                                        <td>'.$record->position_name.'</td>

                                        '.$contentMp.'

                                        <th style="text-align:right;">'.$totalMp.'</th>

                                        <th style="text-align:right;">'.number_format($totalAmount).'</th>

                                    </tr>';

            

                        $norec++;

                    }



                    $start2 = date("Y-m", strtotime($filter_year_start."-".$filter_month_start."-01"));

                    $end2 = date("Y-m", strtotime($filter_year_end."-".$filter_month_end."-01"));



                    $contentMpAccount = "";

                    $totalMpAccount = 0;

                    $totalAmountAccount = 0;

                    while (strtotime($start2) <= strtotime($end2)) {

                        $employeeAccount = $this->crud->query("SELECT
                            (COUNT(i.id) + COUNT(j.id) + COUNT(hl.id)) as mp,
                            (COALESCE(SUM(i.net_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.net_income), 0)) as amount
                        FROM account_coa a
                        JOIN accounts b ON a.account_id = b.id and b.category = 'payroll'
                        LEFT JOIN employees h ON a.division_id = h.division_id and a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
                        LEFT JOIN payrolls i ON h.id = i.employee_id and DATE_FORMAT(STR_TO_DATE(i.period_start, '%Y-%m'), '%Y-%m') = '$start2'
                        LEFT JOIN payroll_pkl j ON h.id = j.employee_id and DATE_FORMAT(STR_TO_DATE(j.period_start, '%Y-%m'), '%Y-%m') = '$start2'
                        LEFT JOIN payroll_harian_lepas hl ON h.id = hl.employee_id and DATE_FORMAT(STR_TO_DATE(hl.period_start, '%Y-%m'), '%Y-%m') = '$start2'
                        WHERE a.division_id = '$division->id' and a.job_type = '$jobtype->job_type' and a.account_id = '$account->account_id'");

        

                        $contentMpAccount .= "<th style='font-weight:bold; text-align:right;'>".@$employeeAccount[0]->mp."</th>

                                              <th style='font-weight:bold; text-align:right;'>".number_format(@$employeeAccount[0]->amount)."</th>";

                        

                        $totalMpAccount += @$employeeAccount[0]->mp;

                        $totalAmountAccount += @$employeeAccount[0]->amount;

                        $start2 = date("Y-m", strtotime("+1 month", strtotime($start2)));

                    }



                    $html .= '  <tr>

                                    <td colspan="4" style="font-weight:bold; text-align:right;">Total ('.$account->account_no.')</td>

                                    '.$contentMpAccount.'

                                    <th style="font-weight:bold; text-align:right;">'.$totalMpAccount.'</th>

                                    <th style="font-weight:bold; text-align:right;">'.number_format($totalAmountAccount).'</th>

                                </tr>';

                }

                $nojob++;

            }

            $no++;

        }

        

        $start3 = date("Y-m", strtotime($filter_year_start."-".$filter_month_start."-01"));

        $end3 = date("Y-m", strtotime($filter_year_end."-".$filter_month_end."-01"));



        $contentMpGrand = "";

        $totalMpGrand = 0;

        $totalAmountGrand = 0;

        while (strtotime($start3) <= strtotime($end3)) {

            $employeeGrand = $this->crud->query("SELECT
                (COUNT(i.id) + COUNT(j.id) + COUNT(hl.id)) as mp,
                (COALESCE(SUM(i.net_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.net_income), 0)) as amount
            FROM account_coa a
            JOIN accounts b ON a.account_id = b.id and b.category = 'payroll'
            LEFT JOIN employees h ON a.division_id = h.division_id and a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
            LEFT JOIN payrolls i ON h.id = i.employee_id and DATE_FORMAT(STR_TO_DATE(i.period_start, '%Y-%m'), '%Y-%m') = '$start3'
            LEFT JOIN payroll_pkl j ON h.id = j.employee_id and DATE_FORMAT(STR_TO_DATE(j.period_start, '%Y-%m'), '%Y-%m') = '$start3'
            LEFT JOIN payroll_harian_lepas hl ON h.id = hl.employee_id and DATE_FORMAT(STR_TO_DATE(hl.period_start, '%Y-%m'), '%Y-%m') = '$start3'");



            $contentMpGrand .= "<th style='font-weight:bold; text-align:right;'>".@$employeeGrand[0]->mp."</th>

                                <th style='font-weight:bold; text-align:right;'>".number_format(@$employeeGrand[0]->amount)."</th>";

            

            $totalMpGrand += @$employeeGrand[0]->mp;

            $totalAmountGrand += @$employeeGrand[0]->amount;

            $start3 = date("Y-m", strtotime("+1 month", strtotime($start3)));

        }



//----------------------------------------------------- Summary

        $html .= '<tr>

                    <th style="text-align:right;" colspan="4">GRAND TOTAL</th>

                    '.$contentMpGrand.'

                    <th style="font-weight:bold; text-align:right;">'.$totalMpGrand.'</th>

                    <th style="font-weight:bold; text-align:right;">'.number_format($totalAmountGrand).'</th>

                </tr>

                </table>

                <br>

                <div style="float:left; width:100%;">

                <table id="customers" border="1" style="width:'.$width2.'%;">

                    <tr>

                        <th rowspan="2" style="text-align:center;" width="20">No</th>

                        <th rowspan="2" style="text-align:center;" width="80">Account No</th>

                        <th rowspan="2" style="text-align:center;" width="200">Description</th>

                        '.$htmlDivision.'

                        <th colspan="2" style="text-align:center;" width="100">Total</th>

                    </tr>

                    <tr>

                        '.$htmlMp.'

                        <th style="text-align:center;" width="80">MP</th>

                        <th style="text-align:center;" width="80">Amount</th>

                    </tr>';



            $summaries = $this->crud->query("SELECT

                g.id as account_id,

                g.number as account_number,

                g.name as account_name,

                g.description

            FROM account_coa a

            JOIN accounts g ON a.account_id = g.id

            WHERE g.category = 'payroll'

            GROUP BY g.number

            ORDER BY g.number asc");



            $nos = 1;

            foreach ($summaries as $summary) {

                $sumstart = date("Y-m", strtotime($filter_year_start."-".$filter_month_start."-01"));

                $sumend = date("Y-m", strtotime($filter_year_end."-".$filter_month_end."-01"));



                $contentMpsummary = "";

                $totalMpsummary = 0;

                $totalAmountsummary = 0;

                while (strtotime($sumstart) <= strtotime($sumend)) {

                    $employeesummary = $this->crud->query("SELECT
                        (COUNT(i.id) + COUNT(j.id) + COUNT(hl.id)) as mp,
                        (COALESCE(SUM(i.net_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.net_income), 0)) as amount
                    FROM account_coa a
                    JOIN accounts b ON a.account_id = b.id and b.category = 'payroll'
                    LEFT JOIN employees h ON a.division_id = h.division_id and a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
                    LEFT JOIN payrolls i ON h.id = i.employee_id and DATE_FORMAT(STR_TO_DATE(i.period_start, '%Y-%m'), '%Y-%m') = '$sumstart'
                    LEFT JOIN payroll_pkl j ON h.id = j.employee_id and DATE_FORMAT(STR_TO_DATE(j.period_start, '%Y-%m'), '%Y-%m') = '$sumstart'
                    LEFT JOIN payroll_harian_lepas hl ON h.id = hl.employee_id and DATE_FORMAT(STR_TO_DATE(hl.period_start, '%Y-%m'), '%Y-%m') = '$sumstart'
                    WHERE a.account_id = '$summary->account_id'");



                    $contentMpsummary .= "  <td style='text-align:right;'>".@$employeesummary[0]->mp."</td>

                                            <td style='text-align:right;'>".number_format(@$employeesummary[0]->amount)."</td>";

                    

                    $totalMpsummary += @$employeesummary[0]->mp;

                    $totalAmountsummary += @$employeesummary[0]->amount;

                    $sumstart = date("Y-m", strtotime("+1 month", strtotime($sumstart)));

                }



                $html .= "  <tr>

                                <td>".$nos."</td>

                                <td>".$summary->account_number."</td>

                                <td>".$summary->description."</td>

                                ".$contentMpsummary."

                                <td style='text-align:right;'>".@$totalMpsummary."</td>

                                <td style='text-align:right;'>".number_format(@$totalAmountsummary)."</td>

                            </tr>";

                $nos++;

            }



            $start4 = date("Y-m", strtotime($filter_year_start."-".$filter_month_start."-01"));

            $end4 = date("Y-m", strtotime($filter_year_end."-".$filter_month_end."-01"));



            $contentMpGrand = "";

            $totalMpGrand = 0;

            $totalAmountGrand = 0;

            while (strtotime($start4) <= strtotime($end4)) {

                $employeeGrand = $this->crud->query("SELECT
                    (COUNT(i.id) + COUNT(j.id) + COUNT(hl.id)) as mp,
                    (COALESCE(SUM(i.net_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.net_income), 0)) as amount
                FROM account_coa a
                JOIN accounts b ON a.account_id = b.id and b.category = 'payroll'
                LEFT JOIN employees h ON a.division_id = h.division_id and a.departement_id = h.departement_id and a.position_id = h.position_id and a.contract_id = h.contract_id
                LEFT JOIN payrolls i ON h.id = i.employee_id and DATE_FORMAT(STR_TO_DATE(i.period_start, '%Y-%m'), '%Y-%m') = '$start4'
                LEFT JOIN payroll_pkl j ON h.id = j.employee_id and DATE_FORMAT(STR_TO_DATE(j.period_start, '%Y-%m'), '%Y-%m') = '$start4'
                LEFT JOIN payroll_harian_lepas hl ON h.id = hl.employee_id and DATE_FORMAT(STR_TO_DATE(hl.period_start, '%Y-%m'), '%Y-%m') = '$start4'");



                $contentMpGrand .= "<th style='font-weight:bold; text-align:right;'>".@$employeeGrand[0]->mp."</th>

                                    <th style='font-weight:bold; text-align:right;'>".number_format(@$employeeGrand[0]->amount)."</th>";

                

                $totalMpGrand += @$employeeGrand[0]->mp;

                $totalAmountGrand += @$employeeGrand[0]->amount;

                $start4 = date("Y-m", strtotime("+1 month", strtotime($start4)));

            }



            $html .= "  <tr>

                            <td colspan='3' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>

                            ".$contentMpGrand."

                            <td style='text-align:right; font-weight:bold;'>".number_format($totalMpGrand)."</td>

                            <td style='text-align:right; font-weight:bold;'>".number_format($totalAmountGrand)."</td>

                        </tr>";

                

            $html .= '</table><br>

                    </div>

                    <div style="float:left; width:50%;">

                        <center>

                    <table id="customers" style="width:100%;">

                        <tr>

                            <th rowspan="2" style="text-align:center;">APPROVED</th>

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

                </center>

                </div>';

                

                $html .= '</body></html>';

                echo $html;

        }

    }

}

