<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Absences extends CI_Controller

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

            $this->load->view('report/absences');

        } else {

            redirect('error_access');

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=report_attandance_summary_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');



            echo '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>

            <style> .str{ mso-number-format:\@; } </style>

            <body>';

            $this->db->select("a.id as division_id, a.name as division_name, b.id as departement_id, b.name as departement_name");

            $this->db->from('divisions a');

            $this->db->join('departements b', 'a.id = b.division_id');

            $this->db->where('a.id', $filter_division);

            $this->db->where('b.id', $filter_departement);

            $this->db->group_by('b.id');

            $this->db->order_by('b.name', 'asc');

            $records = $this->db->get()->result_array();

            // die($this->db->last_query());

            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $start = strtotime($filter_from);

            $finish = strtotime($filter_to);



            $no = 1;

            foreach ($records as $record) {

                $html = '<div style="page-break-after:always;">

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

                    <h3 style="margin:0;">Report Summary Per Departement</h3>

                    <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>

                    <br><br>

                </center>

                <table style="font-size: 11px;">

                    <tr>

                        <td width="100">Division</td>

                        <td width="10">:</td>

                        <td><b>' . $record['division_name'] . '</b></td>

                    </tr>

                    <tr>

                        <td>Departement</td>

                        <td>:</td>

                        <td><b>' . $record['departement_name'] . '</b></td>

                    </tr>

                </table>

                <br>



            <table id="customers" border="1">

                <tr>

                    <th width="20" rowspan="2">No</th>

                    <th style="text-align:center;" rowspan="2">Departement Sub</th>

                    <th style="text-align:center;" rowspan="2">Man Power</th>

                    <th style="text-align:center;" rowspan="2">Absence</th>

                    <th style="text-align:center;" rowspan="2">Permit</th>

                    <th style="text-align:center;" rowspan="2">Sick</th>

                    <th style="text-align:center;" rowspan="2">Leave</th>

                    <th style="text-align:center;" rowspan="2">Late</th>

                    <th style="text-align:center;" rowspan="2">Total</th>

                    <th style="text-align:center;" colspan="5">Warning Letter</th>

                </tr>

                <tr>

                    <th style="text-align:center;">1</th>

                    <th style="text-align:center;">2</th>

                    <th style="text-align:center;">3</th>

                    <th style="text-align:center;">Coaching</th>

                    <th style="text-align:center;">Counseling</th>

                </tr>';



                //Ambil Employee berdasarkan departement dan divisionnnya

                $this->db->select("id, name");

                $this->db->from('departement_subs');

                $this->db->where('departement_id', $record['departement_id']);

                $this->db->like('id', $filter_departement_sub);

                $this->db->order_by('name', 'asc');

                $departement_subs = $this->db->get()->result_array();



                foreach ($departement_subs as $departement_sub) {



                    $this->db->select('a.id, a.number, a.date_sign, e.days');

                    $this->db->from('employees a');

                    $this->db->join('shift_employees c', 'a.id = c.employee_id');

                    $this->db->join('shifts d', 'c.shift_id = d.id');

                    $this->db->join('shift_details e', 'e.shift_id = d.id');

                    $this->db->where('a.departement_sub_id', $departement_sub['id']);

                    $this->db->like('a.id', $filter_employee);

                    $this->db->where('a.status', 0);

                    $this->db->group_by('a.id');

                    $employees = $this->db->get()->result_array();

                    // die($this->db->last_query());

                    $mp = 0;

                    $working = 0;

                    $check_in = 0;

                    $permit = 0;

                    $sick = 0;

                    $leave = 0;

                    $holiday = 0;

                    $late = 0;

                    $wl1 = 0;

                    $wl2 = 0;

                    $wl3 = 0;

                    $coaching = 0;

                    $counseling = 0;

                    $absence = 0;

                    // $workings = [];

                    foreach ($employees as $employee) {

                        $q = "SELECT MIN(date_in) AS date FROM attandances WHERE number = '".$employee['number']."'";
                        $start_date = new DateTime($this->db->query($q)->row()->date);
                        $resignation = $this->crud->read("resignations", [], ["employee_id" => $employee['id']]);
                        $weekend_day = [];
                        $weekday_day = [];

                        // $weekday = [];

                        // $weekday2 = [];

                        // $weekend = [];

                        // $weekend2 = [];



                        for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {

                            $working_date = date('Y-m-d', $i);
                            $week = date('w', $i);

                            if ($employee['days'] == "4") {
                                if (in_array((($start_date->diff(new DateTime($working_date))->days + 1) % 6), [5,0]))
                                    { $weekend_day[] = $working_date; }
                                elseif (@$resignation->resign_date < $working_date) { $weekday_day[] = $working_date; }
                            } else {
                                $weekend = ["0"]; //minggu doang libur
                                if ($employee['days'] == "5") { $weekend[] = "6"; } //tambah sabtu

                                if (in_array($week, $weekend)) { $weekend_day[] = $working_date; }
                                elseif (@$resignation->resign_date < $working_date) { $weekday_day[] = $working_date; }
                            }

                            $this->db->select('*');

                            $this->db->from('resignations');

                            $this->db->where('employee_id', $employee['id']);

                            $this->db->where('resign_date <', $working_date);

                            $resignation = $this->db->get()->row();

    

                            //Jika tanggal yg di looping bukan hari sabtu dan minggu

                            // if (date('w', $i) !== '0' && date('w', $i) !== '6') {

                                //Hari kerja

                                // if ($employee['date_sign'] <= $working_date) {

                                    // if (empty($resignation)) {

                                        // $weekday[] = date('Y-m-d', $i);

                                    // }

                                // }

                            // } else {

                                //Hari libur

                                // $weekend[] = date('Y-m-d', $i);

                            // }

    

                            // if (date('w', $i) !== '0') {

                                //Hari kerja

                                // if ($employee['date_sign'] <= $working_date) {

                                    // if (empty($resignation)) {

                                        // $weekday2[] = date('Y-m-d', $i);

                                    // }

                                // }

                            // } else {

                                //Hari libur

                                // $weekend2[] = date('Y-m-d', $i);

                            // }

                        }

    

                        // if ($employee['days'] == "6") {

                        //     $weekend_day = $weekend2;

                        //     $weekday_day = $weekday2;

                        // } else {

                        //     $weekend_day = $weekend;

                        //     $weekday_day = $weekday;

                        // }



                        //Working Calendar

                        $this->db->select('trans_date');

                        $this->db->from('calendars');

                        $this->db->where('trans_date >=', $filter_from);

                        $this->db->where('trans_date <=', $filter_to);

                        $this->db->where('description !=', "Weekend");

                        if (count($weekend_day) > 0) {

                            $this->db->where_not_in('trans_date', $weekend_day);

                        }

                        $calendar = $this->db->get()->result_array();

                        $calendar_amount = empty($calendar) ? 0 : count($calendar);

                        $w_calendars = array();

                        foreach ($calendar as $cal) {

                            $w_calendars[] = $cal['trans_date'];

                        }



                        //Attandances

                        $tomorow = date('Y-m-d', strtotime("+1 day", strtotime($filter_from)));

                        $this->db->select("number, date_in");

                        $this->db->from('attandances');

                        $this->db->where('number', $employee['number']);

                        $this->db->where("((date_in >= '$filter_from' and date_in <= '$filter_to') or (date_out >= '$tomorow' and date_out <= '$filter_to'))");

                        if (count($weekend_day) > 0) {

                            $this->db->where_not_in('date_in', $weekend_day);

                        }

                        if (count($w_calendars) > 0) {

                            $this->db->where_not_in('date_in', $w_calendars);

                        }

                        $this->db->group_by('date_in');

                        $attandances = $this->db->get()->result_array();



                        $attandance_amount = 0;

                        foreach ($attandances as $attandance) {

                            $attandance_amount++;

                        }



                        //ChangeDays

                        $this->db->select("COUNT(*) as days");

                        $this->db->from('change_days');

                        $this->db->where('employee_id', $employee['id']);

                        $this->db->where("(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')");

                        if (count($weekday_day) > 0) {

                            $this->db->where_in('end', $weekday_day);

                        }

                        $changeDays = $this->db->get()->row();

                        $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;



                        //Permmit

                        $permit_date = "'" . implode("', '", $weekend_day) . "'";

                        $q_permit = $this->db->query("SELECT b.number, b.name, b.absence, COUNT(a.permit_date) as amount

                                FROM permit_types b

                                LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$employee[id]' and a.permit_date >= '$filter_from' and 

                                a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date)

                                WHERE COALESCE(a.approved_to, '') IN ('','fitri')

                                GROUP BY b.id");

                        $r_permit = $q_permit->result_array();



                        $arr_total_sick = 0;

                        $arr_total_leave = 0;

                        $arr_total_permit = 0;

                        $arr_permit_yes = 0;

                        foreach ($r_permit as $permit_data) {

                            if($permit_data['absence'] == "NO"){

                                if($permit_data['name'] == "SAKIT"){

                                    $arr_total_sick += $permit_data['amount'];

                                }elseif($permit_data['name'] == "CUTI"){

                                    $arr_total_leave += $permit_data['amount'];

                                }else{

                                    $arr_total_permit += $permit_data['amount'];

                                }

                            }else{

                                $arr_permit_yes += $permit_data['amount'];

                            }

                            

                        }



                        $mp += 1;

                        $permit += $arr_total_permit;

                        $sick += $arr_total_sick;

                        $leave += $arr_total_leave;

                        $check_in += ($attandance_amount + $arr_permit_yes);

                        $working += (count($weekday_day) - @$calendar_amount - $changeDays_amount);

                        // array_push($workings, count($weekday_day).' - '.@$calendar_amount.' + '.$changeDays_amount);

                        // Hitung keterlambatan (Late)

                        for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {

                            $working_date = date('Y-m-d', $i);

                            // Cek hari kerja

                            if (date('w', $i) !== '0' && date('w', $i) !== '6') {

                                // Ambil shift start

                                $this->db->select('d.start');

                                $this->db->from('shift_employees b');

                                $this->db->join('shifts c', 'c.id = b.shift_id');

                                $this->db->join('shift_details d', 'd.shift_id = c.id');

                                $this->db->where('b.employee_id', $employee['id']);

                                $shift = $this->db->get()->result_object();

                                if ($shift) {

                                    $this->db->select('time_in');

                                    $this->db->from('attandances');

                                    $this->db->where('number', $employee['number']);

                                    $this->db->where('date_in', $working_date);

                                    $att = $this->db->get()->row();

                                    // if ($att && $att->time_in && $shift->start && strtotime($att->time_in) > strtotime($shift->start)) {

                                    //     $late++;

                                    // }

                                    if ($att) {
                                        $time_in = strtotime($att->time_in);
                                        $min = strtotime('-2 Hour', $time_in);
                                        $max = strtotime('+2 Hour', $time_in);

                                        foreach ($shift as $key => $value) {
                                            $shift_start = strtotime($value->start);

                                            // Jika terlambat (time_in > shift_start)
                                            if ($min <= $shift_start && $shift_start <= $max && $time_in > $shift_start)
                                                { $late++; }
                                        }
                                    }
                                }

                            }

                        }

                    }
                    // die(var_dump($workings));

                    // Hitung absence setelah seluruh perhitungan selesai
                    $absence = $working - $check_in - $permit - $sick - $leave;
                    // die($working.' - '.$check_in.' - '.$permit.' - '.$sick.' - '.$leave);
                    if ($absence < 0) $absence = 0;

                    // Hitung Warning Letter 1/2/3
                    $this->db->select('COUNT(*) as total');

                    $this->db->from('warning_letters wl');

                    $this->db->join('employees e', 'wl.employee_id = e.id');

                    $this->db->where('e.departement_sub_id', $departement_sub['id']);

                    $this->db->where('wl.warning_letter', 1);

                    $this->db->where('wl.issue_date >=', $filter_from);

                    $this->db->where('wl.issue_date <=', $filter_to);

                    $wl1 = $this->db->get()->row('total');

                    $this->db->select('COUNT(*) as total');

                    $this->db->from('warning_letters wl');

                    $this->db->join('employees e', 'wl.employee_id = e.id');

                    $this->db->where('e.departement_sub_id', $departement_sub['id']);

                    $this->db->where('wl.warning_letter', 2);

                    $this->db->where('wl.issue_date >=', $filter_from);

                    $this->db->where('wl.issue_date <=', $filter_to);

                    $wl2 = $this->db->get()->row('total');

                    $this->db->select('COUNT(*) as total');

                    $this->db->from('warning_letters wl');

                    $this->db->join('employees e', 'wl.employee_id = e.id');

                    $this->db->where('e.departement_sub_id', $departement_sub['id']);

                    $this->db->where('wl.warning_letter', 3);

                    $this->db->where('wl.issue_date >=', $filter_from);

                    $this->db->where('wl.issue_date <=', $filter_to);

                    $wl3 = $this->db->get()->row('total');

                    // Hitung Coaching

                    $this->db->select('COUNT(*) as total');

                    $this->db->from('coachings c');

                    $this->db->join('employees e', 'c.employee_id = e.id');

                    $this->db->where('e.departement_sub_id', $departement_sub['id']);

                    $this->db->where('c.type', 'COACHING');

                    $this->db->where('c.trans_date >=', $filter_from);

                    $this->db->where('c.trans_date <=', $filter_to);

                    $coaching = $this->db->get()->row('total');

                    // Hitung Counseling

                    $this->db->select('COUNT(*) as total');

                    $this->db->from('coachings c');

                    $this->db->join('employees e', 'c.employee_id = e.id');

                    $this->db->where('e.departement_sub_id', $departement_sub['id']);

                    $this->db->where('c.type', 'COUNSELING');

                    $this->db->where('c.trans_date >=', $filter_from);

                    $this->db->where('c.trans_date <=', $filter_to);

                    $counseling = $this->db->get()->row('total');

                    $html .= '<tr>

                                <td style="text-align:center;">' . $no . '</td>

                                <td style="text-align:center;">' . $departement_sub['name'] . '</td>

                                <td style="text-align:center;">' . $mp . '</td>

                                <td style="text-align:center;">' . $absence . '</td>

                                <td style="text-align:center;">' . $permit . '</td>

                                <td style="text-align:center;">' . $sick . '</td>

                                <td style="text-align:center;">' . $leave . '</td>

                                <td style="text-align:center;">' . $late . '</td>

                                <td style="text-align:center;">' . ($absence + $permit + $sick + $leave) . '</td>

                                <td style="text-align:center;">' . $wl1 . '</td>

                                <td style="text-align:center;">' . $wl2 . '</td>

                                <td style="text-align:center;">' . $wl3 . '</td>

                                <td style="text-align:center;">' . $coaching . '</td>

                                <td style="text-align:center;">' . $counseling . '</td>

                            </tr>';

                    $no++;

                }



                $html .= '</table></div><br><br>';

                echo $html;

            }



            echo '</body></html>';

        }

    }

    // public function print()
    // {
    //     $date_from = $this->input->get('filter_from');
    //     $date_to = $this->input->get('filter_to');
    //     $division = $this->input->get('filter_division');
    //     $departement = $this->input->get('filter_departement');
    //     $departement_sub = $this->input->get('filter_departement_sub');
    //     $employee = $this->input->get('filter_employee');
    // }
}

