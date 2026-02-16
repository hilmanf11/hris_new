<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Payroll_records extends CI_Controller

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

            $this->load->view('report/payroll_records');

        } else {

            redirect('error_access');

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=payroll_records_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_type = $this->input->get('filter_type');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            if($filter_type == "Karyawan"){
                $where = "AND a.contract_id NOT IN('20221119000004','20221119000006','20221119000003')";
            }elseif($filter_type == "Magang"){
                $where = "AND a.contract_id ='20221119000004'";
            }elseif($filter_type == "PKL"){
                $where = "AND a.contract_id ='20221119000006'";
            }elseif($filter_type == "Harian Lepas"){
                $where = "AND a.contract_id ='20221119000003'";
            }else{
                $where = "";
            }



            $records = $this->crud->query("SELECT a.departement_id,

                n.id as division_id,

                n.name as division_name,

                b.name as departement_name, 

                o.name as departement_sub_name,

                q.name as source_name,

                a.job_type, 

                c.name as position_name, 

                d.name as contract_name, 

                e.name as account_name, 

                (COALESCE(SUM(f.total), 0) + COALESCE(SUM(j.total), 0) + COALESCE(SUM(hl.total), 0)) as total_mp, 

                (COALESCE(SUM(f.total_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.total_income), 0)) as total_salary, 

                (COALESCE(SUM(g.total), 0) + COALESCE(SUM(k.total), 0) + COALESCE(SUM(hl_cash.total), 0)) as cash_mp, 

                (COALESCE(SUM(g.total_income), 0) + COALESCE(SUM(k.total_income), 0) + COALESCE(SUM(hl_cash.total_income), 0)) as total_cash, 

                (COALESCE(SUM(h.total), 0) + COALESCE(SUM(l.total), 0) + COALESCE(SUM(hl_bsi.total), 0)) as bsi_mp, 

                (COALESCE(SUM(h.total_income), 0) + COALESCE(SUM(l.total_income), 0) + COALESCE(SUM(hl_bsi.total_income), 0)) as total_bsi, 

                (COALESCE(SUM(i.total), 0) + COALESCE(SUM(m.total), 0) + COALESCE(SUM(hl_mandiri.total), 0)) as mandiri_mp, 

                (COALESCE(SUM(i.total_income), 0) + COALESCE(SUM(m.total_income), 0) + COALESCE(SUM(hl_mandiri.total_income), 0)) as total_mandiri

            FROM account_coa a

            JOIN departements b ON a.departement_id = b.id

            JOIN divisions n ON b.division_id = n.id

            JOIN departement_subs o ON b.division_id = o.division_id and b.id = o.departement_id

            JOIN positions c ON a.position_id = c.id

            JOIN contracts d ON a.contract_id = d.id

            JOIN accounts e ON a.account_id = e.id

            LEFT JOIN (SELECT division_id, departement_id, departement_sub_id, position_id, contract_id, source_id FROM employees GROUP BY source_id) p ON p.division_id = n.id and a.departement_id = p.departement_id and p.departement_sub_id = o.id and a.position_id = p.position_id and a.contract_id = p.contract_id

            LEFT JOIN sources q ON p.source_id = q.id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) f ON f.division_id = n.id and a.departement_id = f.departement_id and f.departement_sub_id = o.id and a.position_id = f.position_id and a.contract_id = f.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) g ON g.division_id = n.id and a.departement_id = g.departement_id and g.departement_sub_id = o.id and a.position_id = g.position_id and a.contract_id = g.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) h ON h.division_id = n.id and a.departement_id = h.departement_id and h.departement_sub_id = o.id and a.position_id = h.position_id and a.contract_id = h.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Mandiri' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) i ON i.division_id = n.id and a.departement_id = i.departement_id and i.departement_sub_id = o.id and a.position_id = i.position_id and a.contract_id = i.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) j ON j.division_id = n.id and a.departement_id = j.departement_id and j.departement_sub_id = o.id and a.position_id = j.position_id and a.contract_id = j.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) k ON k.division_id = n.id and a.departement_id = k.departement_id and k.departement_sub_id = o.id and a.position_id = k.position_id and a.contract_id = k.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) l ON l.division_id = n.id and a.departement_id = l.departement_id and l.departement_sub_id = o.id and a.position_id = l.position_id and a.contract_id = l.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' and a.bank_name = 'Bank Mandiri' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) m ON m.division_id = n.id and a.departement_id = m.departement_id and m.departement_sub_id = o.id and a.position_id = m.position_id and a.contract_id = m.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payroll_harian_lepas b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) hl ON hl.division_id = n.id and a.departement_id = hl.departement_id and hl.departement_sub_id = o.id and a.position_id = hl.position_id and a.contract_id = hl.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payroll_harian_lepas b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and (a.bank_name = '-' or a.bank_name = '') GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) hl_cash ON hl_cash.division_id = n.id and a.departement_id = hl_cash.departement_id and hl_cash.departement_sub_id = o.id and a.position_id = hl_cash.position_id and a.contract_id = hl_cash.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payroll_harian_lepas b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Syariah Indonesia' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) hl_bsi ON hl_bsi.division_id = n.id and a.departement_id = hl_bsi.departement_id and hl_bsi.departement_sub_id = o.id and a.position_id = hl_bsi.position_id and a.contract_id = hl_bsi.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payroll_harian_lepas b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' and a.bank_name = 'Bank Mandiri' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) hl_mandiri ON hl_mandiri.division_id = n.id and a.departement_id = hl_mandiri.departement_id and hl_mandiri.departement_sub_id = o.id and a.position_id = hl_mandiri.position_id and a.contract_id = hl_mandiri.contract_id

            WHERE a.departement_id LIKE '%$filter_departement%' $where

            GROUP BY n.id, a.job_type, a.departement_id, o.id, a.position_id, a.contract_id, a.account_id

            HAVING (COALESCE(SUM(f.total), 0) + COALESCE(SUM(j.total), 0) + COALESCE(SUM(hl.total), 0)) > 0 or (COALESCE(SUM(g.total), 0) + COALESCE(SUM(k.total), 0) + COALESCE(SUM(hl_cash.total), 0)) > 0 or (COALESCE(SUM(h.total), 0) + COALESCE(SUM(l.total), 0) + COALESCE(SUM(hl_bsi.total), 0)) > 0 or (COALESCE(SUM(i.total), 0) + COALESCE(SUM(m.total), 0) + COALESCE(SUM(hl_mandiri.total), 0)) > 0

            ORDER BY n.name, a.job_type, b.name, o.name, a.position_id, a.contract_id asc");



            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>

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

                        <h3 style="margin:0;">Payroll Records</h3>

                        <p style="margin:0;"><b>Employee Payroll Method</b></p>

                        <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>

                    </center>

                    <br>';



                $html .= '  <table id="customers" border="1">

                                <tr>

                                    <th style="text-align:center;" width="50">No</th>

                                    <th style="text-align:center;" width="200">Plant</th>

                                    <th style="text-align:center;" width="80">Job Type</th>

                                    <th style="text-align:center;" width="200">Departement</th>

                                    <th style="text-align:center;" width="200">Departement Sub</th>

                                    <th style="text-align:center;" width="150">Position</th>

                                    <th style="text-align:center;" width="150">Employee Type</th>

                                    <th style="text-align:center;" width="150">Source</th>

                                    <th style="text-align:center;" width="100">COA</th>

                                    <th style="text-align:center;" width="80">MP</th>

                                    <th style="text-align:center;" width="100">Amount</th>

                                    <th style="text-align:center;" width="80">MP</th>

                                    <th style="text-align:center;" width="100">CASH</th>

                                    <th style="text-align:center;" width="80">MP</th>

                                    <th style="text-align:center;" width="100">BSM</th>

                                    <th style="text-align:center;" width="80">MP</th>

                                    <th style="text-align:center;" width="100">Mandiri</th>

                                </tr>';

                $no = 1;

                $total_mp = 0;

                $total_salary = 0;

                $cash_mp = 0;

                $total_cash = 0;

                $bsi_mp = 0;

                $total_bsi = 0;

                $mandiri_mp = 0;

                $total_mandiri = 0;

                foreach ($records as $record) {

                    $html .= "  <tr>

                                    <td>".$no."</td>

                                    <td>".$record->division_name."</td>

                                    <td>".$record->job_type."</td>

                                    <td>".$record->departement_name."</td>

                                    <td>".$record->departement_sub_name."</td>

                                    <td>".$record->position_name."</td>

                                    <td>".$record->contract_name."</td>

                                    <td>".$record->source_name."</td>

                                    <td>".$record->account_name."</td>

                                    <td style='text-align:right;'>".number_format($record->total_mp)."</td>

                                    <td style='text-align:right;'>".number_format($record->total_salary)."</td>

                                    <td style='text-align:right;'>".number_format($record->cash_mp)."</td>

                                    <td style='text-align:right;'>".number_format($record->total_cash)."</td>

                                    <td style='text-align:right;'>".number_format($record->bsi_mp)."</td>

                                    <td style='text-align:right;'>".number_format($record->total_bsi)."</td>

                                    <td style='text-align:right;'>".number_format($record->mandiri_mp)."</td>

                                    <td style='text-align:right;'>".number_format($record->total_mandiri)."</td>

                                </tr>";



                    $total_mp += $record->total_mp;

                    $total_salary += $record->total_salary;

                    $cash_mp += $record->cash_mp;

                    $total_cash += $record->total_cash;

                    $bsi_mp += $record->bsi_mp;

                    $total_bsi += $record->total_bsi;

                    $mandiri_mp += $record->mandiri_mp;

                    $total_mandiri += $record->total_mandiri;

                    $no++;

                }

                $html .= "  <tr>

                                <td colspan=9 style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mp)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($total_salary)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($cash_mp)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($total_cash)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($bsi_mp)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($total_bsi)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($mandiri_mp)."</td>

                                <td style='text-align:right; font-weight:bold;'>".number_format($total_mandiri)."</td>

                            </tr>";

                $html .= '</table>

                <br>

                <table id="customers" border="1" style="width:30%;">

                    <tr>

                        <th style="text-align:center;" width="50">No</th>

                        <th style="text-align:center;" width="100">Account No</th>

                        <th style="text-align:center;" width="200">Total</th>

                    </tr>';



            $summaries = $this->crud->query("SELECT e.name,

                (COALESCE(SUM(f.total), 0) + COALESCE(SUM(j.total), 0) + COALESCE(SUM(hl.total), 0)) as total_mp, 

                (COALESCE(SUM(f.total_income), 0) + COALESCE(SUM(j.total_income), 0) + COALESCE(SUM(hl.total_income), 0)) as total_salary

            FROM account_coa a

            JOIN departements b ON a.departement_id = b.id

            JOIN divisions n ON b.division_id = n.id

            JOIN departement_subs o ON b.division_id = o.division_id and b.id = o.departement_id

            JOIN positions c ON a.position_id = c.id

            JOIN contracts d ON a.contract_id = d.id

            JOIN accounts e ON a.account_id = e.id

            LEFT JOIN (SELECT division_id, departement_id, departement_sub_id, position_id, contract_id, source_id FROM employees GROUP BY source_id) p ON p.division_id = n.id and a.departement_id = p.departement_id and p.departement_sub_id = o.id and a.position_id = p.position_id and a.contract_id = p.contract_id

            LEFT JOIN sources q ON p.source_id = q.id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payrolls b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) f ON f.division_id = n.id and a.departement_id = f.departement_id and f.departement_sub_id = o.id and a.position_id = f.position_id and a.contract_id = f.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.total_income) as total_income FROM employees a JOIN payroll_pkl b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$filter_from' and b.period_end = '$filter_to' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) j ON j.division_id = n.id and a.departement_id = j.departement_id and j.departement_sub_id = o.id and a.position_id = j.position_id and a.contract_id = j.contract_id

            LEFT JOIN (SELECT a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id, COUNT(a.id) as total, SUM(b.net_income) as total_income FROM employees a JOIN payroll_harian_lepas b ON a.id = b.employee_id WHERE a.status = 0 and b.period_start = '$period_start' and b.period_end = '$period_end' GROUP BY a.division_id, a.departement_id, a.departement_sub_id, a.position_id, a.contract_id) hl ON hl.division_id = n.id and a.departement_id = hl.departement_id and hl.departement_sub_id = o.id and a.position_id = hl.position_id and a.contract_id = hl.contract_id

            WHERE a.departement_id LIKE '%$filter_departement%' $where

            GROUP BY e.id

            HAVING (COALESCE(SUM(f.total), 0) + COALESCE(SUM(j.total), 0) + COALESCE(SUM(hl.total), 0)) > 0

            ORDER BY e.name asc");



            $nos = 1;

            $summary_total = 0;

            foreach ($summaries as $summary) {

                $html .= "  <tr>

                                <td>".$nos."</td>

                                <td>".$summary->name."</td>

                                <td style='text-align:right'>".number_format($summary->total_salary)."</td>

                            </tr>";

                $nos++;

                $summary_total += $summary->total_salary;

            }



            $html .= "  <tr>

                            <td colspan='2' style='text-align:right; font-weight:bold;'>GRAND TOTAL</td>

                            <td style='text-align:right; font-weight:bold;'>".number_format($summary_total)."</td>

                        </tr>";



        $html .= '</table><br>

                <center>

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

                            <th style="text-align:center;">HR-GENERALIST</th>

                        </tr>

                    </table>

                </center>';

                

                $html .= '</body></html>';

                echo $html;

        }

    }

}

