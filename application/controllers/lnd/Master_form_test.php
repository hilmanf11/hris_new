<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_form_test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('MasterFormTestModel');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('crud');
        $this->load->model('LndModel');

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
            $this->load->view('lnd/master-form-test', $data);
        } else {
            redirect('error_session');
        }
    }

    public function datatables()
    {
        // Ambil parameter dari request
        $filter_training_name = $this->input->get('filter_training_name', true); // Sanitize input GET
        $filter_department = $this->input->get('filter_department', true); // Sanitize input GET
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        // Pagination
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        // Query Builder
        $this->db->start_cache(); // Cache query sebelum count_all_results
        $this->db->select("a.*, 
            CASE 
                WHEN a.question_type = 'SAME' THEN 'Pre-Test & Post Test is The Same'
                WHEN a.question_type = 'DIFFERENT' THEN 'Pre-Test & Post Test is Different'
                ELSE 'Unknown'
            END as type, c.trainingActivity as name
        ");
        $this->db->from('lnd_master_form_test a');
        $this->db->join('lnd_schedule_training b', 'b.id = a.training_name', 'left');
        $this->db->join('lnd_training_activity c', 'c.id = b.trainingName', 'left');
        
        if (!empty($filter_training_name)) {
            $this->db->where('b.id', $filter_training_name);
        }
        if (!empty($filter_department)) {
            $this->db->like('a.department', $filter_department);
        }

        $this->db->stop_cache(); // Stop caching the query

        // Hitung total data (tanpa limit dan offset)
        $totalRows = $this->db->count_all_results();
        // Ambil data dengan limit dan offset
        // $this->db->order_by('index', 'ASC'); // Pindahkan order_by setelah count_all_results

        // Ambil data dengan limit dan offset
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

    public function storeData() {
        // Ambil JSON string dari form multipart
        $json = $this->input->post('data');
        $data = json_decode($json, true);
        

        if (!$data) {
            return $this->response->send(ResponseStatus::BAD_REQUEST, [], 'Invalid JSON');
        }

        // Validasi manual (opsional)
        if (empty($data['training_name']) || empty($data['department']) || empty($data['questionType'])) {
            return $this->response->send(ResponseStatus::BAD_REQUEST, [], 'Missing required fields');
        }

        // Handle file upload jika ada
        $uploadedFiles = [];
        foreach ($_FILES as $field => $file) {
            if ($file['error'] == 0) {
                $uploadPath = 'assets/image/lnd/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['file_name']     = uniqid('img_');
                $this->load->library('upload', $config);

                if ($this->upload->do_upload($field)) {
                    $uploaded = $this->upload->data();
                    $uploadedFiles[$field] = 'assets/image/lnd/' . $uploaded['file_name'];
                } else {
                    return $this->response->send(ResponseStatus::BAD_REQUEST, [], $this->upload->display_errors('', ''));
                }
            }
        }

        // Simpan ke DB (pass both JSON data & uploadedFiles)
        $save = $this->MasterFormTestModel->insertQuestion($data, $uploadedFiles);
        if($save) {
            return $this->response->send(ResponseStatus::SUCCESS, $save, 'Form test successfully');
        }else{
            return $this->response->send(ResponseStatus::BAD_REQUEST, [], 'Get Competence data failed');
        }
    }

    public function storeData_v2() {
        log_message('debug', 'ISI $_FILES: ' . print_r($_FILES, true));
        log_message('debug', 'ISI $_POST: ' . print_r($_POST, true));
    
        $json = $this->input->post('data');
        $data = json_decode($json, true);
    
        if (!$data) {
            return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'code' => 400,
                        'status' => ResponseStatus::BAD_REQUEST,
                        'data' => [],
                        'message' => 'Invalid JSON'
                    ]));
        }
    
        if (isset($data['allDept'])) $data['department'] = $data['allDept'];
        if (empty($data['training_name']) || empty($data['department']) || empty($data['questionType'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Missing required fields'
                ]));
        }
    
        $uploadPath = 'assets/image/lnd/';
        if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'code' => 500,
                    'status' => ResponseStatus::SERVER_ERROR,
                    'data' => [],
                    'message' => 'Failed to create upload directory'
                ]));
        }
        if (!is_writable($uploadPath)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'code' => 500,
                    'status' => ResponseStatus::SERVER_ERROR,
                    'data' => [],
                    'message' => 'Upload directory is not writable'
                ]));
        }
    
        $uploadedFiles = [];
    
        foreach ($_FILES as $field => $fileGroup) {
            $files = $this->traverseFiles($field, $fileGroup); // get all nested files
            foreach ($files as $fileEntry) {
                $path = $fileEntry['field_path']; // e.g. ['question', 0, 'imageQuestion']
                $file = $fileEntry['file'];
                $path = is_array($fileEntry['field_path']) ? implode('.', $fileEntry['field_path']) : $fileEntry['field_path'];
                log_message('debug', 'Row PATH: ' . print_r($fileEntry['field_path'], true));
    
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fileName = $this->LndModel->upload_v2_direct($file, ['jpg', 'jpeg', 'png', 'gif'], $uploadPath);
                    if ($fileName) {
                        $this->setNestedValue($data, $path, $fileName);
                        $uploadedFiles[] = $uploadPath . $fileName;
                    } else {
                        log_message('error', 'FORM TEST: Upload gagal untuk ' . implode(' -> ', $path));
                        return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(400)
                        ->set_output(json_encode([
                            'code' => 400,
                            'status' => ResponseStatus::BAD_REQUEST,
                            'data' => [],
                            'message' => 'Upload gagal'
                        ]));
                    }
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errMsg = $this->getUploadError($file['error']);
                    log_message('error', 'FORM TEST: Error upload ' . implode(' -> ', $path) . " - $errMsg");
                    return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'code' => 400,
                        'status' => ResponseStatus::BAD_REQUEST,
                        'data' => [],
                        'message' => $errMsg
                    ]));
                }
            }
        }
    
        log_message('debug', 'DATA AKHIR SETELAH PROSES UPLOAD: ' . print_r($data, true));
    
        $save = $this->MasterFormTestModel->insertQuestion($data, $uploadedFiles);
        if ($save) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $data,
                    'message' => 'Form test saved successfully'
                ]));
        } else {
            foreach ($uploadedFiles as $filePath) @unlink(FCPATH . $filePath);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Failed to save form data'
                ]));
        }
    }
    
    
    /**
     * Helper untuk set nilai nested array dari path seperti question[0].imageQuestion
     */
    private function setNestedValue(&$array, $path, $value) {
        if (!is_string($path)) {
            log_message('error', 'setNestedValue expects string path, got: ' . print_r($path, true));
            return;
        }
    
        $keys = preg_split('/(?<!\\\)\./', $path); // untuk key seperti 'question.0..imageQuestion'
        foreach ($keys as &$key) {
            $key = str_replace('..', '.', $key);
        }
        unset($key);
    
        $temp = &$array;
        foreach ($keys as $key) {
            if (!isset($temp[$key]) || !is_array($temp[$key])) {
                $temp[$key] = [];
            }
            $temp = &$temp[$key];
        }
        $temp = $value;
    }
    
    
    
    // Helper untuk mendapatkan pesan error upload
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE in form',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by PHP extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    private function traverseFiles($fieldName, $fileArray, $path = [], &$results = []) {
        if (isset($fileArray['name']) && is_array($fileArray['name'])) {
            foreach ($fileArray['name'] as $key => $value) {
                $this->traverseFiles($fieldName, [
                    'name' => $fileArray['name'][$key],
                    'type' => $fileArray['type'][$key],
                    'tmp_name' => $fileArray['tmp_name'][$key],
                    'error' => $fileArray['error'][$key],
                    'size' => $fileArray['size'][$key]
                ], array_merge($path, [$key]), $results);
            }
        } else {
            $results[] = [
                'field_path' => array_merge([$fieldName], $path),
                'file' => $fileArray
            ];
        }
    
        return $results;
    }
    

    private function processUploadedFiles($data, $uploadedFiles) {
        // Inject uploaded file paths into the data array
        if (is_array($data) && is_array($uploadedFiles)) {
            foreach ($data['question'] as &$question) {
                if (isset($question['imageQuestion']) && isset($uploadedFiles[$question['imageQuestion']])) {
                    $question['imageQuestion'] = $uploadedFiles[$question['imageQuestion']];
                }
                foreach ($question['opsion'] as &$opsion) {
                    if (isset($opsion['image']) && isset($uploadedFiles[$opsion['image']])) {
                        $opsion['image'] =  $uploadedFiles[$opsion['image']];
                    }
                }
            }
    
            if(isset($data['post_question']) && is_array($data['post_question'])){
                 foreach ($data['post_question'] as &$question) {
                    if (isset($question['imageQuestion']) && isset($uploadedFiles[$question['imageQuestion']])) {
                        $question['imageQuestion'] = $uploadedFiles[$question['imageQuestion']];
                    }
                    foreach ($question['opsion'] as &$opsion) {
                        if (isset($opsion['image']) && isset($uploadedFiles[$opsion['image']])) {
                             $opsion['image'] =  $uploadedFiles[$opsion['image']];
                        }
                    }
                }
            }
    
    
        }
        return $data;
    }

    // GET DATA COMPTENCE
    public function readsTraining() 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('lnd_schedule_training');
        echo json_encode($send);
    }

    public function readsTrainings()
	{
		$this->db->start_cache();
		$this->db->select('a.*, b.trainingActivity as name');
		$this->db->from('lnd_schedule_training a');
		$this->db->join('lnd_training_activity b', 'b.id = a.trainingName', 'left');
		$this->db->stop_cache();
		$res = $this->db->get()->result_array();
		$this->db->flush_cache(); // Hapus cache query
		echo json_encode($res);
	}

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=competence_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('lnd_master_form_test');
        $this->db->order_by('index', 'ASC');
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
                            <small>MASTER DIVISION</small>
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
                <th>Competence Id</th>
                <th>Index</th>
                <th>Competence Name</th>
                <th>Remark</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['competenceId'] . '</td>
                    <td>' . $data['index'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['remark'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    

    public function update_data($id) {
        log_message('debug', 'ISI $_FILES SEBELUM LOOP: ' . print_r($_FILES, true));
        log_message('debug', 'ISI $_FILES: ' . print_r($_FILES, true));
        // Tambahkan juga ini untuk melihat isi $_POST
        log_message('debug', 'ISI $_POST: ' . print_r($_POST, true));

        $json = $this->input->post('data');
        $data = json_decode($json, true);
    
        if (!$data) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Invalid JSON'
                ]));
        }

        if (isset($data['allDept'])) $data['department'] = $data['allDept'];
    
        if (empty($data['training_name']) || empty($data['department']) || empty($data['questionType'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Missing required fields'
                ]));
        }
    
        // Handle file upload
        $uploadedFiles = [];
        foreach ($_FILES as $field => $file) {
            if ($file['error'] == 0) {
                $uploadPath = 'assets/image/lnd/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
    
                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['file_name']     = uniqid('img_');
                $this->load->library('upload', $config);
    
                if ($this->upload->do_upload($field)) {
                    $uploaded = $this->upload->data();
                    $uploadedFiles[$field] = 'assets/image/lnd/' . $uploaded['file_name'];
                } else {
                    return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'code' => 400,
                        'status' => ResponseStatus::BAD_REQUEST,
                        'data' => [],
                        'message' => $this->upload->display_errors('', '')
                    ]));
                }
            }
        }
    
        // Update ke DB
        $update = $this->MasterFormTestModel->updateQuestion($id, $data, $uploadedFiles);
        if ($update) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $update,
                    'message' => 'Form test updated successfully'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Failed to update form test'
                ]));
        }
    }

    //GET DATA
    public function readDept()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $division_id = $this->input->get('division_id') ? $this->input->get('division_id') : "";

        $user = $this->crud->read("users", [], ["id" => $this->session->id]);
        if ($user->access == "0") {
            $departement_id = "";
        } else {
            $departement_id = $this->session->departement_id;
        }

        // Join ke table division on division.id = division_id
        $this->db->select('departements.*, divisions.name as division_name');
        $this->db->from('departements');
        $this->db->join('divisions', 'divisions.id = departements.division_id', 'left');
        if ($post !== "") {
            $this->db->like('departements.name', $post);
        }
        if ($division_id !== "") {
            $this->db->where('departements.division_id', $division_id);
        }
        if ($departement_id !== "") {
            $this->db->where('departements.id', $departement_id);
        }
        $query = $this->db->get();
        $send = $query->result_array();

        echo json_encode($send);
    }

    public function update_data_v2($id) {
        log_message('debug', 'ISI $_FILES: ' . print_r($_FILES, true));
        log_message('debug', 'ISI $_POST: ' . print_r($_POST, true));
    
        $json = $this->input->post('data');
        $data = json_decode($json, true);
    
        if (!$data) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Invalid JSON'
                ]));
        }
    
        if (isset($data['allDept'])) {
            $data['department'] = $data['allDept'];
        }
    
        if (empty($data['training_name']) || empty($data['department']) || empty($data['questionType'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Missing required fields'
                ]));
        }
    
        $uploadPath = 'assets/image/lnd/';
        if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'code' => 500,
                    'status' => ResponseStatus::SERVER_ERROR,
                    'data' => [],
                    'message' => 'Failed to create upload directory'
                ]));
        }
        if (!is_writable($uploadPath)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'code' => 500,
                    'status' => ResponseStatus::SERVER_ERROR,
                    'data' => [],
                    'message' => 'Upload directory is not writable'
                ]));
        }
    
        $uploadedFiles = [];
    
        foreach ($_FILES as $field => $fileGroup) {
            $files = $this->traverseFiles($field, $fileGroup); // Ambil semua file nested
            foreach ($files as $fileEntry) {
                $path = $fileEntry['field_path']; // array path: ['question', 0, 'imageQuestion']
                $file = $fileEntry['file'];
                $flatPath = is_array($path) ? implode('.', $path) : $path;
    
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fileName = $this->LndModel->upload_v2_direct($file, ['jpg', 'jpeg', 'png', 'gif'], $uploadPath);
                    if ($fileName) {
                        $this->setNestedValue($data, $flatPath, $fileName);
                        $uploadedFiles[] = $uploadPath . $fileName;
                    } else {
                        log_message('error', 'FORM TEST: Upload gagal untuk ' . $flatPath);
                        return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(400)
                        ->set_output(json_encode([
                            'code' => 400,
                            'status' => ResponseStatus::BAD_REQUEST,
                            'data' => [],
                            'message' => 'Upload gagal'
                        ]));
                    }
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errMsg = $this->getUploadError($file['error']);
                    log_message('error', 'FORM TEST: Error upload ' . $flatPath . " - $errMsg");
                    return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'code' => 400,
                        'status' => ResponseStatus::BAD_REQUEST,
                        'data' => [],
                        'message' => $errMsg
                    ]));
                }
            }
        }
    
        log_message('debug', 'DATA AKHIR SETELAH PROSES UPLOAD (UPDATE): ' . print_r($data, true));
    
        $update = $this->MasterFormTestModel->updateQuestion_v2($id, $data, $uploadedFiles);
        if ($update) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $data,
                    'message' => 'Form test updated successfully'
                ]));
        } else {
            foreach ($uploadedFiles as $filePath) @unlink(FCPATH . $filePath);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => [],
                    'message' => 'Failed to update form test'
                ]));
        }
    }
    
    

    public function get_detail($id) {
        $data = $this->MasterFormTestModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'code' => 404,
                    'status' => ResponseStatus::NOT_FOUND,
                    'data' => null,
                    'message' => 'Get data failed'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $data,
                    'message' => 'Get data successfully'
                ]));
        } 
    }

    public function delete_data($id) {
        $data = $this->MasterFormTestModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::NOT_FOUND,
                    'data' => null,
                    'message' => 'Data not found'
                ]));
        } else {
            $this->MasterFormTestModel->delete_data($id);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $id,
                    'message' => 'Master Form Test Deleted successfully'
                ]));
        }
    }

    public function list()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $competenceId = $this->input->get('competenceId') ? $this->input->get('competenceId') : "";
        $send = $this->crud->reads('lnd_master_form_test', ["competenceId" => $post, "competenceId" => $competenceId], [], "", "index", "asc");
        echo json_encode($send);
    }
    //UPLOAD DATA
    public function generatedata()
    {
        error_reporting(0);

        if ($this->input->post('file_type') == "text") {
            
        } elseif ($this->input->post('file_type') == "excel") {
            require_once 'assets/vendors/excel_reader2.php';
            $target = basename($_FILES['file_upload']['name']);
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
            chmod($_FILES['file_upload']['name'], 0777);
            $file = $_FILES['file_upload']['name'];
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);

            for ($i = 3; $i <= $total_row; $i++) {
                $datas[] = array(
                    'name' => trim($data->val($i, 2)),
                    'index' => trim($data->val($i, 3)),
                    'remark' => trim($data->val($i, 4))
                );
            }

            $datas['total'] = count($datas);
            echo json_encode($datas);

            unlink($_FILES['file_upload']['name']);
        } else {
            echo json_encode("Format File Error");
        }
    }

    function uploadv2($filename, $extension, $path, $id = [], $table = "", $field = "")
    {
        //Setting Upload Image
        $file = $_FILES[$filename]["name"];
        $extension_explode = explode('.', $file);
        $extension_final = strtolower(end($extension_explode));
        $size = $_FILES[$filename]['size'];
        $temporary = $_FILES[$filename]['tmp_name'];
        $newname   = round(microtime(true)) . '.' . $extension_final;

        log_message('debug', 'Uploading file: ' . $file);
        log_message('debug', 'File name: ' . $newname);
        log_message('debug', 'File size: ' . $size);
        log_message('debug', 'File temp: ' . $temporary);
        log_message('debug', 'Upload path: ' . $path);


        if (in_array($extension_final, $extension) === true || $file == "") {
            if ($size < 2097152) {
                if ($file == "") {
                     log_message('debug', 'File is empty, checking for existing record.');
                    if ($id == []) {
                        //
                         log_message('debug', 'ID is empty, no existing record to retrieve.');
                    } else {
                        $records = $this->read($table, $id);
                        log_message('debug', 'Existing record: ' . print_r($records, true));
                        return @$records->$field;
                    }
                } else {
                    if ($id == []) {
                         log_message('debug', 'ID is empty, not deleting existing file.');
                        //
                    } else {
                        $records = $this->read($table, $id);
                        log_message('debug', 'Existing record: ' . print_r($records, true));
                        if(isset($records->$field)){
                             @unlink('"' . $records->$field . '"');
                             log_message('debug', 'Deleted existing file: ' . $records->$field);
                        }
                        else{
                            log_message('debug', 'Existing file does not exist: ' . $records->$field);
                        }

                    }

                    if (@move_uploaded_file($temporary, $path . $newname)) {
                         log_message('debug', 'File moved to: ' . $path . $newname);
                        return $newname;
                    }
                    else{
                         $errorMsg = "Failed to move uploaded file";
                         log_message('error', $errorMsg);
                         return $this->output
                            ->set_content_type('application/json')
                            ->set_status_header(500)
                            ->set_output(json_encode(['error' => $errorMsg]));
                    }


                }
            } else {
                $errorMsg = "Your file is too big a maximum of 2 mb";
                log_message('error', $errorMsg);
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Use 400 for client errors
                    ->set_output(json_encode(['error' => $errorMsg]));
                //show_error("Your file is too big a maximum of 2 mb", 200, "File Upload Error");
                //exit;
            }
        } else {
            $errorMsg = "Your extension file is not recognized";
            log_message('error', $errorMsg);
             return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Use 400 for client errors
                    ->set_output(json_encode(['error' => $errorMsg]));
            //show_error("Your extension file is not recognized", 200, "File Upload Error");
            //exit;
        }
    }

    private function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension.';
        default:
            return 'Unknown upload error.';
    }
}

    public function upload()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            // Validasi data duplikat terlebih dahulu
            $existingData = $this->crud->read('lnd_master_form_test', [
                "name" => $data['name'],
                "index" => $data['index']
            ]);

            if (!empty($existingData)) {
                echo json_encode(array("title" => "Data Duplicated", "message" => "please check Competene Name => ". $data['trainingActivity'], "theme" => "error"));
                return;
            }

            $idGenerateDate = $this->crud->autoidPrifix('lnd_master_form_test', 'competenceId', 'C'); 
            $data['competenceId'] = $idGenerateDate;
            $tempIndex = $data["index"];

            $queryIndex = $this->db->query("SELECT a.index FROM lnd_master_form_test AS a WHERE a.index = '$tempIndex' ");
            $resIndex = $queryIndex->row_array();

            if (!empty($resIndex)) {
                echo json_encode(array("title" => "Not Found", "message" => "Index  " . $data['index'] . " already exists ", "theme" => "error"));
            } else if(empty($data['name'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence Name cannot be Null", "theme" => "error"));            
            } else if(empty($data['index'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Index cannot be Null", "theme" => "error"));
            } else {
                $send = $this->crud->create('lnd_master_form_test', $data);
                echo $send;
            }
        }
    }

    public function uploadFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/competence.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadclearFailed()
    {
        @unlink('failed/competence.txt');
    }
    public function uploadDownloadFailed()
    {
        $file = "failed/competence.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }
}