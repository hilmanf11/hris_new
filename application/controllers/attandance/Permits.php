<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Permits extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[permits.number]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('attandance/permits');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('permits', ["name" => $post]);
        echo json_encode($send);
    }

    public function readRequest()
    {
        $this->db->select('b.name');
        $this->db->from('permits a');
        $this->db->join('users b', 'a.created_by = b.username');
        $this->db->group_by('a.created_by');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readShifts()
    {
        $employee_id = $this->input->post('employee_id');
        $this->db->select('c.name, c.days');
        $this->db->from('shift_employees a');
        $this->db->join('shifts b', 'a.shift_id = b.id');
        $this->db->join('shift_details c', 'b.id = c.shift_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.employee_id', $employee_id);
        $results = $this->db->get()->row();

        echo json_encode($results);
    }

    public function readLeave()
    {
        $employee_id = $this->input->post('employee_id');
        $permit_type_id = $this->input->post('permit_type_id');

        $year = date("Y");
        $this->db->select('c.name, c.days');
        $this->db->from('shift_employees a');
        $this->db->join('shifts b', 'a.shift_id = b.id');
        $this->db->join('shift_details c', 'b.id = c.shift_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.employee_id', $employee_id);
        $records = $this->db->get()->result_array();

        $calendar = $this->crud->reads("calendars", ["trans_date" => $year], ["permit" => 1]);

        $permitType = $this->crud->read('permit_types', [], ["id" => $permit_type_id, "cutoff" => "YES"]);
        $permits = $this->crud->reads('permits', ["employee_id" => $employee_id, "DATE_FORMAT(permit_date, '%Y')" => $year], ["permit_type_id" => @$permitType->id]);
        $totalPermit = 0;
        foreach ($permits as $permit) {
            $totalPermit += $permit->duration;
        }

        echo json_encode(@array_merge($records, ["total" => (12 - $totalPermit - count($calendar))]));
    }

    public function getDays()
    {
        $employee_id = $this->input->post('employee_id');
        $permit_type_id = $this->input->post('permit_type_id');
        $working_day = $this->input->post('working_day');
        $date_from = strtotime($this->input->post('date_from'));
        $date_to = strtotime($this->input->post('date_to'));
        $year = date("Y", $date_from);

        for ($i = $date_from; $i <= $date_to; $i += (60 * 60 * 24)) {
            if ($working_day == "6") {
                if (date('w', $i) !== '0') {
                    $weekdays[] = $i;
                } else {
                    $weekends[] = $i;
                }
            } else {
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $weekdays[] = $i;
                } else {
                    $weekends[] = $i;
                }
            }
        }
        $total_days = @count($weekdays);

        $calendar = $this->crud->reads("calendars", ["trans_date" => $year], ["permit" => 1]);

        $permitType = $this->crud->read('permit_types', [], ["id" => $permit_type_id, "cutoff" => "YES"]);
        $permits = $this->crud->reads('permits', [
            "employee_id" => $employee_id,
            "DATE_FORMAT(permit_date, '%Y')" => $year
        ], ["permit_type_id" => @$permitType->id]);
        $totalPermit = 0;
        foreach ($permits as $permit) { $totalPermit += $permit->duration; }

        echo json_encode(['days' => $total_days, 'total' => 12 - $totalPermit - count($calendar)]);
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
            $filter_permit_type = $this->input->get('filter_permit_type');
            $filter_request = $this->input->get('filter_request');
            $filter_approval = $this->input->get('filter_approval');
            $aprvDepartement = $this->checkApprovalAccess('permits');

            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*,
                c.number as employee_number,
                c.name as employee_name,
                d.name as division_name,
                e.name as departement_name,
                f.name as departement_sub_name,
                g.name as permit_type_name,
                h.name as reason_name,
                i.name as request_name
            ');
            $this->db->from('permits a');
            $this->db->join('employees c', 'a.employee_id = c.id');
            $this->db->join('divisions d', 'c.division_id = d.id');
            $this->db->join('departements e', 'c.departement_id = e.id');
            $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');
            $this->db->join('permit_types g', 'a.permit_type_id = g.id');
            $this->db->join('reasons h', 'a.reason_id = h.id');
            $this->db->join('users i', 'a.created_by = i.username');
            $this->db->where('a.deleted', 0);
            $this->db->where('c.deleted', 0);
            $this->db->where('c.status', 0);
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.permit_date >=', $filter_from);
                $this->db->where('a.permit_date <=', $filter_to);
            }
            $this->db->like('c.division_id', $filter_division);
            $this->db->like('c.departement_id', $filter_departement);
            $this->db->like('c.departement_id', $aprvDepartement);
            $this->db->like('c.departement_sub_id', $filter_departement_sub);
            $this->db->like('c.id', $filter_employee);
            $this->db->like('a.permit_type_id', $filter_permit_type);
            $this->db->like('a.created_by', $filter_request);
            if ($filter_approval == "0") {
                $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            } elseif ($filter_approval == "1") {
                $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
            }
            $this->db->order_by('a.permit_date', 'DESC');

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
            $post = $this->input->post();

            $date_from = strtotime($post['date_from']);
            $date_to = strtotime($post['date_to']);

            // $this->db->distinct()->select("b.days")->from("shift_employees a")
            // ->join("shift_details b", "a.shift_id = b.shift_id")->where(["a.employee_id" => $post['employee_id']]);
            // $shift = $this->db->get()->row()->days;

            $plant_sub_dept = '20240813000001'; // KI
            // $plant_sub_dept = '20260304000001'; // BRI

            $send = "";
            $leave = $post['leave'];
            for ($i = $date_from; $i <= $date_to; $i += (60 * 60 * 24)) {
                $permit_date = date('Y-m-d', $i);

                $mp = $this->crud->read("employees", [], ["id" => $post['employee_id']]);
                $count_attandance = $this->db->from('attandances a')->join('employees b', 'a.number = b.number')
                ->where("b.id", $post['employee_id'])->where("a.date_in", $permit_date)->count_all_results();
                // die($this->db->last_query());

                $this->db->select('b.attandance')->from('permits a')
                    ->join('permit_types b', 'a.permit_type_id = b.id')
                    ->where("a.employee_id", $post['employee_id'])
                    ->where("a.permit_date", $permit_date);
                $count_permit = $this->db->count_all_results();
                $permit = false;
                // die($this->db->last_query());
                if ($count_permit != 0) { $permit = $this->db->get()->row_array()['attandance'] == 'YES'; }

                if ($permit) {
                    echo json_encode(array("title" => "Available", "message" => "The permit requestion for this employee and permit date has been created", "theme" => "error"));
                    exit;
                } elseif ($count_attandance != 0) {
                    echo json_encode(array("title" => "Available", "message" => "The attandance for this employee and date in has been created", "theme" => "error"));
                    exit;
                } else {
                    if (date('w', $i) !== '0' || $mp->departement_sub_id == $plant_sub_dept) {
                        $attachment = $this->crud->upload('attachment', ['pdf', 'png', 'jpg', 'jpeg'], 'assets/image/attandance/');
                        $post_final = array(
                            "employee_id" => $post['employee_id'],
                            "permit_type_id" => $post['permit_type_id'],
                            "reason_id" => $post['reason_id'],
                            "trans_date" => $post['trans_date'],
                            "permit_date" => $permit_date,
                            "duration" => "1",
                            "leave" => ($leave - 1),
                            "note" => $post['note'],
                            "attachment" => $attachment
                        );
                        $send = $this->crud->create('permits', $post_final);
                    } else {
                        echo json_encode(array("title" => "Available", "message" => "The permit requestion for sunday cannot created", "theme" => "error"));
                        exit;
                    }
                }

                $leave = ($leave - 1);
            }

            echo $send;
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
            unset($post['working_day']);
            $attachment = $this->crud->upload('attachment', ['pdf', 'png', 'jpg', 'jpeg'], 'assets/image/attandance/');
            $post_final = array_merge($post, ["attachment" => $attachment]);
            $send = $this->crud->update('permits', ["id" => $id], $post_final);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $permit = $this->crud->read('permits', [], ["id" => $data['id']]);
        $send = $this->crud->delete('permits', $data);
        @unlink('assets/image/attandance/' . @$permit->attachment);
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
                'number' => $data->val($i, 2),
                'permit_type_number' => $data->val($i, 3),
                'reason_number' => $data->val($i, 4),
                'trans_date' => $data->val($i, 5),
                'date_from' => $data->val($i, 6),
                'date_to' => $data->val($i, 7),
                'note' => $data->val($i, 8)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/permits.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/permits.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/permits.txt";

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
            $post = $this->input->post('data');
            $date_from = strtotime($post['date_from']);
            $date_to = strtotime($post['date_to']);

            $employee = $this->crud->read('employees', ["number" => $post['number'], "status" => 0]);
            $permittype = $this->crud->read('permit_types', ["number" => $post['permit_type_number']]);
            $reason = $this->crud->read('reasons', ["number" => $post['reason_number']]);

            if (!empty($employee)) {
                if (!empty($permittype)) {
                    if (!empty($reason)) {
                        $send = "";

                        $year = date("Y", strtotime($date_from));

                        $calendar = $this->crud->reads("calendars", ["trans_date" => $year], ["permit" => 1]);
                        $permitTypeCutoff = $this->crud->read('permit_types', ["id" => $permittype->id, "cutoff" => "YES"]);
                        $permits = $this->crud->reads('permits', ["permit_date" => $year], ["employee_id" => $employee->id, "permit_type_id" => @$permitTypeCutoff->id]);
                        $totalPermit = (12 - count($calendar));
                        foreach ($permits as $leave) {
                            $totalPermit -= $leave->duration;
                        }

                        for ($i = $date_from; $i <= $date_to; $i += (60 * 60 * 24)) {
                            $permit_date = date('Y-m-d', $i);
                            $this->db->select('*')->from('permits')->where([
                                "employee_id" => $employee->id,
                                "permit_date" => $permit_date
                            ]);
                            $permit = $this->db->get()->row();

                            $this->db->distinct()->select('TRIM(id) AS id, TRIM(number) AS number')->from('attandances')
                            ->where(["number" => $employee->number, "date_in" => $permit_date]);
                            $attandance = $this->db->get()->row();

                            if (!empty($attandance)) {
                                echo json_encode(array("title" => "Available", "message" => "Permit date already exists in attendance for ID $employee->number on $permit_date", "theme" => "error"));
                                exit;
                            } elseif (!empty($permit)) {
                                echo json_encode(array("title" => "Available", "message" => "The permit requestion for this employee and permit date has been created", "theme" => "error"));
                                exit;
                            } else {
                                if ($totalPermit <= 0) {
                                    echo json_encode(array("title" => "Not Found", "message" => $employee->name . " This total permit is over", "theme" => "error"));
                                    exit;
                                } else {
                                    $post_permit = array(
                                        'employee_id' => $employee->id,
                                        'permit_type_id' => $permittype->id,
                                        'reason_id' => $reason->id,
                                        'trans_date' => $post['trans_date'],
                                        'permit_date' => $permit_date,
                                        'duration' => "1",
                                        'leave' => $totalPermit,
                                        'note' => $post['note']
                                    );

                                    $send = $this->crud->create('permits', $post_permit);
                                    $totalPermit = ($totalPermit - 1);
                                }
                            }
                        }

                        if ($send == "") {
                            echo json_encode(array("title" => "Error", "message" => " Date Format not text", "theme" => "error"));
                        } else {
                            echo $send;
                        }
                    } else {
                        echo json_encode(array("title" => "Not Found", "message" => $post['reason_number'] . " Reason ID Not Found", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Found", "message" => $post['permit_type_number'] . " Permit Type ID Not Found", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Not Found", "message" => $post['number'] . " Employee ID Not Found", "theme" => "error"));
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=permits_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_permit_type = $this->input->get('filter_permit_type');
        $filter_request = $this->input->get('filter_request');
        $filter_approval = $this->input->get('filter_approval');
        $aprvDepartement = $this->checkApprovalAccess('permits');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,
                c.number as employee_number,
                c.name as employee_name,
                d.name as division_name,
                e.name as departement_name,
                f.name as departement_sub_name,
                g.name as permit_type_name,
                h.name as reason_name,
                i.name as request_name
            ');
        $this->db->from('permits a');
        $this->db->join('employees c', 'a.employee_id = c.id');
        $this->db->join('divisions d', 'c.division_id = d.id');
        $this->db->join('departements e', 'c.departement_id = e.id');
        $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');
        $this->db->join('permit_types g', 'a.permit_type_id = g.id');
        $this->db->join('reasons h', 'a.reason_id = h.id');
        $this->db->join('users i', 'a.created_by = i.username');
        $this->db->where('a.deleted', 0);
        $this->db->where('c.deleted', 0);
        $this->db->where('c.status', 0);
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.permit_date >=', $filter_from);
            $this->db->where('a.permit_date <=', $filter_to);
        }
        $this->db->like('c.division_id', $filter_division);
        $this->db->like('c.departement_id', $filter_departement);
        $this->db->like('c.departement_id', $aprvDepartement);
        $this->db->like('c.departement_sub_id', $filter_departement_sub);
        $this->db->like('c.id', $filter_employee);
        $this->db->like('a.permit_type_id', $filter_permit_type);
        $this->db->like('a.created_by', $filter_request);
        if ($filter_approval == "0") {
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        } elseif ($filter_approval == "1") {
            $this->db->where("(a.approved_to != '' and a.approved_to is not null)");
        }
        $this->db->order_by('a.permit_date', 'DESC');
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
                            <small>DATA PERMIT</small>
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
                <th>Request No</th>
                <th>Request Name</th>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Permit Type</th>
                <th>Permit Date</th>
                <th>Reason</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['request_name'] . '</td>
                    <td>' . $data['employee_number'] . '</td>
                    <td>' . $data['employee_name'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['permit_type_name'] . '</td>
                    <td>' . $data['permit_date'] . '</td>
                    <td>' . $data['reason_name'] . '</td>
                    <td>' . $data['note'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
