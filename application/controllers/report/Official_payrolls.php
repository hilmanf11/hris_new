<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Official_payrolls extends CI_Controller

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

            $this->load->view('report/official_payrolls');

        } else {

            redirect('error_access');

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=official_payrolls_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            $query = $this->db->query("SELECT a.id, a.name

                FROM departements a

                LEFT JOIN employees b ON a.id = b.departement_id

                LEFT JOIN payrolls c ON b.number = c.number

                JOIN privilege_groups d ON b.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE c.period_start = '$period_start' and c.period_end = '$period_end'

                AND a.division_id LIKE '%$filter_division%'

                AND a.id LIKE '%$filter_departement%'

                GROUP BY a.id

                ORDER BY a.`name` ASC");

            $records = $query->result_array();



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

                        <h3 style="margin:0;">Official Payroll Report</h3>

                        <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>

                    </center>

                    <br>';



            $html .= '  <table id="customers" border="1">

                            <tr>

                                <th width="20">No</th>

                                <th style="text-align:center;" width="200">Departement</th>

                                <th style="text-align:center;" width="100">Group</th>

                                <th style="text-align:center;" width="80">MP</th>

                                <th style="text-align:center;" width="100">Total Payroll</th>

                                <th style="text-align:center;" width="80">MP</th>

                                <th style="text-align:center;" width="100">Cash</th>

                                <th style="text-align:center;" width="80">MP</th>

                                <th style="text-align:center;" width="100">Bank Syariah Indonesia</th>

                                <th style="text-align:center;" width="80">MP</th>

                                <th style="text-align:center;" width="100">Bank Mandiri</th>

                                <th style="text-align:center;" width="80">Bal MP</th>

                                <th style="text-align:center;" width="100">Bal Payroll</th>

                            </tr>';

            $no = 1;

            $mp_payroll = 0;

            $mp_cash = 0;

            $mp_bsi = 0;

            $mp_mandiri = 0;

            $total_payroll = 0;

            $total_cash = 0;

            $total_bsi = 0;

            $total_mandiri = 0;

            $total_bal_mp = 0;

            $total_bal_payroll = 0;

            foreach ($records as $record) {

                $departement_id = $record['id'];



                //EMPLOYEE---------------------------------------------------------------------------------------------------------------

                $qEmployee = $this->db->query("SELECT a.id, a.name,
                        SUM(b.net_income) as total_payroll, 
                        COUNT(b.number) as total_employee
                    FROM employees a 
                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number
                    JOIN groups c ON a.group_id = c.id
                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'
                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'
                    AND a.departement_id = '$departement_id'
                    AND c.name NOT IN ('MAGANG','PKL')
                    GROUP BY a.departement_id");
                $rEmployee = $qEmployee->row();



                $qEmployeeCash = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name NOT IN ('MAGANG','PKL')

                    AND a.bank_name = '-'

                    GROUP BY a.departement_id");

                $rEmployeeCash = $qEmployeeCash->row();



                $qEmployeeBsm = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name NOT IN ('MAGang','PKL')

                    AND a.bank_name = 'Bank Syariah Indonesia'

                    GROUP BY a.departement_id");

                $rEmployeeBsm = $qEmployeeBsm->row();



                $qEmployeeMandiri = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name NOT IN ('MAGANG','PKL')

                    AND a.bank_name = 'Bank Mandiri'

                    GROUP BY a.departement_id");

                $rEmployeeMandiri = $qEmployeeMandiri->row();



                //MAGANG ---------------------------------------------------------------------------------------------------------------

                $qMagang = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name = 'MAGANG'

                    GROUP BY a.departement_id");

                $rMagang = $qMagang->row();



                $qMagangCash = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name = 'MAGANG'

                    AND a.bank_name = '-'

                    GROUP BY a.departement_id");

                $rMagangCash = $qMagangCash->row();



                $qMagangBsm = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name = 'MAGANG'

                    AND a.bank_name = 'Bank Syariah Indonesia'

                    GROUP BY a.departement_id");

                $rMagangBsm = $qMagangBsm->row();



                $qMagangMandiri = $this->db->query("SELECT a.id, a.name,

                        SUM(b.net_income) as total_payroll, 

                        COUNT(b.number) as total_employee

                    FROM employees a 

                    JOIN (
                        SELECT number, net_income, period_start, period_end FROM payrolls 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                        UNION ALL
                        SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                        WHERE period_start = '$period_start' AND period_end = '$period_end'
                    ) b ON a.number = b.number

                    JOIN groups c ON a.group_id = c.id

                    JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                    WHERE b.period_start = '$period_start' and b.period_end = '$period_end'

                    AND a.departement_id = '$departement_id'

                    AND c.name = 'MAGANG'

                    AND a.bank_name = 'Bank Mandiri'

                    GROUP BY a.departement_id");

                $rMagangMandiri = $qMagangMandiri->row();





                $html .= '  <tr>

                                <td rowspan="3">' . $no . '</td>

                                <td rowspan="3">' . $record['name'] . '</td>

                            </tr>';



                $html .= '<tr>

                            <td>EMPLOYEE</td>

                            <td style="text-align:center;">' . number_format(@$rEmployee->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rEmployee->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rEmployeeCash->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rEmployeeCash->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rEmployeeBsm->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rEmployeeBsm->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rEmployeeMandiri->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rEmployeeMandiri->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format((@$rEmployee->total_employee - (@$rEmployeeCash->total_employee + @$rEmployeeBsm->total_employee + @$rEmployeeMandiri->total_employee))) . '</td>

                            <td style="text-align:right;">' . number_format((@$rEmployee->total_payroll - (@$rEmployeeCash->total_payroll + @$rEmployeeBsm->total_payroll + @$rEmployeeMandiri->total_payroll))) . '</td>

                        </tr>';



                $html .= '<tr>

                            <td>MAGANG</td>

                            <td style="text-align:center;">' . number_format(@$rMagang->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rMagang->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rMagangCash->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rMagangCash->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rMagangBsm->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rMagangBsm->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format(@$rMagangMandiri->total_employee) . '</td>

                            <td style="text-align:right;">' . number_format(@$rMagangMandiri->total_payroll) . '</td>

                            <td style="text-align:center;">' . number_format((@$rMagang->total_employee - (@$rMagangCash->total_employee + @$rMagangBsm->total_employee + @$rMagangMandiri->total_employee))) . '</td>

                            <td style="text-align:right;">' . number_format((@$rMagang->total_payroll - (@$rMagangCash->total_payroll + @$rMagangBsm->total_payroll + @$rMagangMandiri->total_payroll))) . '</td>

                        </tr>';



                $mp_payroll += (@$rEmployee->total_employee + @$rMagang->total_employee);

                $mp_cash += (@$rEmployeeCash->total_employee + @$rMagangCash->total_employee);

                $mp_bsi += (@$rEmployeeBsm->total_employee + @$rMagangBsm->total_employee);

                $mp_mandiri += (@$rEmployeeMandiri->total_employee + @$rMagangMandiri->total_employee);

                $total_payroll += (@$rEmployee->total_payroll + @$rMagang->total_payroll);

                $total_cash += (@$rEmployeeCash->total_payroll + @$rMagangCash->total_payroll);

                $total_bsi += (@$rEmployeeBsm->total_payroll + @$rMagangBsm->total_payroll);

                $total_mandiri += (@$rEmployeeMandiri->total_payroll + @$rMagangMandiri->total_payroll);

                $total_bal_mp += ((@$rEmployee->total_employee + @$rMagang->total_employee) - ((@$rEmployeeCash->total_employee + @$rMagangCash->total_employee) + (@$rEmployeeBsm->total_employee + @$rMagangBsm->total_employee) + (@$rEmployeeMandiri->total_employee + @$rMagangMandiri->total_employee)));

                $total_bal_payroll += ((@$rEmployee->total_payroll + @$rMagang->total_payroll) - ((@$rEmployeeCash->total_payroll + @$rMagangCash->total_payroll) + (@$rEmployeeBsm->total_payroll + @$rMagangBsm->total_payroll) + (@$rEmployeeMandiri->total_payroll + @$rMagangMandiri->total_payroll)));

                $no++;

            }



            $period_start_bf = date('Y-m', strtotime('-1 month', strtotime($filter_from)));

            $period_end_bf = date('Y-m', strtotime('-1 month', strtotime($filter_to)));

            $period_start_bf2 = date('Y-m-d', strtotime('-1 month', strtotime($filter_from)));

            $period_end_bf2 = date('Y-m-d', strtotime('-1 month', strtotime($filter_to)));



            //EMPLOYEE---------------------------------------------------------------------------------------------------------------

            $qEmployeeBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name NOT IN ('MAGANG','PKL')

                GROUP BY a.departement_id");

            $rEmployeeBf = $qEmployeeBf->row();



            $qEmployeeCashBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name NOT IN ('MAGANG','PKL')

                AND a.bank_name = '-'

                GROUP BY a.departement_id");

            $rEmployeeCashBf = $qEmployeeCashBf->row();



            $qEmployeeBsmBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name NOT IN ('MAGANG','PKL')

                AND a.bank_name = 'Bank Syariah Indonesia'

                GROUP BY a.departement_id");

            $rEmployeeBsmBf = $qEmployeeBsmBf->row();



            $qEmployeeMandiriBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name NOT IN ('MAGANG','PKL')

                AND a.bank_name = 'Bank Mandiri'

                GROUP BY a.departement_id");

            $rEmployeeMandiriBf = $qEmployeeMandiriBf->row();



            //MAGANG ---------------------------------------------------------------------------------------------------------------

            $qMagangBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name = 'MAGANG'

                GROUP BY a.departement_id");

            $rMagangBf = $qMagangBf->row();



            $qMagangCashBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name = 'MAGANG'

                AND a.bank_name = '-'

                GROUP BY a.departement_id");

            $rMagangCashBf = $qMagangCashBf->row();



            $qMagangBsmBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name = 'MAGANG'

                AND a.bank_name = 'Bank Syariah Indonesia'

                GROUP BY a.departement_id");

            $rMagangBsmBf = $qMagangBsmBf->row();



            $qMagangMandiriBf = $this->db->query("SELECT a.id, a.name,

                    SUM(b.net_income) as total_payroll, 

                    COUNT(b.number) as total_employee

                FROM employees a 

                JOIN (
                    SELECT number, net_income, period_start, period_end FROM payrolls 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                    UNION ALL
                    SELECT number, net_income, period_start, period_end FROM payroll_harian_lepas 
                    WHERE period_start = '$period_start_bf' AND period_end = '$period_end_bf'
                ) b ON a.number = b.number

                JOIN groups c ON a.group_id = c.id

                JOIN privilege_groups d ON a.group_id = d.group_id and d.username = '$username' and d.status = '1'

                WHERE b.period_start = '$period_start_bf' and b.period_end = '$period_end_bf'

                AND a.departement_id = '$departement_id'

                AND c.name = 'MAGANG'

                AND a.bank_name = 'Bank Mandiri'

                GROUP BY a.departement_id");

            $rMagangMandiriBf = $qMagangMandiriBf->row();



            $html .= '<tr style="background: #BEFFAF;">

                        <td colspan="3" style="text-align:right;"><b>TOTAL PAID (NOW)</b></td>

                        <td style="text-align:center;">' . number_format($mp_payroll) . '</td>

                        <td style="text-align:right;">' . number_format($total_payroll) . '</td>

                        <td style="text-align:center;">' . number_format($mp_cash) . '</td>

                        <td style="text-align:right;">' . number_format($total_cash) . '</td>

                        <td style="text-align:center;">' . number_format($mp_bsi) . '</td>

                        <td style="text-align:right;">' . number_format($total_bsi) . '</td>

                        <td style="text-align:center;">' . number_format($mp_mandiri) . '</td>

                        <td style="text-align:right;">' . number_format($total_mandiri) . '</td>

                        <td style="text-align:center;">' . number_format($total_bal_mp) . '</td>

                        <td style="text-align:right;">' . number_format($total_bal_payroll) . '</td>

                    </tr>

                    <tr style="background: #FFAFAF;">

                        <td colspan="3" style="text-align:right;"><b>TOTAL PAID (BEFORE)</b></td>

                        <td style="text-align:center;">' . number_format(@$rEmployeeBf->total_employee + @$rMagangBf->total_employee) . '</td>

                        <td style="text-align:right;">' . number_format(@$rEmployeeBf->total_payroll + @$rMagangBf->total_payroll) . '</td>

                        <td style="text-align:center;">' . number_format(@$rEmployeeCashBf->total_employee + @$rMagangCashBf->total_employee) . '</td>

                        <td style="text-align:right;">' . number_format(@$rEmployeeCashBf->total_payroll + @$rMagangCashBf->total_payroll) . '</td>

                        <td style="text-align:center;">' . number_format(@$rEmployeeBsmBf->total_employee + @$rMagangBsmBf->total_employee) . '</td>

                        <td style="text-align:right;">' . number_format(@$rEmployeeBsmBf->total_payroll + @$rMagangBsmBf->total_payroll) . '</td>

                        <td style="text-align:center;">' . number_format(@$rEmployeeMandiriBf->total_employee + @$rMagangMandiriBf->total_employee) . '</td>

                        <td style="text-align:right;">' . number_format(@$rEmployeeMandiriBf->total_payroll + @$rMagangMandiriBf->total_payroll) . '</td>

                        <td style="text-align:center;">' . number_format(((@$rEmployeeBf->total_employee + @$rMagangBf->total_employee) - ((@$rEmployeeCashBf->total_employee + @$rMagangCashBf->total_employee) + (@$rEmployeeBsmBf->total_employee + @$rMagangBsmBf->total_employee) + (@$rEmployeeMandiriBf->total_employee + @$rMagangMandiriBf->total_employee)))) . '</td>

                        <td style="text-align:right;">' . number_format(((@$rEmployeeBf->total_payroll + @$rMagangBf->total_payroll) - ((@$rEmployeeCashBf->total_payroll + @$rMagangCashBf->total_payroll) + (@$rEmployeeBsmBf->total_payroll + @$rMagangBsmBf->total_payroll) + (@$rEmployeeMandiriBf->total_payroll + @$rMagangMandiriBf->total_payroll)))) . '</td>

                    </tr>

                    <tr style="background: #FFF1AF;">

                        <td colspan="3" style="text-align:right;"><b>UP/DOWN</b></td>

                        <td style="text-align:center;">' . number_format(($mp_payroll - (@$rEmployeeBf->total_employee + @$rMagangBf->total_employee))) . '</td>

                        <td style="text-align:right;">' . number_format(($total_payroll - (@$rEmployeeBf->total_payroll + @$rMagangBf->total_payroll))) . '</td>

                        <td style="text-align:center;">' . number_format(($mp_cash - (@$rEmployeeCashBf->total_employee + @$rMagangCashBf->total_employee))) . '</td>

                        <td style="text-align:right;">' . number_format(($total_cash - (@$rEmployeeCashBf->total_payroll + @$rMagangCashBf->total_payroll))) . '</td>

                        <td style="text-align:center;">' . number_format(($mp_bsi - (@$rEmployeeBsmBf->total_employee + @$rMagangBsmBf->total_employee))) . '</td>

                        <td style="text-align:right;">' . number_format(($total_bsi - (@$rEmployeeBsmBf->total_payroll + @$rMagangBsmBf->total_payroll))) . '</td>

                        <td style="text-align:center;">' . number_format(($mp_mandiri - (@$rEmployeeMandiriBf->total_employee + @$rMagangMandiriBf->total_employee))) . '</td>

                        <td style="text-align:right;">' . number_format(($total_mandiri - (@$rEmployeeMandiriBf->total_payroll + @$rMagangMandiriBf->total_payroll))) . '</td>

                        <td style="text-align:center;">' . number_format(($total_bal_mp - ((@$rEmployeeBf->total_employee + @$rMagangBf->total_employee) - ((@$rEmployeeCashBf->total_employee + @$rMagangCashBf->total_employee) + (@$rEmployeeBsmBf->total_employee + @$rMagangBsmBf->total_employee) + (@$rEmployeeMandiriBf->total_employee + @$rMagangMandiriBf->total_employee))))) . '</td>

                        <td style="text-align:right;">' . number_format(($total_bal_payroll - ((@$rEmployeeBf->total_payroll + @$rMagangBf->total_payroll) - ((@$rEmployeeCashBf->total_payroll + @$rMagangCashBf->total_payroll) + (@$rEmployeeBsmBf->total_payroll + @$rMagangBsmBf->total_payroll) + (@$rEmployeeMandiriBf->total_payroll + @$rMagangMandiriBf->total_payroll))))) . '</td>

                    </tr>';

            $html .= '</table>

            <br>

            <center>

                <table id="customers" style="width:70%;">

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

            </center>';

        }



        $html .= '</body></html>';

        echo $html;

    }

}

