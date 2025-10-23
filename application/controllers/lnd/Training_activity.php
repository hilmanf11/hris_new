<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Training_activity extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('TrainingActivityModel');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('crud');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
    }

    public function index() {
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['session_name'] = $this->session->name;
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header');
            $this->load->view('lnd/training-activity', $data);
        } else {
            redirect('error_session');
        }
    }

    public function datatables()
    {
        // Ambil parameter dari request
        $competenceId = $this->input->get('competenceId', true); // Sanitize input GET
        $trainingActivityId = $this->input->get('id', true); // Sanitize input GET
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        // Pagination
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        // Query Builder
        $this->db->start_cache(); // Cache query sebelum count_all_results
        $this->db->select('a.*, b.name as competenceName');
        $this->db->from('lnd_training_activity a');
        $this->db->join('lnd_competence b', 'a.competenceId = b.id');
        
        if (!empty($trainingActivityId)) {
            $this->db->like('a.id', $trainingActivityId);
        }
        if (!empty($competenceId)) {
            $this->db->like('a.competenceId', $competenceId);
        }
        $this->db->stop_cache(); // Stop caching the query
        
        // Hitung total data (tanpa limit dan offset)
        $totalRows = $this->db->count_all_results();
        
        // Ambil data dengan limit dan offset
        $this->db->order_by('a.index', 'ASC');
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();
        $this->db->flush_cache(); // Hapus cache query

        // Mapping Data
        $result = [
            'total' => $totalRows,
            'rows' => $records
        ];

        // Kirim sebagai JSON
        echo json_encode($result);
    }

    public function get_data() {
        $data = $this->TrainingActivityModel->get_all_data();

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Get Training Activity data failed'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Training Activity data successfully'
            ]));
        } 
    }

    public function get_detail($id) {
        $data = $this->TrainingActivityModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Get Training Activity data failed'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Training Activity data successfully'
            ]));
        } 
    }

    public function create_data() {
        // Ambil request body secara manual
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);
        // Generate trainingActivityId
        $idGenerateDate = $this->crud->autoidPrifix('lnd_training_activity', 'trainingActivityId', 'T'); 
        $data['trainingActivityId'] = $idGenerateDate;
        $data['id'] = $this->uuid();

        $existingData = $this->crud->read('lnd_training_activity', [
            "index" => $data['index']
        ]);

        if (!empty($existingData)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Data Duplicated. please check Training Activity => '. $data['trainingActivity']
            ]));
            echo json_encode(array("title" => "Data Duplicated", "message" => "please check Training Activity => ". $data['trainingActivity'], "theme" => "error"));
            return;
        }
        

        // Validasi dan proses data
        if (!empty($data)) {
            $dataTemp = $this->TrainingActivityModel->insert_data($data);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $dataTemp,
                'message' => 'Training Activity created successfully'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Training Activity creation failed.'
            ]));
        }
    }
    

    public function update_data($id) {
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);
        // $payloadId   = base64_decode($id);

        if (!empty($data)) {
            $dataTemp = $this->TrainingActivityModel->update_data($id, $data);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $dataTemp,
                'message' => 'Training Activity updated successfully'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Training Activity updated failed.'
            ]));
        }
    }

    public function delete_data($id) {
        $data = $this->TrainingActivityModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Data not found'
            ]));
        } else {
            $this->TrainingActivityModel->delete_data($id);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $id,
                'message' => 'Training Activity delete successfully'
            ]));
        }
    }

    public function list()
    {
        $trainingActivityId = $this->input->get('trainingActivityId') ? $this->input->get('trainingActivityId') : "";
        $send = $this->crud->query("SELECT a.*, b.name as competenceName FROM lnd_training_activity a JOIN lnd_competence b ON a.competenceId = b.id ORDER BY a.index ASC");
        echo json_encode($send);
    }

    // GET DATA COMPTENCE
    public function readsCompetence() 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('lnd_competence', [], [], "", "index", "asc");
        echo json_encode($send);
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
                'competenceId' => $data->val($i, 2),
                'trainingActivity' => $data->val($i, 3),
                'index' => $data->val($i, 4),
                'remarks' => $data->val($i, 5),
                'induction' => $data->val($i, 6)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/training_activity.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/training_activity.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            
            // Validasi data duplikat terlebih dahulu
            // Ubah pengecekan menjadi OR (atau) pada kondisi where
            $this->db->where('trainingActivity', $data['trainingActivity']);
            $this->db->or_where('index', $data['index']);
            $existingData = $this->db->get('lnd_training_activity')->result_array();

            if (!empty($existingData)) {
                echo json_encode(array("title" => "Data Duplicated", "message" => "please check Training Activity => ". $data['trainingActivity'], "theme" => "error"));
                return;
            }

            $tempCompetenceName = $data["competenceId"];

            $queryCompetence = $this->db->query("SELECT a.id FROM lnd_competence AS a WHERE UPPER(a.name) = UPPER('$tempCompetenceName')");
            $resCompetence = $queryCompetence->row_array();
            $data['competenceId'] = $resCompetence ? $resCompetence['id'] : null;

            $lnd_training_activity = $this->crud->read('lnd_training_activity', ["competenceId" => $data['competenceId'], "trainingActivity" => $data['trainingActivity'], "index" => $data['index'], "remarks" => $data['remarks'], "induction" => $data['induction']]);
            $idGenerateDate = $this->crud->autoidPrifix('lnd_training_activity', 'trainingActivityId', 'T'); 
            $data['trainingActivityId'] = $idGenerateDate;
            $data['id'] = $this->uuid();

            if (empty($resCompetence)) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence  " . $data['competenceId'] . " Not Found", "theme" => "error"));
            } else if(empty($data['trainingActivity'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence Training Activity cannot be Null", "theme" => "error"));            
            } else if(empty($data['induction'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence Induction cannot be Null", "theme" => "error"));
            } else if(empty($data['index'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence Index cannot be Null", "theme" => "error"));
            } else {
                $send = $this->crud->create('lnd_training_activity', $data);
                echo $send;
            }
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/training_activity.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=training_activity_$format.xls");
        }

        $induction = ['Basic Requirement', 'L&D Program: Upgrade Competence', 'L&D Program: Refresh Training', 'L&D Program: Training Activities'];

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as competenceName');
        $this->db->from('lnd_training_activity a');
        $this->db->join('lnd_competence b', 'a.competenceId = b.id');
        // Custom ORDER BY using FIELD for induction column
        $field_order = "FIELD(a.induction, " . implode(',', array_map(function($val) {
            return "'" . $val . "'";
        }, $induction)) . ")";

        $this->db->order_by('index', 'ASC');
        $this->db->order_by($field_order, '', false); // false agar tidak di-escape ulang
        $records = $this->db->get()->result_array();
        $logo = base_url('assets/image/logo_bei_2.jpg');

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>BANSHU ELECTRIC INDONESIA</b><br>
                            <small>HUMAN RESOURCE INFORMATION SYSTEM</small>
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
                <th>Training Activity ID</th>
                <th>Competence Name</th>
                <th>Index</th>
                <th>Induction</th>
                <th>Training Activity</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['trainingActivityId'] . '</td>
                    <td>' . $data['competenceName'] . '</td>
                    <td>' . $data['index'] . '</td>
                    <td>' . $data['induction'] . '</td>
                    <td>' . $data['trainingActivity'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}