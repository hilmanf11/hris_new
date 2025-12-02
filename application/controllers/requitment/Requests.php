<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Requests extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('crud');

        //VALIDASI FORM
        $this->form_validation->set_rules('departement_sub_id', 'Departement', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            // die(json_encode($this->session));

            $this->load->view('template/header', $data);
            $this->load->view('requitment/requests', [
                'button_status' => $this->session->username == 'admin' || $this->session->departement_id == '20221119000010'
            ]);
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readRequests()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, b.name as division_name, c.name as departement_name, d.name as departement_sub_name, i.users_id_from as status_check, i.users_id_to as status_notification');
        $this->db->from('employee_requests a');
        $this->db->join('divisions b', 'a.division_id = b.id');
        $this->db->join('departements c', 'a.departement_id = c.id');
        $this->db->join('departement_subs d', 'a.departement_sub_id = d.id');
        $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
        $this->db->like('a.request_by', $post);
        $this->db->where('a.status', 0);
        $this->db->where("(i.users_id_to = '' or i.users_id_to is null)");
        $this->db->group_by('a.request_by');
        $this->db->group_by('a.request_date');
        $this->db->order_by('a.request_date', 'desc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readInformation()
    {
        $datenow = date("Y-m-d");
        $aprvDepartement = $this->checkApprovalAccess('employee_requests');
        $this->db->select('request_by, due_date');
        $this->db->from('employee_requests');
        $this->db->like('departement_id', $aprvDepartement);
        $this->db->where('due_date >=', $datenow);
        $this->db->order_by('due_date', 'asc');
        $records = $this->db->get()->result_array();

        foreach ($records as $data) {
            $date1 = new DateTime($datenow);
            $date2 = new DateTime($data['due_date']);
            $days = $date2->diff($date1)->days + 1;

            if ($days <= 2) {
                echo '<b style="margin-bottom:5px; color:red;">Timeout</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            } elseif ($days <= 10) {
                echo '<b style="margin-bottom:5px; color:orange;">Warning</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            } else {
                echo '<b style="margin-bottom:5px; color:green;">Progress</b> | This Request By <b>' . $data['request_by'] . '</b> only ' . $days . ' days left <br>';
            }
        }
    }

    public function readPrevEmployee()
    {
        $this->db->distinct()->select("TRIM(b.id) AS id, TRIM(b.name) AS name")
        ->from("resignations a")->join("employees b", "a.employee_id = b.id")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_request = $this->input->get('filter_request');
            $filter_approval = $this->input->get('filter_approval');
            $aprvDepartement = $this->checkApprovalAccess('employee_requests');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                i.users_id_from as status_check,
                i.users_id_to as status_notification, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name,
                b.name as position');
            $this->db->from('employee_requests a');
            $this->db->join('positions b', 'a.position_id = b.id');
            $this->db->join('divisions c', 'a.division_id = c.id');
            $this->db->join('departements d', 'a.departement_id = d.id');
            $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
            $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.departement_id', $aprvDepartement);
            $this->db->like('a.request_by', $filter_request);
            if ($filter_from != "" || $filter_to != "") {
                $this->db->where("a.request_date between '$filter_from' and '$filter_to'");
            }
            if ($filter_approval == "0") {
                $this->db->where("(i.users_id_to = '' or i.users_id_to is null)");
            } elseif ($filter_approval == "1") {
                $this->db->where("(i.users_id_to != '' or i.users_id_to is not null)");
            }
            $this->db->order_by('a.due_date', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $get = $this->db->get()->result();
            // die($this->db->last_query());
            $records = [];
            foreach ($get as $index => $row) {
                if ($row->status_request == 'progress') { $row->stat_request = 'Progress'; }
                elseif ($row->status_request == 'medical_document') { $row->stat_request = 'Medical & Document'; }
                elseif ($row->status_request == 'interview') { $row->stat_request = 'Interview'; }
                elseif ($row->status_request == 'psychotest') { $row->stat_request = 'Psychotest'; }
                elseif ($row->status_request == 'closed') { $row->stat_request = 'Closed'; }
                else { $row->stat_request = 'Submission'; }

                array_push($records, $row);
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function upload()
    {
        $loc = 'assets/document/reqruitment/attachment/';

        if (!is_dir($loc)) { mkdir($loc); }

        $fields = ['attachment_pmk', 'attachment_mp', 'attachment_so', 'attachment_job'];
        $result = [];

        foreach ($fields as $key => $value) {
            if (isset($_FILES[$value])) { array_push($result, [
                'index' => $value,
                'value' => $this->crud->upload($value, ['pdf', 'xlsx', 'xls'], $loc)
            ]); }
        }

        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                if (!isset($post['previous_emp'])) { $post['previous_emp'] = null; }
                if (!isset($post['attachment_pmk'])) { $post['attachment_pmk'] = null; }
                if (!isset($post['attachment_so'])) { $post['attachment_so'] = null; }
                if (!isset($post['attachment_job'])) { $post['attachment_job'] = null; }
                if (!isset($post['attachment_mp'])) { $post['attachment_mp'] = null; }

                $send = $this->crud->create('employee_requests', $post);
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

            if (!isset($post['previous_emp'])) { $post['previous_emp'] = null; }
            if (!isset($post['attachment_pmk'])) { $post['attachment_pmk'] = null; }
            if (!isset($post['attachment_so']) || $post['request_form'] == 'PKL') { $post['attachment_so'] = null; }
            if (!isset($post['attachment_job']) || $post['request_form'] == 'PKL') { $post['attachment_job'] = null; }
            if (!isset($post['attachment_mp'])) { $post['attachment_mp'] = null; }

            $send = $this->crud->update('employee_requests', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateStatus()
    {
        $id = base64_decode($this->input->post('id'));
        $status = base64_decode($this->input->post('status'));
        $send = json_decode($this->crud->update('employee_requests', ["id" => $id], ['status_request' => $status]), true);

        echo json_encode(['status' => (isset($send['theme']) ? '' : json_encode($send))]);
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_requests', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=employee_requests_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_request = $this->input->get('filter_request');
        $filter_approval = $this->input->get('filter_approval');
        $aprvDepartement = $this->checkApprovalAccess('employee_requests');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, 
                i.users_id_from as status_check,
                i.users_id_to as status_notification, 
                c.name as division_name, 
                d.name as departement_name,
                e.name as departement_sub_name,
                b.name as position');
        $this->db->from('employee_requests a');
        $this->db->join('positions b', 'a.position_id = b.id');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->join('notifications i', "a.id = i.table_id and i.table_name = 'employee_requests'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.departement_id', $aprvDepartement);
        $this->db->like('a.request_by', $filter_request);
        if ($filter_from != "" || $filter_to != "") {
            $this->db->where("a.request_date between '$filter_from' and '$filter_to'");
        }
        if ($filter_approval == "0") {
            $this->db->where("(i.users_id_to != '' or i.users_id_to != null)");
        } elseif ($filter_approval == "1") {
            $this->db->where("(i.users_id_to = '' or i.users_id_to = null)");
        }
        $this->db->order_by('a.due_date', 'ASC');
        $records = $this->db->get()->result_array();
        // die($this->db->last_query());
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
                            <small>EMPLOYEE REQUEST</small>
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
                <th>Request By</th>
                <th>Request Date</th>
                <th>Due Date</th>
                <th>Request Form</th>
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Position</th>
                <th>Request Type</th>
                <th>Qty</th>
                <th>Note</th>
                <th>PMK Form</th>
                <th>Organizational Structure</th>
                <th>Job Desc</th>
                <th>MP Need Analysis</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $btn_attachment_pmk = (empty($data['attachment_pmk'])) ? '' : '<a href="' . base_url('assets/document/reqruitment/attachment/') . $data['attachment_pmk'] . '">Download</a>';
            $btn_attachment_so = (empty($data['attachment_so'])) ? '' : '<a href="' . base_url('assets/document/reqruitment/attachment/') . $data['attachment_so'] . '">Download</a>';
            $btn_attachment_job = (empty($data['attachment_job'])) ? '' : '<a href="' . base_url('assets/document/reqruitment/attachment/') . $data['attachment_job'] . '">Download</a>';
            $btn_attachment_mp = (empty($data['attachment_mp'])) ? '' : '<a href="' . base_url('assets/document/reqruitment/attachment/') . $data['attachment_mp'] . '">Download</a>';

            if ($data['status_request'] == 'progress') { $data['status_request'] = 'Progress'; }
            elseif ($data['status_request'] == 'medical_document') { $data['status_request'] = 'Medical & Document'; }
            elseif ($data['status_request'] == 'interview') { $data['status_request'] = 'Interview'; }
            elseif ($data['status_request'] == 'psychotest') { $data['status_request'] = 'Psychotest'; }
            elseif ($data['status_request'] == 'closed') { $data['status_request'] = 'Closed'; }
            else { $data['status_request'] = 'Submission'; }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['request_by'] . '</td>
                    <td>' . $data['request_date'] . '</td>
                    <td>' . $data['due_date'] . '</td>
                    <td>' . $data['request_form'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['position'] . '</td>
                    <td>' . $data['request'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['description'] . '</td>
                    <td style="text-align: center">'.$btn_attachment_pmk.'</td>
                    <td style="text-align: center">'.$btn_attachment_so.'</td>
                    <td style="text-align: center">'.$btn_attachment_job.'</td>
                    <td style="text-align: center">'.$btn_attachment_mp.'</td>
                    <td style="text-align: center">'.$data['status_request'].'</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    //FORM PROCESS
    public function readForm()
    {
        $this->db->distinct()->select(
            "TRIM(a.request_form) AS div_form,".
            "TRIM(a.division_id) AS div_id,".
            "COALESCE(TRIM(b.name), '') AS div_name,".
            "TRIM(a.departement_id) AS dept_id,".
            "COALESCE(TRIM(c.name), '') AS dept_name,".
            "DATE(a.request_date) AS date"
        )->from("employee_requests a")
            ->join("divisions b", "a.division_id = b.id")
            ->join("departements c", "a.departement_id = c.id")
        ->order_by("div_name, dept_name");
        echo json_encode($this->db->get()->result());
    }

    public function printRequest($act = 'print')
    {
        $tick = '✔';
        $div = base64_decode($this->input->get('div'));
        $dept = base64_decode($this->input->get('dept'));
        $req_date = base64_decode($this->input->get('req_date'));
        $is_pkl = base64_decode($this->input->get('form')) == 'PKL';
        $sub_depts = [];
        $data = [];
        $fields = [
            '<no>' => 0,
            '<sub_dept>' => '',
            '<qty>' => '',
            '<gender>' => '',
            '<request_replace>' => '',
            '<request_new>' => '',
            '<additional>' => '',
            '<previous_nothing>' => ''
        ];

        if ($is_pkl) {
            $add_field = [
                '<length_service>' => '',
                '<school>' => '',
                '<height_155>' => '',
                '<height_none>' => '',
                '<previous_vacant>' => '',
                '<previous_internship>' => '',
                '<previous_mutation>' => '',
                '<previous_not_return>' => '',
                '<previous_potential>' => '',
                '<skill_computer>' => '',
                '<skill_other>' => '',
                '<note>' => ''
            ];
        } else {
            $add_field = [
                '<education>' => '',
                '<experience>' => '',
                '<type_permanent>' => '',
                '<type_contract>' => '',
                '<previous_termination>' => '',
                '<previous_resign>' => '',
                '<previous_mutation>' => '',
                '<previous_promotion>' => '',
                '<previous_demotion>' => '',
                '<source_internal>' => '',
                '<source_external>' => ''
            ];
        }
        $fields = array_merge($fields, $add_field);

        $this->db->distinct()->select("TRIM(name) AS name")->from("departements")->where("id", $dept);
        $dept_name = $this->db->get()->row()->name;

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("departement_subs")->where("departement_id", $dept);
        $get = $this->db->get()->result();
        foreach ($get as $key => $row) { $sub_depts[$row->id] = $row->name; }
        // die(var_dump($sub_depts));

        $this->db->distinct()->select(
            "TRIM(departement_sub_id) AS sub_dept,".
            "COALESCE(qty, 0) AS qty,".
            "TRIM(description) AS additional,".
            "TRIM(gender) AS gender,".
            "TRIM(education) AS education,".
            "TRIM(experience) AS experience,".
            "TRIM(request) AS request,".
            "TRIM(previous) AS previous,".
            "TRIM(candidate_sources) AS source,".
            "TRIM(length_service) AS length_service,".
            "TRIM(schools) AS school,".
            "TRIM(min_height) AS height,".
            "TRIM(skills) AS skill,".
            "TRIM(notes) AS note,".
            "created_date"
        )->from("employee_requests")
            ->where("division_id", $div)
            ->where("departement_id", $dept)
            ->where("request_date", $req_date)
            ->where("request_form", ($is_pkl) ? 'PKL' : 'Others')
        ->order_by('created_date');
        $get = $this->db->get()->result();
        foreach ($get as $key => $row) {
            $replace = $fields;

            $replace['<no>'] = $key + 1;
            $replace['<sub_dept>'] = $sub_depts[$row->sub_dept];
            $replace['<qty>'] = $row->qty;
            $replace['<gender>'] = (strtolower($row->gender) == 'male') ? 'Pria' : 'Wanita';
            $replace[($row->request == 'New Request') ? '<request_new>' : '<request_replace>'] = $tick;
            $replace['<additional>'] = $row->additional;

            if ($is_pkl) {
                $replace['<length_service>'] = $row->length_service;
                $replace['<school>'] = $row->school;
                $field = '<previous_nothing>';
                if ($row->previous == 'Vacant Position') { $field = '<previous_vacant>'; }
                elseif ($row->previous == 'Internship Period Ended') { $field = '<previous_internship>'; }
                elseif ($row->previous == 'Mutation') { $field = '<previous_mutation>'; }
                elseif ($row->previous == 'Did Not Return') { $field = '<previous_not_return>'; }
                elseif ($row->previous == 'Not Potential') { $field = '<previous_potential>'; }
                $replace[$field] = $tick;
                $replace[($row->height == 'None') ? '<height_none>' : '<height_155>'] = $tick;
                $replace[($row->skill == 'Others') ? '<skill_other>' : '<skill_computer>'] = $tick;
                $replace['<note>'] = $row->note;
            } else {
                $replace['<education>'] = $row->education;
                $replace['<experience>'] = $row->experience;
                $field = '<previous_nothing>';
                if ($row->previous == 'Termination') { $field = '<previous_termination>'; }
                elseif ($row->previous == 'Resign') { $field = '<previous_resign>'; }
                elseif ($row->previous == 'Mutation') { $field = '<previous_mutation>'; }
                elseif ($row->previous == 'Promotion') { $field = '<previous_promotion>'; }
                elseif ($row->previous == 'Demotion') { $field = '<previous_demotion>'; }
                $replace[$field] = $tick;

                $replace[($row->source == 'Internal') ? '<source_internal>' : '<source_external>'] = $tick;
            }

            array_push($data, $replace);
        }
        // die(base64_encode(json_encode($data)));

        ob_clean();
        $spreadsheet = IOFactory::load(FCPATH.'template/tmp_request_form.xlsx');
        $spreadsheet->setActiveSheetIndex(($is_pkl) ? 1 : 0);
        $sheet = $spreadsheet->getActiveSheet();
        $col = ($is_pkl) ? 'A3' : 'A6';
        $title = $sheet->getCell($col)->getValue();

        $col = ($is_pkl) ? 'I7' : 'AH7';
        $sheet->setCellValue('A7', $sheet->getCell('A7')->getValue().$dept_name);
        $sheet->setCellValue($col, $sheet->getCell($col)->getValue().$req_date);

        $ranges = [];
        $num_last = ($is_pkl) ? 28 : 21;
        $num_first = ($is_pkl) ? 10 : 11;
        $end_col = ($is_pkl) ? 'O' : 'AT';
        $num_next = $num_last + 1;
        $range = 'A'.$num_first.':'.$end_col.$num_last;
        array_push($ranges, $range);
        for ($i=0; $i < count($data) - 1; $i++) {
            $this->duplicateRangePreserveStyle($sheet, $range, $num_next);
            $num_last = $num_next + ($num_last - $num_first);
            $num_first = $num_next;
            $num_next = $num_last + 1;
            $range = 'A'.$num_first.':'.$end_col.$num_last;
            array_push($ranges, $range);
        }

        foreach ($ranges as $key => $range) {
            $data_key = array_keys($data[$key]);
            $data_value = array_values($data[$key]);
            $cellIterator = $sheet->rangeToArray($range, null, true, true, true);
            foreach ($cellIterator as $rowIndex => $row) {
                foreach ($row as $col => $value) {
                    $replace = str_replace($data_key, $data_value, $value);
                    if ($replace != $value)
                        { $sheet->setCellValue($col.$rowIndex, $replace); }
                }
            }
        }

        $spreadsheet->removeSheetByIndex(($is_pkl) ? 0 : 1);
        if ($act == 'print') {
            $writer = IOFactory::createWriter($spreadsheet, 'Html');
            ob_start();
            $writer->save('php://output');
            $html_excel = ob_get_clean();

            echo <<<HTML
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>$title</title>
                    <style>
                        table {
                            width: 100% !important;
                            table-layout: fixed;
                            border-collapse: collapse;
                        }
                        td, th {
                            word-wrap: break-word;
                            border: none !important;
                        }
                    </style>
                </head>
                <body>
                    <div>
                        $html_excel
                    </div>
                    <script>window.onload = () => window.print();</script>
                </body>
                </html>
            HTML;
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        }
    }

    function duplicateRangePreserveStyle($sheet, string $sourceRange, int $targetStartRow, bool $copyValues = true)
    {
        // parse source range
        [$start, $end] = explode(':', $sourceRange);
        [$startCol, $startRow] = preg_split('/(?=\d)/', $start, 2);
        [$endCol, $endRow]     = preg_split('/(?=\d)/', $end, 2);

        $startColIndex = Coordinate::columnIndexFromString($startCol);
        $endColIndex   = Coordinate::columnIndexFromString($endCol);
        $startRow = (int)$startRow;
        $endRow   = (int)$endRow;

        $rowCount = $endRow - $startRow + 1;
        $colCount = $endColIndex - $startColIndex + 1;

        $sheet->insertNewRowBefore($targetStartRow, $rowCount);

        // 1) Duplicate styles per cell (uses duplicateStyle)
        for ($r = 0; $r < $rowCount; $r++) {
            $srcRow = $startRow + $r;
            $dstRow = $targetStartRow + $r;

            // copy row height if set
            $srcRowDim = $sheet->getRowDimension($srcRow);
            $dstRowDim = $sheet->getRowDimension($dstRow);
            if ($srcRowDim->getRowHeight() !== -1) { // -1 means auto
                $dstRowDim->setRowHeight($srcRowDim->getRowHeight());
            } else {
                $dstRowDim->setRowHeight(-1); // keep auto
            }

            for ($c = 0; $c < $colCount; $c++) {
                $srcColIndex = $startColIndex + $c;
                $dstColIndex = $srcColIndex; // keep same column letter (if you want to shift columns, change here)
                $srcColLetter = Coordinate::stringFromColumnIndex($srcColIndex);
                $dstColLetter = Coordinate::stringFromColumnIndex($dstColIndex);

                $srcCell = $srcColLetter . $srcRow;
                $dstCell = $dstColLetter . $dstRow;

                // copy style object from src to dst
                $style = $sheet->getStyle($srcCell);
                $sheet->duplicateStyle($style, $dstCell);

                // copy value (optional)
                if ($copyValues) {
                    $srcCellObj = $sheet->getCell($srcCell);
                    $srcValue = $srcCellObj->getValue();
                    $srcType  = $srcCellObj->getDataType();

                    $dstCellObj = $sheet->getCell($dstCell);
                    $dstCellObj->setValue($srcValue, $srcType);
                }

                // copy comment if exists
                // $comment = $sheet->getComment($srcCell);
                // if ($comment && $comment->getText() !== null) {
                //     $sheet->getComment($dstCell)->getText()->createTextRun($comment->getText()->getPlainText());
                //     $sheet->getComment($dstCell)->setAuthor($comment->getAuthor());
                // }
            }
        }

        // 2) Copy column widths for all columns in the range (preserve exact width)
        for ($ci = $startColIndex; $ci <= $endColIndex; $ci++) {
            $colLetter = Coordinate::stringFromColumnIndex($ci);
            $srcColDim = $sheet->getColumnDimension($colLetter);
            // if width explicitly set, copy; otherwise leave default
            if ($srcColDim->getWidth() !== -1) {
                $sheet->getColumnDimension($colLetter)->setWidth($srcColDim->getWidth());
                // ensure width is not auto-size if original had fixed width
                $sheet->getColumnDimension($colLetter)->setAutoSize($srcColDim->getAutoSize());
            }
        }

        // 3) Handle merge ranges: find merges that intersect sourceRange and create corresponding merges at target
        $mergeRanges = $sheet->getMergeCells(); // returns array of 'A2:B2' => 'A2:B2'
        foreach ($mergeRanges as $mergedRange) {
            // check overlap: simple approach - check if mergedRange start..end inside sourceRange
            [$mStart, $mEnd] = explode(':', $mergedRange);
            // parse merged start
            [$mStartCol, $mStartRow] = preg_split('/(?=\d)/', $mStart, 2);
            [$mEndCol, $mEndRow]     = preg_split('/(?=\d)/', $mEnd, 2);
            $mStartColIndex = Coordinate::columnIndexFromString($mStartCol);
            $mEndColIndex   = Coordinate::columnIndexFromString($mEndCol);
            $mStartRow = (int)$mStartRow;
            $mEndRow   = (int)$mEndRow;

            // if merged range lies within the source rectangle
            if (
                $mStartColIndex >= $startColIndex && $mEndColIndex <= $endColIndex
                && $mStartRow >= $startRow && $mEndRow <= $endRow
            ) {
                // compute relative offset from source top-left
                $rowOffset = $mStartRow - $startRow;
                $colOffset = $mStartColIndex - $startColIndex;

                // target merged coordinates
                $tStartColIndex = $startColIndex + $colOffset;
                $tStartRow = $targetStartRow + $rowOffset;
                $tEndColIndex = $tStartColIndex + ($mEndColIndex - $mStartColIndex);
                $tEndRow = $tStartRow + ($mEndRow - $mStartRow);

                $tStartColLetter = Coordinate::stringFromColumnIndex($tStartColIndex);
                $tEndColLetter = Coordinate::stringFromColumnIndex($tEndColIndex);

                $targetMerge = sprintf('%s%d:%s%d', $tStartColLetter, $tStartRow, $tEndColLetter, $tEndRow);

                // create merge if not already merged
                $already = false;
                foreach ($sheet->getMergeCells() as $existing) {
                    if ($existing === $targetMerge) { $already = true; break; }
                }
                if (! $already) {
                    $sheet->mergeCells($targetMerge);
                }
            }
        }
    }
}
