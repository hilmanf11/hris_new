<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Machines extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[machines.number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('attandance/machines');
        } else {
            redirect('error_access');
        }
    }

    public function testing(){
        $ip = "160.150.140.44"; // IP mesin absensi
        $key = "0"; // CommKey

        $connect = fsockopen($ip, "80", $errno, $errstr, 1);
        if ($connect) {
            $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">$key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
            $newLine = "\r\n";

            fputs($connect, "POST /iWsService HTTP/1.0" . $newLine);
            fputs($connect, "Content-Type: text/xml" . $newLine);
            fputs($connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
            fputs($connect, $soap_request . $newLine);

            $buffer = "";
            while ($response = fgets($connect, 1024)) {
                $buffer .= $response;
            }

            // Parsing data
            function Parse_Data($data, $start, $end) {
                $data = " " . $data;
                $result = "";
                $start_pos = strpos($data, $start);
                if ($start_pos !== false) {
                    $end_pos = strpos(substr($data, $start_pos), $end);
                    if ($end_pos !== false) {
                        $result = substr($data, $start_pos + strlen($start), $end_pos - strlen($start));
                    }
                }
                return $result;
            }

            $buffer = Parse_Data($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
            $rows = explode("\r\n", $buffer);
            die(json_encode($rows));
            foreach ($rows as $row) {
                $data = Parse_Data($row, "<Row>", "</Row>");
                $pin = Parse_Data($data, "<PIN>", "</PIN>");
                $datetime = Parse_Data($data, "<DateTime>", "</DateTime>");
                echo "User: $pin - Waktu: $datetime<br>";
            }
        } else {
            echo "Koneksi gagal.";
        }

    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('machines', ["name" => $post], [], "", "name", "asc");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('*');
            $this->db->from('machines');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('machines', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('machines', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('machines', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        $ip = $this->input->post('ip');
        $port = $this->input->post('port');
        $keys = $this->input->post('keys');
        $period_from = $this->input->post('period_from');
        $period_to = $this->input->post('period_to');
        $type = $this->input->post('type');

        $dateFrom = strtotime($period_from);
        $dateTo = strtotime($period_to);

        $period = [];
        for ($i = $dateFrom; $i <= $dateTo; $i += (60 * 60 * 24)) {
            $period[] = date('Y-m-d', $i);
        }

        $connect = fsockopen($ip, $port, $errno, $errstr, 1);

        if($connect){
            $soap_request="<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">".$keys."</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
            $newLine="\r\n";
            fputs($connect, "POST /iWsService HTTP/1.0".$newLine);
            fputs($connect, "Content-Type: text/xml".$newLine);
            fputs($connect, "Content-Length: ".strlen($soap_request).$newLine.$newLine);
            fputs($connect, $soap_request.$newLine);
            $buffer="";
            while($response = fgets($connect, 1024)){
                $buffer = $buffer.$response;
            }
            $buffer = $this->Parse_Data($buffer,"<GetAttLogResponse>","</GetAttLogResponse>");
            $buffer = explode("\r\n",$buffer);
            // die(var_dump($buffer));

            $datas = array();
            // $lastPin = array();
            foreach ($buffer as $key => $value) {
                $data = $this->Parse_Data($value,"<Row>","</Row>");
                // if ($key == 19333) { die(var_dump($data)); }
                $PIN = $this->Parse_Data($data,"<PIN>","</PIN>");
                $DateTime = $this->Parse_Data($data,"<DateTime>","</DateTime>");

                $pecah = explode(" ", $DateTime);
                $date = $pecah[0];
                $time = $pecah[1];
                // if ($key == 19333) { die(var_dump($pecah).var_dump($period).var_dump(in_array($date, $period))); }
                if (in_array($date, $period)) {
                    array_push($datas, [
                        "pin" => $PIN,
                        "date" => $date,
                        "time" => $time,
                        "type" => $type
                    ]);
                }
                // if ($key == 19333) { die(var_dump($datas)); }
            }
            // for($a = 0; $a < count($buffer); $a++){
                // $data = $this->Parse_Data($buffer[$a],"<Row>","</Row>");

                // $PIN = $this->Parse_Data($data,"<PIN>","</PIN>");
                // $DateTime = $this->Parse_Data($data,"<DateTime>","</DateTime>");
                // $Verified = $this->Parse_Data($data,"<Verified>","</Verified>");
                // $Status = $this->Parse_Data($data,"<Status>","</Status>");
                // $WorkCode = $this->Parse_Data($data,"<WorkCode>","</WorkCode>");

                // $pecah = explode(" ", $DateTime);
                // $date = $pecah[0];
                // $time = $pecah[1];

                // if (in_array($date, $period)) {
                    // if(in_array($PIN, $lastPin)){
                        //
                    // }else{
                        // $datas[] = array(
                        //     "pin" => $PIN,
                        //     "date" => $date,
                        //     "time" => $time,
                        //     "type" => $type
                        // );
                    // }

                    // $lastPin[] = $PIN;
                // }
            // }

            die(json_encode(array("code" => 200, "message" => "Data Founded", "results" => $datas, "total" => count($datas))));
        }else{
            die(json_encode(array("code" => 500, "message" => "Not Connected to Machine Attandance")));
        }
    }

    function Parse_Data($data,$p1,$p2){
        $data=" ".$data;
        $hasil="";
        $awal=strpos($data,$p1);
        if($awal!=""){
            $akhir=strpos(strstr($data,$p1),$p2);
            if($akhir!=""){
                $hasil=substr($data,$awal+strlen($p1),$akhir-strlen($p1));
            }
        }
        return $hasil;  
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $date_in = $data['date'];
            $time_in = $data['time'];
            $type_in = $data['type'] == "IN";
            $to_time_in = strtotime($data['time']);

            //Employee
            $this->db->select('*');
            $this->db->from('employees');
            $this->db->where('machine_code', $data['pin']);
            $employee = $this->db->get()->row();

            //Jika Terdaftar di master employee
            if (!empty($employee->number)) {
                $data = [];
                $message = [
                    "title" => "Updated",
                    "message" => $employee->name . " | " . $date_in . " " . $time_in,
                    "theme" => "success"
                ];

                $this->db->distinct()->select("id")->from("attandances")
                    ->where("number", $employee->number)
                    ->where(($type_in) ? "date_in" : "date_out", $date_in);
                $attandances = $this->db->get()->row();
                // die(var_dump($attandances));

                if (empty($attandances)) {
                    $is_weekday = date('w') != 6;
                    $shift = null;
                    $ends = [];
                    $default_shift = [(object) [
                        'tolerance' => '0',
                        'shift_start' => '05:00:00',
                        'shift_end' => '17:00:00',
                        'shift_name' => 'SHIFT DEFAULT',
                        'shift_detail' => 'SHIFT DEFAULT PAGI',
                        'shift_days' => '5'
                    ],(object) [
                        'tolerance' => '0',
                        'shift_start' => '17:00:01',
                        'shift_end' => '04:59:59',
                        'shift_name' => 'SHIFT DEFAULT',
                        'shift_detail' => 'SHIFT DEFAULT MALAM',
                        'shift_days' => '5'
                    ]];
                    $data['status'] = "MACHINE";
                    $data['number'] = $employee->number;

                    $this->db->distinct()->select(
                         "TRIM(d.shift_id) AS id,"
                        ."TRIM(d.tolerance) AS tolerance,"
                        ."TRIM(d.start) AS shift_start,"
                        ."TRIM(d.end) AS shift_end,"
                        ."TRIM(c.name) AS shift_name,"
                        ."TRIM(d.name) AS shift_detail,"
                        ."TRIM(d.days) AS shift_days"
                    )->from("employees a")
                        ->join("shift_employees b", "a.id = b.employee_id")
                        ->join("shifts c", "b.shift_id = c.id")
                        ->join("shift_details d", "c.id = d.shift_id")
                    ->where("d.working", ($is_weekday) ? "WEEKDAY" : "WEEKEND")->where("a.number", $employee->number)
                    ->order_by("shift_start");
                    $shifts = $this->db->get()->result_object();
                    // die($this->db->last_query());

                    if (count($shifts) == 1) { $shift = $shifts[0]; }
                    else {
                        foreach ($shifts as $index => $row) {
                            $night = ($row->shift_start > $row->shift_end) ? ' 1 days' : '';
                            $tolerance = ($row->tolerance == '0') ? '2' : $row->tolerance;
                            $tolerance_end = strtotime($row->shift_end.' -'.$tolerance.' hours');
                            $tolerance_start = strtotime($row->shift_start.' +'.$tolerance.' hours'.$night);

                            // if ($index == 1) { die(date('Y-m-d H:i:s', $tolerance_end)); }
                            if (($type_in && $to_time_in <= $tolerance_start && empty($shift)) ||
                                (!$type_in && $to_time_in >= $tolerance_end)) { $shift = $row; }
                        }
                    }
                    // die(var_dump($shift));

                    if (empty($shift)) {
                        $morning_shift = $to_time_in >= strtotime($default_shift[0]->shift_start) && $to_time_in <= strtotime($default_shift[0]->shift_end);

                        if ($type_in) { $idx = ($morning_shift) ? 0 : 1; }
                        else { $idx = ($morning_shift) ? 1 : 0; }

                        $shift = $default_shift[$idx];
                    }

                    $data['shift_start'] = $shift->shift_start;
                    $data['shift_end'] = $shift->shift_end;
                    $data['shift_name'] = $shift->shift_name;
                    $data['shift_detail'] = $shift->shift_detail;
                    $data['shift_days'] = $shift->shift_days;
                    $is_night = $data['shift_start'] > $data['shift_end'];

                    $data['date_in'] = ($type_in) ? $date_in :
                        date('Y-m-d', strtotime($date_in.(($is_night) ? ' -1 days' : '')));
                    $data['date_out'] = (!$type_in) ? $date_in :
                        date('Y-m-d', strtotime($date_in.(($is_night) ? ' +1 days' : '')));

                    $message['title'] = "Created";
                    $message['message'] .= " Create data Check In";
                } else {$message['message'] .= " Update data Check In"; }

                $data[($type_in) ? 'time_in' : 'time_out'] = $time_in;
                // die(var_dump($data));

                if (empty($attandances)) { $this->crud->create('attandances', $data); }
                else { $this->crud->update('attandances', ["id" => $attandances->id], $data); }
                echo json_encode($message);

                // if ($type_in) {

                    //Attandance
                    // $this->db->select('*');
                    // $this->db->from('attandances');
                    // $this->db->where('number', $employee->number);
                    // $this->db->where("date_in = '$date_in'");
                    // $attandance_in = $this->db->get()->row();

                    // $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$time_in)));
                    // $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$time_in)));
                    // $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                    // $this->db->from('shift_employees b');
                    // $this->db->join('shifts c', 'c.id = b.shift_id');
                    // $this->db->join('shift_details d', 'd.shift_id = c.id');
                    // $this->db->where('b.employee_id', $employee->id);
                    // $this->db->where("TIME(d.start) >=  TIME('$tolerance_hour_min') && TIME(d.start) <= TIME('$tolerance_hour_plus')");
                    // $shift = $this->db->get()->row();

                    // $time_begin = strtotime($date_in . " " . $time_in);
                    // $time_end = strtotime($date_in . " " . @$shift->end);
                    // $tomorrow = date("Y-m-d", strtotime('+1 days', strtotime($date_in)));

                    // $diff = $time_begin - $time_end;
                    // $hour = floor($diff / (60 * 60));

                    // //die($date_in . " " . $tolerance_hour_plus . " - " . $date_in . " " . @$shift->end . " | " . $hour);
                    // if ($hour < 0) {
                    //     $date_out = $date_in;
                    // } else {
                    //     $date_out = $tomorrow;
                    // }

                    // if (!empty($attandance_in->number)) {
                    //     $data_attandance = array(
                    //         'number' => $employee->number,
                    //         'date_in' => $date_in,
                    //         'time_in' => $time_in,
                    //         'shift_start' => @$shift->start,
                    //         'shift_name' => @$shift->name,
                    //         'shift_detail' => @$shift->shift_name,
                    //         'shift_days' => @$shift->days,
                    //         'status' => "MACHINE",
                    //     );

                    //     $this->crud->update('attandances', ["id" => $attandance_in->id], $data_attandance);
                    //     echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check In", "theme" => "success"));
                    // } else {
                    //     $data_attandance = array(
                    //         'number' => $employee->number,
                    //         'date_in' => $date_in,
                    //         'time_in' => $time_in,
                    //         'date_out' => $date_out,
                    //         'shift_start' => @$shift->start,
                    //         'shift_name' => @$shift->name,
                    //         'shift_detail' => @$shift->shift_name,
                    //         'shift_days' => @$shift->days,
                    //         'status' => "MACHINE",
                    //         // 'time_out' => @$shift->end,
                    //     );
                    //     $this->crud->create('attandances', $data_attandance);
                    //     echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check In", "theme" => "success"));
                    // }
                    //echo json_encode(array("title" => "Warning", "message" => $employee->number . " Next", "theme" => "error"));
                // } else {
                    // $yesterday = date("Y-m-d", strtotime('-1 days', strtotime($date_in)));

                    // //Attandance
                    // $this->db->select('*');
                    // $this->db->from('attandances');
                    // $this->db->where('number', $employee->number);
                    // $this->db->where("date_out = '$date_in'");
                    // $attandance_out = $this->db->get()->row();

                    // $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$time_in)));
                    // $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$time_in)));
                    // $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");
                    // $this->db->from('shift_employees b');
                    // $this->db->join('shifts c', 'c.id = b.shift_id');
                    // $this->db->join('shift_details d', 'd.shift_id = c.id');
                    // $this->db->where('b.employee_id', $employee->id);
                    // $this->db->where("TIME(d.end) >=  TIME('$tolerance_hour_min') && TIME(d.end) <= TIME('$tolerance_hour_plus')");
                    // $shift = $this->db->get()->row();

                    // $time_begin = strtotime($date_in . " " . @$shift->start);
                    // $time_end = strtotime($date_in . " " . $time_in);

                    // $diff = $time_begin - $time_end;
                    // $hour = floor($diff / (60 * 60));

                    // // die($date_in . " " . @$shift->start . " - " . $date_in . " " . @$time_in . " | " . $hour);
                    // // die($hour);
                    // if ($hour < 0) {
                    //     $date_out = $date_in;
                    // } else {
                    //     $date_out = $yesterday;
                    // }

                    // if (!empty($attandance_out->number)) {
                    //     $data_attandance = array(
                    //         'number' => $employee->number,
                    //         'date_out' => $date_in,
                    //         'time_out' => $time_in,
                    //         'shift_start' => @$shift->start,
                    //         'shift_name' => @$shift->name,
                    //         'shift_detail' => @$shift->shift_name,
                    //         'shift_days' => @$shift->days,
                    //         'status' => "MACHINE",
                    //     );
                    //     $this->crud->update('attandances', ["id" => $attandance_out->id], $data_attandance);
                    //     echo json_encode(array("title" => "Updated", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Update data Check Out", "theme" => "success"));
                    // } else {
                    //     $data_attandance = array(
                    //         'number' => $employee->number,
                    //         'date_in' => $date_out,
                    //         // 'time_in' => @$shift->start,
                    //         'date_out' => $date_in,
                    //         'time_out' => $time_in,
                    //         'shift_start' => @$shift->start,
                    //         'shift_name' => @$shift->name,
                    //         'shift_detail' => @$shift->shift_name,
                    //         'shift_days' => @$shift->days,
                    //         'status' => "MACHINE",
                    //     );
                    //     $this->crud->create('attandances', $data_attandance);
                    //     echo json_encode(array("title" => "Created", "message" => $employee->name . " | " . $date_in . " " . $time_in . " Create data Check Out", "theme" => "success"));
                    // }
                    //echo json_encode(array("title" => "Warning", "message" => $employee->number . " Next", "theme" => "error"));
                // }
            } else {
                echo json_encode(array("title" => "Warning", "message" => "Machine ID Un Registered in Data Employee", "theme" => "error"));
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=machines_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('machines');
        $this->db->where('deleted', 0);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER MACHINE ATTANDANCE</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Ip Address</th>
                <th>Port</th>
                <th>Keys</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['ip'] . '</td>
                        <td>' . $data['port'] . '</td>
                        <td>' . $data['keys'] . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
