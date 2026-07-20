<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Resignations extends CI_Controller
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
        $this->form_validation->set_rules('employee_id', 'Employee', 'required|min_length[1]|max_length[30]|is_unique[resignations.employee_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('relation/resignations');
        } else {
            redirect('error_access');
        }
    }

    public function readEmployees()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();

        $session_id = $this->session->id;
        $session_dept = $this->session->departement_id;

        $user = $this->crud->read("users", [], ["id" => $session_id]);
        if ($user->access == "0") {
            $departement_id = "";
        } else {
            $departement_id = $session_dept;
        }

        $this->db->select('a.*');
        $this->db->from('employees a');
        $this->db->join('resignations b', 'a.id = b.employee_id');
        $this->db->like('a.departement_id', $departement_id);
        $this->db->group_start();
        $this->db->like('a.number', $post);
        $this->db->or_like('a.name', $post);
        $this->db->group_end();
        $this->db->order_by('a.name', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readEmployeeResign(){
        $dateNow = date("Y-m-d");
        $date = date("Y-m-d", strtotime('-3 days', strtotime($dateNow)));

        $this->db->select('*');
        $this->db->from('cutoff');
        $this->db->order_by('start', 'desc');
        $cutoff = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('resignations');
        $this->db->where('resign_date <', $cutoff->start);
        $this->db->where('status_resign', "ON PROCEDURE");
        $this->db->where('status', '0');
        $onprocedure = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('resignations');
        $this->db->where('resign_date <', $dateNow);
        $this->db->where('status_resign', "UN PROCEDURE");
        $this->db->where('status', '0');
        $unprocedure = $this->db->get()->result_array();

        foreach($unprocedure as $record){
            $this->crud->update('employees', ["id" => $record['employee_id']], ["status" => 1, "status_date" => $cutoff->finish]);
            $this->crud->update('resignations', ["employee_id" => $record['employee_id']], ["status" => 1]);
        }

        if($cutoff){
            foreach($onprocedure as $record){
                $this->crud->update('employees', ["id" => $record['employee_id']], ["status" => 1, "status_date" => $cutoff->finish]);
                $this->crud->update('resignations', ["employee_id" => $record['employee_id']], ["status" => 1]);
            }
        }
    }

    //GET DATE SERVICE
    public function readService($dateSign = "", $dateResign = "")
    {
        $start  = date_create($dateSign);
        $end = date_create($dateResign); // waktu sekarang
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

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_resign_type = $this->input->get('filter_resign_type');
            $filter_reason = $this->input->get('filter_reason');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.name as employee_name, 
                b.number as employee_number, 
                b.date_sign,
                b.gender,
                b.jkn,
                b.jamsostek,
                d.name as division_name, 
                e.name as departement_name, 
                f.name as departement_sub_name, 
                c.name as reason_name,
                g.name as contract_name,
                h.name as position_name');
            $this->db->from('resignations a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('reason_resignations c', 'a.reason_resignation_id = c.id');
            $this->db->join('divisions d', 'b.division_id = d.id');
            $this->db->join('departements e', 'b.departement_id = e.id');
            $this->db->join('departement_subs f', 'b.departement_sub_id = f.id');
            $this->db->join('contracts g', 'b.contract_id = g.id');
            $this->db->join('positions h', 'b.position_id = h.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.resign_date >=', $filter_from);
            $this->db->where('a.resign_date <=', $filter_to);
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.resign_type', $filter_resign_type);
            $this->db->like('a.reason_resignation_id', $filter_reason);
            $this->db->order_by('b.name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            // die($this->db->last_query());
            //Mapping Data
            $data  = array();
            foreach ($records as $record) {
                $data[] =  array_merge($record, array("service" => $this->readService($record['date_sign'], $record['resign_date'])));
            }

            $result['total'] = $totalRows;
            $result = @array_merge($result, ['rows' => $data]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('resignations', $post);
                echo $send;

                $this->readEmployeeResign();
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
            $send = $this->crud->update('resignations', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('resignations', $data);
        $this->crud->update('employees', ["id" => $data['employee_id']], ["status" => 0, "status_date" => date("Y-m-d")]);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'employee_number' => $data->val($i, 2),
                'request_date' => $data->val($i, 3),
                'resign_type' => $data->val($i, 4),
                'resign_date' => $data->val($i, 5),
                'reason_code' => $data->val($i, 6),
                'remarks' => $data->val($i, 7)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/resignations.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/resignations.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/resignations.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['employee_number'], "status" => 0]);
            $reasons = $this->crud->read('reason_resignations', ["number" => $data['reason_code']]);

            if (!empty($employees)) {
                $resignations = $this->crud->read('resignations', ["employee_id" => $employees->id]);
                if (!empty($resignations)) {
                    echo json_encode(array("title" => "Failed", "message" => $data['employee_number'] . " Employee ID is Already Registered", "theme" => "error"));
                } elseif (!empty($reasons)) {
                    $date_1 = new DateTime($data['request_date']);
                    $date_2 = new DateTime($data['resign_date']);
                    $day = $date_2->diff($date_1)->days + 1;

                    if($day > 0){
                        if($day >= 30){
                            $status_resign = "ON PROCEDURE";
                        }else{
                            $status_resign = "UN PROCEDURE";
                        }

                        $post = array(
                            'employee_id' => $employees->id,
                            'reason_resignation_id' => $reasons->id,
                            'trans_date' => date("Y-m-d"),
                            'resign_type' => $data['resign_type'],
                            'request_date' => $data['request_date'],
                            'resign_date' => $data['resign_date'],
                            'remarks' => $data['remarks'],
                            'status_resign' => $status_resign,
                        );
    
                        $send = $this->crud->create('resignations', $post);
                        echo $send;

                        $this->readEmployeeResign();
                    }else{
                        echo json_encode(array("title" => "Date", "message" => "Request Date < Resign Date", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Found", "message" => $data['reason_code'] . " Reason ID Not Found", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Not Found", "message" => $data['employee_number'] . " Employee ID Not Found", "theme" => "error"));
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        $format_cell_text = NULL;
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=data_resignations_$format.xls");
            $format_cell_text = <<<HTML
                style="mso-number-format:'\@';"
            HTML;
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_resign_type = $this->input->get('filter_resign_type');
        $filter_reason = $this->input->get('filter_reason');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
            b.name as employee_name, 
            b.number as employee_number, 
            b.date_sign,
            b.gender,
            b.jkn,
            b.jamsostek,
            d.name as division_name, 
            e.name as departement_name, 
            f.name as departement_sub_name, 
            c.name as reason_name,
            g.name as contract_name,
            h.name as position_name');
        $this->db->from('resignations a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('reason_resignations c', 'a.reason_resignation_id = c.id');
        $this->db->join('divisions d', 'b.division_id = d.id');
        $this->db->join('departements e', 'b.departement_id = e.id');
        $this->db->join('departement_subs f', 'b.departement_sub_id = f.id');
        $this->db->join('contracts g', 'b.contract_id = g.id');
        $this->db->join('positions h', 'b.position_id = h.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.resign_date >=', $filter_from);
        $this->db->where('a.resign_date <=', $filter_to);
        $this->db->like('b.id', $filter_employee);
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        $this->db->like('a.resign_type', $filter_resign_type);
        $this->db->like('a.reason_resignation_id', $filter_reason);
        $this->db->order_by('b.name', 'ASC');
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
                            <small>DATA RESIGNATIONS</small>
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
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Position</th>
                <th>Employee Type</th>
                <th>JKN No</th>
                <th>BPJS TK</th>
                <th>Gender</th>
                <th>Resign Type</th>
                <th>Join Date</th>
                <th>Resign Date</th>
                <th>Fit For Service</th>
                <th>Reason</th>
                <th>Remarks</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td '.$format_cell_text.'>' . $data['employee_number'] . '</td>
                            <td>' . $data['employee_name'] . '</td>
                            <td>' . $data['division_name'] . '</td>
                            <td>' . $data['departement_name'] . '</td>
                            <td>' . $data['departement_sub_name'] . '</td>
                            <td>' . $data['position_name'] . '</td>
                            <td>' . $data['contract_name'] . '</td>
                            <td '.$format_cell_text.'>' . $data['jkn'] . '</td>
                            <td '.$format_cell_text.'>' . $data['jamsostek'] . '</td>
                            <td>' . $data['gender'] . '</td>
                            <td>' . $data['resign_type'] . '</td>
                            <td>' . $data['date_sign'] . '</td>
                            <td>' . $data['resign_date'] . '</td>
                            <td>' . $this->readService($data['date_sign'], $data['resign_date']) . '</td>
                            <td>' . $data['reason_name'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['status_resign'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
