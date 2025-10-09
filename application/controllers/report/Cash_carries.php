<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Cash_carries extends CI_Controller

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

            $this->load->view('report/cash_carries');

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

        $filter_group = $this->input->get('filter_group');

        $filter_bank = $this->input->get('filter_bank');

        $username = $this->session->username;



        $period_start = date("Y-m", strtotime($filter_from));

        $period_end = date("Y-m", strtotime($filter_to));



        //Select Query

        $this->db->select('a.*,

            b.number as employee_number,

            b.name as employee_name,

            b.departement_id,

            b.departement_sub_id,

            b.group_id,

            b.position_id,

            c.name as departement_name, 

            d.name as departement_sub_name, 

            e.name as group_name');

        $this->db->from('cash_carries a');

        $this->db->join('employees b', "a.employee_id = b.id");

        $this->db->join('departements c', "b.departement_id = c.id");

        $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

        $this->db->join('groups e', "b.group_id = e.id");

        //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");

        $this->db->where('a.deleted', 0);

        $this->db->where('b.contract_id !=', 20221119000003);
        $this->db->where('b.contract_id !=', 20250704000001);
        $this->db->where('b.group_id !=', 20221119000002);

        $this->db->where('a.status', 0);

        $this->db->group_start();
        $this->db->where('a.approved_to', '');
        $this->db->or_where('a.approved_to IS NULL');
        $this->db->group_end();

        if ($filter_from != "" && $filter_to != "") {

            $this->db->where('a.trans_date >=', $filter_from);

            $this->db->where('a.trans_date <=', $filter_to);

        }

        $this->db->like('b.id', $filter_employee);

        $this->db->like('b.division_id', $filter_division);

        $this->db->like('b.departement_id', $filter_departement);

        $this->db->like('b.departement_sub_id', $filter_departement_sub);

        $this->db->like('b.group_id', $filter_group);

        $this->db->like('b.bank_name', $filter_bank);

        $this->db->group_by("b.id");

        $this->db->order_by('b.name', 'ASC');

        //Get Data Array

        $cash_carries = $this->db->get()->result_array();
        // die($this->db->last_query());


        $datas = [];

        foreach ($cash_carries as $cash_carry) {

            //Select Query

            $this->db->select('a.*,

                b.bank_no,

                b.bank_name,

                b.number as employee_number,

                b.name as employee_name,

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                b.position_id,

                c.name as departement_name, 

                d.name as departement_sub_name,

                h.name as division_name, 

                e.name as group_name,

                COALESCE(i.weekday, 0) as total_weekday,

                COALESCE(i.sunday, 0) as total_sunday,

                COALESCE(i.saturday, 0) as total_saturday,

                COALESCE(i.holiday, 0) as total_holiday,

                COALESCE(i.meal, 0) as total_meal');

            $this->db->from('cash_carries a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('divisions h', "b.division_id = h.id");

            $this->db->join('groups e', "b.group_id = e.id");

            //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");

            // $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in");

            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');

            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');

            $this->db->where('a.deleted', 0);

            $this->db->where('b.contract_id !=', 20221119000003);
            $this->db->where('b.contract_id !=', 20250704000001);
            $this->db->where('b.group_id !=', 20221119000002);

            $this->db->where('a.status', 0);

            $this->db->group_start();
            $this->db->where('a.approved_to', '');
            $this->db->or_where('a.approved_to IS NULL');
            $this->db->group_end();

            if ($filter_from != "" && $filter_to != "") {

                $this->db->where('a.trans_date >=', $filter_from);

                $this->db->where('a.trans_date <=', $filter_to);

            }

            $this->db->where('b.id', $cash_carry['employee_id']);

            $this->db->order_by('b.name', 'ASC');

            $records = $this->db->get()->result_array();



            $total = 0;

            foreach ($records as $record) {

                $this->db->select('c.days');

                $this->db->from('shift_employees a');

                $this->db->join('shifts b', 'a.shift_id = b.id', 'left');

                $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');

                $this->db->where('a.employee_id', $record['employee_id']);

                $shift_employee = $this->db->get()->row();



                $this->db->select('trans_date');

                $this->db->from('calendars');

                $this->db->where('trans_date', $record['trans_date']);

                $calendars = $this->db->get()->result_array();



                $start = strtotime($record['trans_date']);

                $hour = $record['duration_hour'];



                //Validasi Uang makan

                if ($record['meal'] == 0) {

                    $meal = 0;

                } else {

                    $meal = @$record['total_meal'];

                }



                if (@$shift_employee->days == "5") {

                    if (date('w', $start) !== '0' && date('w', $start) !== '6') {



                        //Kalo ada tanggal Merah

                        if (count($calendars) > 0) {

                            $total += ((@$record['total_holiday'] * $hour) + $meal);

                        } else {

                            $total += ((@$record['total_weekday'] * $hour) + $meal);

                        }

                    } else {

                        if (date('w', $start) === '0'){

                            $total += ((@$record['total_sunday'] * $hour) + $meal);

                        }else{

                            $total += ((@$record['total_saturday'] * $hour) + $meal);

                        }

                    }

                } else {

                    if (date('w', $start) !== '0') {



                        //Kalo ada tanggal Merah

                        if (count($calendars) > 0) {

                            $total += ((@$record['total_holiday'] * $hour) + $meal);

                        } else {

                            $total += ((@$record['total_weekday'] * $hour) + $meal);

                        }

                    } else {

                        if (date('w', $start) === '0'){

                            $total += ((@$record['total_sunday'] * $hour) + $meal);

                        }else{

                            $total += ((@$record['total_saturday'] * $hour) + $meal);

                        }

                    }

                }

            }



            $amount = ["amount" => $total];

            $datas[] = array_merge($cash_carry, $amount);

        }



        echo json_encode(@$datas);

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

            $filter_employee = $this->input->get('filter_employee');

            $username = $this->session->username;



            //Select Query

            $this->db->select('a.*,

                b.bank_no,

                b.bank_name,

                b.number as employee_number,

                b.name as employee_name,

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                b.position_id,

                c.name as departement_name, 

                d.name as departement_sub_name,

                h.name as division_name, 

                e.name as group_name,

                COALESCE(i.weekday, 0) as total_weekday,

                COALESCE(i.sunday, 0) as total_sunday,

                COALESCE(i.saturday, 0) as total_saturday,

                COALESCE(i.holiday, 0) as total_holiday,

                COALESCE(i.meal, 0) as total_meal');

            $this->db->from('cash_carries a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('divisions h', "b.division_id = h.id");

            $this->db->join('groups e', "b.group_id = e.id");

            //$this->db->join('privilege_groups f', "b.group_id = f.id and f.username = '$username' and f.status = '1'", "left");

            // $this->db->join('attandances g', "b.number = g.number and a.trans_date = g.date_in");

            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');

            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');

            $this->db->where('a.deleted', 0);

            $this->db->where('b.contract_id !=', 20221119000003);
            $this->db->where('b.contract_id !=', 20250704000001);
            $this->db->where('b.group_id !=', 20221119000002);

            $this->db->where('a.status', 0);

            $this->db->group_start();
            $this->db->where('a.approved_to', '');
            $this->db->or_where('a.approved_to IS NULL');
            $this->db->group_end();

            if ($filter_from != "" && $filter_to != "") {

                $this->db->where('a.trans_date >=', $filter_from);

                $this->db->where('a.trans_date <=', $filter_to);

            }

            $this->db->where('b.id', $filter_employee);

            $this->db->order_by('b.name', 'ASC');

            //Get Data Array

            $cash_carries = $this->db->get()->result_array();



            if($cash_carries){



                //Config

                $this->db->select('*');

                $this->db->from('config');

                $config = $this->db->get()->row();



                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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

                                    Print By ' . $this->session->username . ' <br>

                                </div>

                            </center><br><br><br>

                            <center>

                                <h3 style="margin:0;">REPORT DETAIL CASH CARRY</h3>

                                <span style="font-size:12px;">' . date("d F Y", strtotime($filter_from)) . ' to ' . date("d F Y", strtotime($filter_to)) . '</span>

                            </center>

                            <br>

                            <div style="width:100%;">

                                <table style="font-size:12px; width:50%; float:left; margin-bottom:20px;">

                                    <tr>

                                        <td>Employee ID</td>

                                        <td>:</td>

                                        <td><b style="mso-number-format:\@;">' . $cash_carries[0]['employee_number'] . '</b></td>

                                    </tr>

                                    <tr>

                                        <td>Employee Name</td>

                                        <td>:</td>

                                        <td><b>' . $cash_carries[0]['employee_name'] . '</b></td>

                                    </tr>

                                    <tr>

                                        <td>Plant</td>

                                        <td>:</td>

                                        <td><b>' . $cash_carries[0]['division_name'] . '</b></td>

                                    </tr>

                                </table>

                                <table style="font-size:12px; width:50%; float:left;">

                                    <tr>

                                        <td>Departement</td>

                                        <td>:</td>

                                        <td><b>' . $cash_carries[0]['departement_name'] . '</b></td>

                                    </tr>

                                    <tr>

                                        <td>Departement Sub</td>

                                        <td>:</td>

                                        <td><b>' . $cash_carries[0]['departement_sub_name'] . '</b></td>

                                    </tr>

                                    <tr>

                                        <td>Group</td>

                                        <td>:</td>

                                        <td><b>' . $cash_carries[0]['group_name'] . '</b></td>

                                    </tr>

                                </table>

                            </div>

                            

                            <table id="customers" border="1">

                                <tr>

                                    <th width="20">No</th>

                                    <th style="text-align:center;">Bank No</th>

                                    <th style="text-align:center;">Bank Name</th>

                                    <th style="text-align:center;">Trans Date</th>

                                    <th style="text-align:center;">Type</th>

                                    <th style="text-align:center;">Hour</th>

                                    <th style="text-align:center;">Amount</th>

                                    <th style="text-align:center;">Meal</th>

                                    <th style="text-align:center;">Total</th>

                                </tr>';

                $no = 1;

                $grand_total = 0;

                foreach ($cash_carries as $record) {

                    $this->db->select('c.days');

                    $this->db->from('shift_employees a');

                    $this->db->join('shifts b', 'a.shift_id = b.id', 'left');

                    $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');

                    $this->db->where('a.employee_id', $record['employee_id']);

                    $shift_employee = $this->db->get()->row();



                    $this->db->select('trans_date');

                    $this->db->from('calendars');

                    $this->db->where('trans_date', $record['trans_date']);

                    $calendars = $this->db->get()->result_array();



                    $start = strtotime($record['trans_date']);

                    // $att_time_begin = strtotime(@$record['date_in'] . " " . @$record['time_in']);

                    // $att_time_end = strtotime(@$record['date_out'] . " " . @$record['time_out']);

                    

                    // $tomorrow = strtotime(date('Y-m-d', strtotime(@$record['date_out'] . "+1 days")) . " " . @$record['time_out']);



                    // $att_diff = $att_time_end - $att_time_begin;

                    // $att_hour = floor($att_diff / (60 * 60));



                    // if ($att_hour < 0) {

                    //     $att_diff = $tomorrow - $att_time_begin;

                    //     $att_hour = floor($att_diff / (60 * 60));

                    // }



                    $hour = $record['duration_hour'];



                    //Validasi Jam

                    // if ($att_hour > $cc_hour) {

                    //     $hour = $cc_hour;

                    // } else {

                    //     $hour = $att_hour;

                    // }



                    //Validasi Uang makan

                    if ($record['meal'] == 0) {

                        $meal = 0;

                    } else {

                        $meal = @$record['total_meal'];

                    }



                    if (@$shift_employee->days == "5") {

                        if (date('w', $start) !== '0' && date('w', $start) !== '6') {



                            //Kalo ada tanggal Merah

                            if (count($calendars) > 0) {

                                $total = ((@$record['total_holiday'] * $hour));

                            } else {

                                $total = ((@$record['total_weekday'] * $hour));

                            }

                        } else {

                            if (date('w', $start) === '0'){

                                $total = ((@$record['total_sunday'] * $hour));

                            }else{

                                $total = ((@$record['total_saturday'] * $hour));

                            }

                        }

                    } else {

                        if (date('w', $start) !== '0') {



                            //Kalo ada tanggal Merah

                            if (count($calendars) > 0) {

                                $total = ((@$record['total_holiday'] * $hour));

                            } else {

                                $total = ((@$record['total_weekday'] * $hour));

                            }

                        } else {

                            if (date('w', $start) === '0'){

                                $total = ((@$record['total_sunday'] * $hour));

                            }else{

                                $total = ((@$record['total_saturday'] * $hour));

                            }

                        }

                    }



                    $html .= '  <tr>

                                    <td>' . $no . '</td>

                                    <td style="mso-number-format:\@;">' . $record['bank_no'] . '</td>

                                    <td>' . $record['bank_name'] . '</td>

                                    <td>' . $record['trans_date'] . '</td>

                                    <td>' . $record['type'] . '</td>

                                    <td style="text-align:center;">' . number_format($hour) . '</td>

                                    <td style="text-align:right;">' . number_format($total) . '</td>

                                    <td style="text-align:right;">' . number_format($meal) . '</td>

                                    <td style="text-align:right;">' . number_format($total + $meal) . '</td>

                                </tr>';

                    $grand_total += ($total + $meal);

                    $no++;

                }



                $html .= '  <tr>

                                <th style="text-align:right;" colspan="8">GRAND TOTAL</th>

                                <th style="text-align:right;">' . number_format($grand_total) . '</th>

                            </tr>';

                $html .= '</table>

                            <br>

                            <center>

                                <table id="customers" style="width:60%;">

                                    <tr>

                                        <th width="100" style="text-align:center;">BOD</th>

                                        <th width="100" style="text-align:center;">LEADER FAT COST CONTROL</th>

                                        <th width="100" style="text-align:center;">ASSISTANT MANAGER HRD-GA</th>

                                        <th width="100" style="text-align:center;">LEADER HR</th>

                                        <th width="100" style="text-align:center;">PAYROLL STAFF</th>

                                    </tr>

                                    <tr>

                                        <td style="height:60px;"></td>

                                        <td style="height:60px;"></td>

                                        <td style="height:60px;"></td>

                                        <td style="height:60px;"></td>

                                        <td style="height:60px;"></td>

                                    </tr>

                                    <tr>

                                        <th style="text-align:center; height:20px;"></th>

                                        <th style="text-align:center;"></th>

                                        <th style="text-align:center;"></th>

                                        <th style="text-align:center;"></th>

                                        <th style="text-align:center;"></th>

                                    </tr>

                                </table>

                            </center>

                            </body></html>';

                echo $html;

            }else{

                echo "<center><br><br><br><h2>Data Not Complete to View</h2></center>";

            }

        }

    }

    public function print_recap($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_cash_carries_summary_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_group = $this->input->get('filter_group');
            $filter_bank = $this->input->get('filter_bank');
            $username = $this->session->username;

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('a.*,
                b.number as employee_number,
                b.name as employee_name,
                b.bank_no,
                b.bank_name,
                c.name as division_name, 
                d.name as departement_name,
                e.name as group_name,
                p.name as position_name,
                COALESCE(i.weekday, 0) as total_weekday,
                COALESCE(i.sunday, 0) as total_sunday,
                COALESCE(i.saturday, 0) as total_saturday,
                COALESCE(i.holiday, 0) as total_holiday,
                COALESCE(i.meal, 0) as total_meal
            ');
            $this->db->from('cash_carries a');
            $this->db->join('employees b', "a.employee_id = b.id");
            $this->db->join('divisions c', "b.division_id = c.id");
            $this->db->join('departements d', "b.departement_id = d.id");
            $this->db->join('groups e', "b.group_id = e.id");
            $this->db->join('positions p', "b.position_id = p.id", 'left');
            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');
            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');
            
            $this->db->where('a.deleted', 0);
            $this->db->where('b.contract_id !=', 20221119000003);
            $this->db->where('b.contract_id !=', 20250704000001);
            $this->db->where('b.group_id !=', 20221119000002);
            $this->db->where('a.status', 0);
            $this->db->group_start();
            $this->db->where('a.approved_to', '');
            $this->db->or_where('a.approved_to IS NULL');
            $this->db->group_end();
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if ($filter_employee != "") {
                $this->db->like('b.id', $filter_employee);
            }
            if ($filter_division != "") {
                $this->db->like('b.division_id', $filter_division);
            }
            if ($filter_departement != "") {
                $this->db->like('b.departement_id', $filter_departement);
            }
            if ($filter_departement_sub != "") {
                $this->db->like('b.departement_sub_id', $filter_departement_sub);
            }
            if ($filter_group != "") {
                $this->db->like('b.group_id', $filter_group);
            }
            if ($filter_bank != "") {
                $this->db->like('b.bank_name', $filter_bank);
            }
            $this->db->order_by('b.name', 'ASC');
            //Get Data Array
            $records = $this->db->get()->result_array();

            if (empty($records)) {
                echo "<center><br><br><br><h2>Data Not Found</h2></center>";
                return;
            }

            $employee_data = [];
            foreach ($records as $record) {
                $employee_id = $record['employee_id'];
                if (!isset($employee_data[$employee_id])) {
                    $employee_data[$employee_id] = [
                        'employee_number' => $record['employee_number'],
                        'employee_name' => $record['employee_name'],
                        'division_name' => $record['division_name'],
                        'departement_name' => $record['departement_name'],
                        'position_name' => $record['position_name'],
                        'bank_name' => $record['bank_name'],
                        'bank_no' => $record['bank_no'],
                        'total_amount' => 0,
                        'total_ot' => 0
                    ];
                }

                $this->db->select('c.days');
                $this->db->from('shift_employees a');
                $this->db->join('shifts b', 'a.shift_id = b.id', 'left');
                $this->db->join('shift_details c', 'b.id = c.shift_id', 'left');
                $this->db->where('a.employee_id', $record['employee_id']);
                $shift_employee = $this->db->get()->row();

                $this->db->select('trans_date');
                $this->db->from('calendars');
                $this->db->where('trans_date', $record['trans_date']);
                $calendars = $this->db->get()->result_array();

                $start = strtotime($record['trans_date']);
                $hour = $record['duration_hour'];

                $employee_data[$employee_id]['total_ot'] += $hour;

                if ($record['meal'] == 0) {
                    $meal = 0;
                } else {
                    $meal = @$record['total_meal'];
                }

                $total = 0;
                if (@$shift_employee->days == "5") {
                    if (date('w', $start) !== '0' && date('w', $start) !== '6') { // Weekday
                        if (count($calendars) > 0) { // Holiday
                            $total = ((@$record['total_holiday'] * $hour) + $meal);
                        } else {
                            $total = ((@$record['total_weekday'] * $hour) + $meal);
                        }
                    } else { // Weekend
                        if (date('w', $start) === '0'){ // Sunday
                            $total = ((@$record['total_sunday'] * $hour) + $meal);
                        } else { // Saturday
                            $total = ((@$record['total_saturday'] * $hour) + $meal);
                        }
                    }
                } else { // 6 work days
                    if (date('w', $start) !== '0') { // Not Sunday
                        if (count($calendars) > 0) { // Holiday
                            $total = ((@$record['total_holiday'] * $hour) + $meal);
                        } else {
                            $total = ((@$record['total_weekday'] * $hour) + $meal);
                        }
                    } else { // Sunday
                        $total = ((@$record['total_sunday'] * $hour) + $meal);
                    }
                }
                $employee_data[$employee_id]['total_amount'] += $total;
            }

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><style> .str{ mso-number-format:\@; } </style><body>';
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

                                <h3 style="margin:0;">REPORT SUMMARY CASH CARRY</h3>

                                <span style="font-size:12px;">' . date("d F Y", strtotime($filter_from)) . ' to ' . date("d F Y", strtotime($filter_to)) . '</span>

                            </center>

                            <br>
                    <table id="customers" border="1">
                        <tr>
                            <th width="20" style="text-align:center;">No</th>
                            <th style="text-align:center;">Employee ID</th>
                            <th style="text-align:center;">Fullname</th>
                            <th style="text-align:center;">Division</th>
                            <th style="text-align:center;">Departement</th>
                            <th style="text-align:center;">Position</th>
                            <th style="text-align:center;">Bank Name</th>
                            <th style="text-align:center;">Bank Account</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center;">OT</th>
                        </tr>';

            $grandTotalAmount = 0;
            $grandTotalOT = 0;
            $no = 1;
            foreach ($employee_data as $emp) {
                $html .= '  <tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $emp['employee_number'] . '</td>
                                <td>' . $emp['employee_name'] . '</td>
                                <td>' . $emp['division_name'] . '</td>
                                <td>' . $emp['departement_name'] . '</td>
                                <td>' . $emp['position_name'] . '</td>
                                <td>' . $emp['bank_name'] . '</td>
                                <td style="mso-number-format:\@;">' . $emp['bank_no'] . '</td>
                                <td style="text-align:right;">' . number_format($emp['total_amount'], 2, ',', '.') . '</td>
                                <td style="text-align:right;">' . number_format($emp['total_ot'], 2, ',', '.') . '</td>
                            </tr>';
                $grandTotalAmount += $emp['total_amount'];
                $grandTotalOT += $emp['total_ot'];
                $no++;
            }

            $html .= '  <tr>
                        <th style="text-align:right;" colspan="8">GRAND TOTAL</th>
                        <th style="text-align:right;">' . number_format($grandTotalAmount, 2, ',', '.') . '</th>
                        <th style="text-align:right;">' . number_format($grandTotalOT, 2, ',', '.') . '</th>
                    </tr>
                    </table>
                    <br>
                    <center>
                        <table id="customers" style="width:100%;">
                            <tr>
                                <th width="20%" style="text-align:center;">Disetujui</th>
                                <th width="40%" style="text-align:center;" colspan="2">Diketahui</th>
                                <th width="20%" style="text-align:center;">Diperiksa</th>
                                <th width="20%" style="text-align:center;">Dibuat</th>
                            </tr>
                            <tr>
                                <td style="height:60px;"></td>
                                <td style="height:60px;"></td>
                                <td style="height:60px;"></td>
                                <td style="height:60px;"></td>
                                <td style="height:60px;"></td>
                            </tr>
                            <tr>
                                <td style="text-align:center;font-weight:bold;">Kinenta Harsono</td>
                                <td style="text-align:center;font-weight:bold;">Maya Evilia</td>
                                <td style="text-align:center;font-weight:bold;">Rizki Andri S</td>
                                <td style="text-align:center;font-weight:bold;">Nia Nurazizah</td>
                                <td style="text-align:center;font-weight:bold;">Jihan Maharani</td>
                            </tr>
                             <tr>
                                <td style="text-align:center;">BOD</td>
                                <td style="text-align:center;">Leader FAT Cost Control</td>
                                <td style="text-align:center;">Asst Manager HRD-GA</td>
                                <td style="text-align:center;">Leader HR</td>
                                <td style="text-align:center;">Staff HR-Payroll</td>
                            </tr>
                        </table>
                    </center>';

            $html .= '</body></html>';
            echo $html;
        }
    }

    public function print_detail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_cash_carries_detail_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_group = $this->input->get('filter_group');
            $filter_bank = $this->input->get('filter_bank');
            $username = $this->session->username;

            // Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            // Date range
            $period = new DatePeriod(
                new DateTime($filter_from),
                new DateInterval('P1D'),
                (new DateTime($filter_to))->modify('+1 day')
            );
            $dates = [];
            foreach ($period as $value) {
                $dates[] = $value->format('Y-m-d');
            }

            // Select Query
            $this->db->select('
                a.trans_date, a.duration_hour, a.meal, a.employee_id,
                b.number as employee_number, b.name as employee_name, b.bank_no, b.bank_name,
                c.name as division_name,
                d.name as departement_name,
                ds.name as departement_sub_name,
                g.name as group_name,
                p.name as position_name,
                j.allowance_id,
                i.weekday, i.sunday, i.saturday, i.holiday, i.meal as total_meal,
                b.job_type, s.name as source_name
            ');
            $this->db->from('cash_carries a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('divisions c', 'b.division_id = c.id');
            $this->db->join('departements d', 'b.departement_id = d.id');
            $this->db->join('departement_subs ds', 'b.departement_sub_id = ds.id', 'left');
            $this->db->join('groups g', 'b.group_id = g.id', 'left');
            $this->db->join('positions p', 'b.position_id = p.id', 'left');
            $this->db->join('setup_cash_carries j', 'a.employee_id = j.employee_id', 'left');
            $this->db->join('allowance_cash_carries i', 'j.allowance_id = i.id', 'left');
            $this->db->join('sources s', 'b.source_id = s.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('b.contract_id !=', 20221119000003);
            $this->db->where('b.contract_id !=', 20250704000001);
            $this->db->where('b.group_id !=', 20221119000002);
            $this->db->group_start();
            $this->db->where('a.approved_to', '');
            $this->db->or_where('a.approved_to IS NULL');
            $this->db->group_end();
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if ($filter_employee != "") {
                $this->db->like('b.id', $filter_employee);
            }
            if ($filter_division != "") {
                $this->db->like('b.division_id', $filter_division);
            }
            if ($filter_departement != "") {
                $this->db->like('b.departement_id', $filter_departement);
            }
            if ($filter_departement_sub != "") {
                $this->db->like('b.departement_sub_id', $filter_departement_sub);
            }
            if ($filter_group != "") {
                $this->db->like('b.group_id', $filter_group);
            }
            if ($filter_bank != "") {
                $this->db->like('b.bank_name', $filter_bank);
            }
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            $records = $this->db->get()->result_array();

            if (empty($records)) {
                echo "<center><br><br><br><h2>Data Not Found</h2></center>";
                return;
            }

            $data = [];
            $calendars_cache = [];
            foreach ($records as $record) {
                $employee_id = $record['employee_id'];

                if (!isset($data[$employee_id])) {
                    $data[$employee_id] = [
                        'employee_number' => $record['employee_number'],
                        'employee_name' => $record['employee_name'],
                        'division_name' => $record['division_name'],
                        'departement_name' => $record['departement_name'],
                        'departement_sub_name' => $record['departement_sub_name'],
                        'position_name' => $record['position_name'],
                        'bank_name' => $record['bank_name'],
                        'bank_no' => $record['bank_no'],
                        'job_type' => $record['job_type'],
                        'source_name' => $record['source_name'],
                        'dates' => [],
                        'total_amount' => 0,
                        'total_ot' => 0,
                    ];
                }

                $this->db->select('c.days');
                $this->db->from('shift_employees sa');
                $this->db->join('shifts s', 'sa.shift_id = s.id', 'left');
                $this->db->join('shift_details c', 's.id = c.shift_id', 'left');
                $this->db->where('sa.employee_id', $record['employee_id']);
                $shift_employee = $this->db->get()->row();

                $trans_date = $record['trans_date'];
                if (!isset($calendars_cache[$trans_date])) {
                    $this->db->select('trans_date');
                    $this->db->from('calendars');
                    $this->db->where('trans_date', $trans_date);
                    $calendars_cache[$trans_date] = $this->db->get()->result_array();
                }
                $calendars = $calendars_cache[$trans_date];
                $start = strtotime($trans_date);
                $hour = (float)$record['duration_hour'];
                $meal_allowance = ($record['meal'] == 0) ? 0 : (float)$record['total_meal'];
                $daily_total = 0;
                if (@$shift_employee->days == "5") {
                    if (date('w', $start) !== '0' && date('w', $start) !== '6') {
                        $daily_total = (count($calendars) > 0) ? ((float)$record['holiday'] * $hour) : ((float)$record['weekday'] * $hour);
                    } else {
                        $daily_total = (date('w', $start) === '0') ? ((float)$record['sunday'] * $hour) : ((float)$record['saturday'] * $hour);
                    }
                } else {
                    if (date('w', $start) !== '0') {
                        $daily_total = (count($calendars) > 0) ? ((float)$record['holiday'] * $hour) : ((float)$record['weekday'] * $hour);
                    } else {
                        $daily_total = ((float)$record['sunday'] * $hour);
                    }
                }
                $final_daily_total = $daily_total + $meal_allowance;
                if (!isset($data[$employee_id]['dates'][$trans_date])) {
                    $data[$employee_id]['dates'][$trans_date] = 0;
                }
                $data[$employee_id]['dates'][$trans_date] += $final_daily_total;
                $data[$employee_id]['total_amount'] += $final_daily_total;
                $data[$employee_id]['total_ot'] += $hour;
            }

            $html = '<center>

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

                                <h3 style="margin:0;">REPORT DETAIL CASH CARRY</h3>

                                <span style="font-size:12px;">' . date("d F Y", strtotime($filter_from)) . ' to ' . date("d F Y", strtotime($filter_to)) . '</span>

                            </center>

                            <br>';

            $html .= '<table id="customers" border="1"><thead><tr>
                        <th>No</th>
                        <th>Employee ID</th>
                        <th>Fullname</th>
                        <th>Division</th>
                        <th>Departement</th>
                        <th>Departement Sub</th>
                        <th>Job Type</th>
                        <th>Position</th>
                        <th>Source</th>
                        <th>Bank Name</th>
                        <th>Bank Account</th>';
                        
            foreach ($dates as $date) {
                $html .= '<th class="date">' . date('d-M', strtotime($date)) . '</th>';
            }
            $html .= '<th>Total</th><th>OT</th></tr></thead><tbody>';

            $no = 1;
            $grand_total_amount = 0;
            $grand_total_ot = 0;
            $date_totals = array_fill_keys($dates, 0);

            foreach ($data as $emp) {
                $html .= '<tr>
                            <td style="text-align:center;">' . $no++ . '</td>
                            <td style="mso-number-format:\@;">' . $emp['employee_number'] . '</td>
                            <td>' . $emp['employee_name'] . '</td>
                            <td>' . $emp['division_name'] . '</td>
                            <td>' . $emp['departement_name'] . '</td>
                            <td>' . $emp['departement_sub_name'] . '</td>
                            <td>' . (!empty($emp['job_type']) ? $emp['job_type'] : '-') . '</td>
                            <td>' . $emp['position_name'] . '</td>
                            <td>' . (!empty($emp['source_name']) ? $emp['source_name'] : '-') . '</td>
                            <td>' . $emp['bank_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $emp['bank_no'] . '</td>
                            ';
                foreach ($dates as $date) {
                    $amount = isset($emp['dates'][$date]) ? $emp['dates'][$date] : 0;
                    if ($amount > 0) {
                        $html .= '<td class="num" style="text-align:right;">' . $amount . '</td>';
                    } else {
                        $html .= '<td style="text-align:right;">-</td>';
                    }
                    if (isset($emp['dates'][$date])) {
                        $date_totals[$date] += $amount;
                    }
                }
                $html .= '<td class="num" style="text-align:right; font-weight:bold;">' . $emp['total_amount'] . '</td>
                          <td class="num2" style="text-align:right; font-weight:bold;">' . number_format($emp['total_ot'], 2, ',', '.') . '</td>
                          </tr>';
                $grand_total_amount += $emp['total_amount'];
                $grand_total_ot += $emp['total_ot'];
            }
            $html .= '</tbody><tfoot><tr><th colspan="11" style="text-align:right;">Total</th>';
            foreach ($dates as $date) {
                if ($date_totals[$date] > 0) {
                    $html .= '<th class="num" style="text-align:right;">' . $date_totals[$date] . '</th>';
                } else {
                    $html .= '<th style="text-align:right;">-</th>';
                }
            }
            $html .= '<th class="num" style="text-align:right;">' . $grand_total_amount . '</th>
                      <th class="num2" style="text-align:right;">' . number_format($grand_total_ot, 2, ',', '.') . '</th>
                      </tr></tfoot></table></body></html>';

            echo $html;
        }
    }
}