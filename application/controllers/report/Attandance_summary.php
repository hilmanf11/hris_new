<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Attandance_summary extends CI_Controller
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
            $this->load->view('report/attandance_summary');
        } else {
            redirect('error_access');
        }
    }

    public function readGroupFilters()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        echo json_encode($this->crud->reads('groups', ["name" => $post]));
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
            $filter_group = $this->input->get('filter_group');
            $filter_employee = $this->input->get('filter_employee');

            $division = $this->crud->read("divisions", [], ["id" => $filter_division]);
            $departement = $this->crud->read("departements", [], ["id" => $filter_departement]);
            $departement_sub = $this->crud->read("departement_subs", [], ["id" => $filter_departement_sub]);
            $group = $this->crud->read("groups", [], ["id" => $filter_group]);

            $division_name = empty($division->name) ? "ALL" : $division->name;
            $departement_name = empty($departement->name) ? "ALL" : $departement->name;
            $departement_sub_name = empty($departement_sub->name) ? "ALL" : $departement_sub->name;
            $group_name = empty($group->name) ? "ALL" : $group->name;

            $header_html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>';

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            // Ambil permit_types di luar loop agar konsisten
            $permit_types = $this->crud->reads("permit_types", [], [], "", "name", "asc");

            $no = 1;
            $html = '
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
                </center><br><br>
                <center>
                    <h3 style="margin:0;">Report Attandance Summary</h3>
                    <p style="margin:0;">Period ' . $filter_from . ' to ' . $filter_to . '</p>
                </center>
                <table style="font-size: 11px;">
                    <tr>
                        <td width="100">Division</td>
                        <td width="10">:</td>
                        <td><b>' . $division_name . '</b></td>
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
                    <tr>
                        <td>Group</td>
                        <td>:</td>
                        <td><b>' . $group_name . '</b></td>
                    </tr>
                </table>
                <br>';
                
                // Header tabel konsisten
                $html .= '<table id="customers" border="1">
                    <tr>
                        <th width="20" rowspan="2">No</th>
                        <th style="text-align:center;" rowspan="2">Departement Sub</th>
                        <th style="text-align:center;" rowspan="2">Employee ID</th>
                        <th style="text-align:center;" rowspan="2">Employee Name</th>
                        <th style="text-align:center;" rowspan="2">Group</th>
                        <th style="text-align:center;" rowspan="2">Shift</th>
                        <th style="text-align:center;" colspan="' . count($permit_types) . '">Permit</th>
                        <th style="text-align:center;" rowspan="2">Absence</th>
                        <th style="text-align:center;" rowspan="2">Late</th>
                        <th style="text-align:center;" rowspan="2">Change Days</th>
                        <th style="text-align:center;" rowspan="2">Working Days</th>
                    </tr>
                    <tr>';
                foreach ($permit_types as $permit_type) {
                    $html .= '<th style="text-align:center;">' . $permit_type->number . '</th>';
                }
                
                $html .= '</tr>';
                //Ambil Employee berdasarkan departement dan divisionnnya
                $this->db->select("
                    a.id as employee_id, 
                    a.division_id,
                    a.departement_id,
                    a.departement_sub_id,
                    a.number, a.name, 
                    a.date_sign,
                    a.division_id, 
                    a.departement_id, 
                    a.departement_sub_id, 
                    e.name as departement_sub_name, 
                    g.name as shift_name,
                    h.days,
                    i.name as group_name");
                $this->db->from('employees a');
                $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
                $this->db->join('shift_employees f', 'a.id = f.employee_id');
                $this->db->join('shifts g', 'f.shift_id = g.id');
                $this->db->join('shift_details h', 'h.shift_id = g.id');
                $this->db->join('groups i', 'a.group_id = i.id', 'left');
                $this->db->where('a.status', '0');
                $this->db->like('a.division_id', $filter_division);
                $this->db->like('a.departement_id', $filter_departement);
                $this->db->like('a.departement_sub_id', $filter_departement_sub);
                $this->db->like('a.group_id', $filter_group);
                $this->db->like('a.id', $filter_employee);
                $this->db->group_by('a.number');
                $this->db->order_by('e.name, a.name', 'asc');
                $employees = $this->db->get()->result_array();
                // die($this->db->last_query());
                $attandance_absece = 0;
                $attandance_total = 0;
                $attandance_days = 0;
                $change_days = 0;
                $total_days = 0;
                $total_late = 0;
                foreach ($employees as $data) {
                    $weekday = [];
                    $weekday2 = [];
                    $weekend = [];
                    $weekend2 = [];
                    for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $i);
                        $this->db->select('*');
                        $this->db->from('resignations');
                        $this->db->where('employee_id', $data['employee_id']);
                        $this->db->where('resign_date <', $working_date);
                        $resignation = $this->db->get()->row();
                        //Jika tanggal yg di looping bukan hari sabtu dan minggu
                        if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                            //Hari kerja
                            if ($data['date_sign'] <= $working_date) {
                                if (empty($resignation)) {
                                    $weekday[] = date('Y-m-d', $i);
                                }
                            }
                        } else {
                            //Hari libur
                            $weekend[] = date('Y-m-d', $i);
                        }
                        if (date('w', $i) !== '0') {
                            //Hari kerja
                            if ($data['date_sign'] <= $working_date) {
                                if (empty($resignation)) {
                                    $weekday2[] = date('Y-m-d', $i);
                                }
                            }
                        } else {
                            //Hari libur
                            $weekend2[] = date('Y-m-d', $i);
                        }
                    }
                    if ($data['days'] == "6") {
                        $weekend_day = $weekend2;
                        $weekday_day = $weekday2;
                    } else {
                        $weekend_day = $weekend;
                        $weekday_day = $weekday;
                    }
                    //Tanggal merah di master calendar
                    $this->db->select('trans_date');
                    $this->db->from('calendars');
                    $this->db->where('trans_date >=', $data['date_sign']);
                    $this->db->where("trans_date between '$filter_from' and '$filter_to'");
                    $this->db->where('description !=', "Weekend");
                    if (count($weekend2) > 0) {
                        $this->db->where_not_in('trans_date', $weekend2);
                    }
                    $calendar = $this->db->get()->result_array();
                    // if ($data['number'] == '22070011210433') { die($this->db->last_query()); }
                    $calendar_amount = empty($calendar) ? 0 : count($calendar);
                    $w_calendars = array();
                    foreach ($calendar as $cal) {
                        $w_calendars[] = $cal['trans_date'];
                    }
                    $tomorow = date('Y-m-d', strtotime("+1 day", strtotime($filter_from)));
                    //Attandances
                    $this->db->select("number, date_in");
                    $this->db->from('attandances');
                    $this->db->where('number', $data['number']);
                    $this->db->where("((date_in >= '$filter_from' and date_in <= '$filter_to') or (date_out >= '$tomorow' and date_out <= '$filter_to'))");
                    if (count($weekend_day) > 0) {
                        $this->db->where_not_in('date_in', $weekend_day);
                    }
                    if (count($w_calendars) > 0) {
                        $this->db->where_not_in('date_in', $w_calendars);
                    }
                    $this->db->group_by('date_in');
                    $attandances = $this->db->get()->result_array();
                    // die($this->db->last_query());
                    $attandance_amount = 0;
                    foreach ($attandances as $attandance) {
                        $attandance_amount++;
                    }
                    //Change Days
                    $this->db->select("COUNT(*) as days");
                    $this->db->from('change_days');
                    $this->db->where('employee_id', $data['employee_id']);
                    $this->db->where("(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')");
                    if (count($weekday_day) > 0) {
                        $this->db->where_in('end', $weekday_day);
                    }
                    $changeDays = $this->db->get()->row();
                    $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;
                    //Permit yang non deduction atau tidak potong gaji
                    $permit_date = "'" . implode("', '", $weekend_day) . "'";
                    $calendar_date = "'" . implode("', '", $w_calendars) . "'";
                    $q_permit = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                            FROM permit_types b
                            LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date)
                            WHERE b.absence = 'NO' and COALESCE(a.approved_to, '') IN ('','fitri')
                            GROUP BY b.id");
                    $r_permit = $q_permit->result_array();
                    // if ($data['number'] == '22070011210433') { die($this->db->last_query()); }
                    $arr_total_permit = 0;
                    foreach ($r_permit as $permit_data) {
                        $arr_total_permit += $permit_data['amount'];
                    }
                    //Permit yang deduction atau potong gaji
                    // $q_permit_deduction = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    // FROM permit_types b
                    // LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    // WHERE b.payroll = 'DEDUCTION' and COALESCE(a.approved_to, '') IN ('','fitri')
                    // GROUP BY b.id");
                    // $r_permit_deduction = $q_permit_deduction->result_array();
                    // $arr_total_permit_deduction = 0;
                    // foreach ($r_permit_deduction as $permit_data_deduction) {
                    //     $arr_total_permit_deduction += $permit_data_deduction['amount'];
                    // }
                    //Permit atau absen nya YES di anggap masuk kerja
                    $q_permit_absence = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) and a.permit_date not in ($calendar_date)
                    WHERE b.absence = 'YES' and COALESCE(a.approved_to, '') IN ('','fitri')
                    GROUP BY b.id");
                    $r_permit_absence = $q_permit_absence->result_array();
                    $arr_total_permit_absence = 0;
                    foreach ($r_permit_absence as $permit_data_absence) {
                        $arr_total_permit_absence += $permit_data_absence['amount'];
                    }
                    //Hitung Hari dia masuk kerja
                    $working_days = ($attandance_amount + $arr_total_permit_absence);
                    $hkw = (@count($weekday_day) - @$calendar_amount);
                    $absence = (@count($weekday_day) - @$calendar_amount - @$working_days - $arr_total_permit - @$changeDays_amount);
                    // if ($data['number'] == '22070011210433') { die(@count($weekday_day).' - '.@$calendar_amount.' - '.@$working_days.' - '.$arr_total_permit.' - '.@$changeDays_amount); }
                    if ($absence < 0) {
                        $absence_final = 0;
                    } else {
                        $absence_final = $absence;
                    }
                    // Hitung jumlah keterlambatan
                    $late_count = 0;
                    for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $i);
                        // Cek apakah hari kerja
                        if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                            // Ambil data attendance untuk tanggal tersebut
                            $this->db->select("a.time_in, d.start as shift_start");
                            $this->db->from('attandances a');
                            $this->db->join('shift_employees b', 'b.employee_id = ' . $data['employee_id'], 'left');
                            $this->db->join('shifts c', 'b.shift_id = c.id', 'left');
                            $this->db->join('shift_details d', 'c.id = d.shift_id', 'left');
                            $this->db->where('a.number', $data['number']);
                            $this->db->where('a.date_in', $working_date);
                            $this->db->where('a.time_in IS NOT NULL');
                            $this->db->where('a.time_in !=', '');
                            $attendance_data = $this->db->get()->result_object();
                            // if ($data['employee_id'] == 'KINP04250002') { die($this->db->last_query()); }
                            if ($attendance_data) {
                                foreach ($attendance_data as $key => $value) {
                                    $shift_start = strtotime($value->shift_start);
                                    $time_in = strtotime($value->time_in);

                                    $min = strtotime('-2 Hour', $time_in);
                                    $max = strtotime('+2 Hour', $time_in);

                                    // Jika terlambat (time_in > shift_start)
                                    if ($min <= $shift_start && $shift_start <= $max && $time_in > $shift_start)
                                        { $late_count++; }
                                }
                            }
                        }
                    }
                    //Permit
                    $q_permit = $this->db->query("SELECT b.name, COUNT(a.duration) as permit
                            FROM permit_types b
                            LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$data[employee_id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                            WHERE COALESCE(a.approved_to, '') IN ('','fitri')
                            GROUP BY b.id ORDER BY b.name asc");
                    $r_permit = $q_permit->result_array();
                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td>' . $data['departement_sub_name'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                                <td>' . $data['name'] . '</td>
                                <td>' . $data['group_name'] . '</td>
                                <td>' . $data['shift_name'] . '</td>';
                    $total_permit = 0;
                    foreach ($r_permit as $data_permit) {
                        $html .= '<td style="text-align:center;">' . $data_permit['permit'] . '</td>';
                        $total_permit += $data_permit['permit'];
                    }
                    $html .= '  <td style="text-align:center;">' . $absence_final . '</td>
                                <td style="text-align:center;">' . $late_count . '</td>
                                <td style="text-align:center;">' . $changeDays_amount . '</td>
                                <td style="text-align:center;">' . $working_days . '</td>
                            </tr>';
                    $attandance_absece += $absence_final;
                    $attandance_total += $working_days;
                    $change_days += @$changeDays_amount;
                    $total_days += $hkw;
                    $attandance_days += count($weekday_day);
                    $total_late += $late_count;
                    $no++;
                }
                //Total Permit Final
                $this->db->select("b.name, COUNT(a.duration) as permit");
                $this->db->from("permit_types b");
                $this->db->join(
                    "(SELECT a.employee_id, a.permit_type_id, a.permit_date, a.duration, b.departement_id, b.departement_sub_id, b.group_id FROM permits a JOIN employees b ON a.employee_id = b.id WHERE b.status = 0 and COALESCE(a.approved_to, '') IN ('','fitri')) a",
                    "a.permit_type_id = b.id and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.employee_id LIKE '%$filter_employee%' and a.departement_id like '%$filter_departement%' and a.departement_sub_id LIKE '%$filter_departement_sub%' and a.group_id LIKE '%$filter_group%'",
                    "left"
                );
                $this->db->group_by("b.id");
                $this->db->order_by("b.name", "ASC");
                $rt_permit = $this->db->get()->result_array();
                //Total Permit Final MP
                $q_permit_mp = $this->db->query("SELECT a.name, COUNT(b.employee_id) as employee FROM permit_types a 
                LEFT JOIN (SELECT a.employee_id, a.permit_type_id, a.permit_date, a.duration, b.departement_id, b.departement_sub_id, b.group_id FROM permits a JOIN employees b ON a.employee_id = b.id WHERE b.status = 0 and COALESCE(a.approved_to, '') IN ('','fitri') 
                and a.permit_date BETWEEN '$filter_from' and '$filter_to' and a.employee_id LIKE '%$filter_employee%' and b.departement_id LIKE '%$filter_departement%' and b.departement_sub_id LIKE '%$filter_departement_sub%' and b.group_id LIKE '%$filter_group%'
                GROUP BY a.employee_id, a.permit_type_id) b ON a.id = b.permit_type_id
                GROUP BY a.id ORDER BY a.name ASC");
                $rt_permit_mp = $q_permit_mp->result_array();
                //Grand Total Permit
                $html .= '  <tr><th colspan="6" style="text-align:right;">Grand Total Permit</th>';
                foreach ($rt_permit as $row) {
                    $html .= '<td style="text-align:center;">' . $row['permit'] . '</td>';
                }
                $html .= '  <th style="text-align:center;">' . $attandance_absece . '</th>
                            <th style="text-align:center;">' . $total_late . '</th>
                            <th style="text-align:center;">' . $change_days . '</th>
                            <th style="text-align:center;">' . $attandance_total . '</th>
                        </tr>';
                //Grand Total MP
                $html .= '  <tr><th colspan="6" style="text-align:right;">Grand Total MP</th>';
                foreach ($rt_permit_mp as $row_mp) {
                    $html .= '<td style="text-align:center;">' . $row_mp['employee'] . '</td>';
                }
                $html .= '  <th style="text-align:center;"></th>
                            <th style="text-align:center;"></th>
                            <th style="text-align:center;"></th>
                            <th style="text-align:center;"></th>
                        </tr>';
                $html .= '</table></div><br><br>';
            // Setelah loop selesai, baru echo seluruh HTML
            echo $header_html . $html . '</body></html>';
        }
    }
}
