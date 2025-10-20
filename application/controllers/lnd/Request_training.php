<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request_training extends CI_Controller {
    
    private $idGenerateDate;
    

    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('RequestTrainingModel');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('crud');
        $this->load->model('LndModel');
		$this->load->library('Ciqrcode');

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
            $this->load->view('lnd/request-training', $data);
        } else {
            redirect('error_session');
        }
    }

    public function datatables()
    {
        // Ambil parameter dari request
        $trainingMaterial = $this->input->get('trainingActivities', true); // Sanitize input GET
        $requestTrainingId = $this->input->get('id', true); // Sanitize input GET
        $reasons = $this->input->get('reasons', true); // Sanitize input GET
        $departement = $this->input->get('departement', true); // Sanitize input GET
        $suggestTrainingDate = base64_decode($this->input->get('suggestDateTraining', true)); // Sanitize input GET
        
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        // Pagination
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        // Query Builder
        $this->db->start_cache(); // Cache query sebelum count_all_results

        // Tambahkan DISTINCT pada select
        $this->db->select('DISTINCT
            a.*,
            DATE_FORMAT(a.suggestDateTraining, "%Y-%m-%d") as suggestDate,
            a.status as statusTraining,
            rth.status as statusApproval,
            rth.approved as statusApproved,
            rth.approval_data as approvedData,
            rth.approved_by as approved_by,
            rth.approved_date as approved_date,
            COALESCE(e.name, a.trainer_name) as trainerName,
            employeeApprover.gender as gender,
            u.name as approverName,
            userReject.name as inputter
        ', false);

        $this->db->from('lnd_request_training a');

        // Subquery untuk mendapatkan baris lnd_request_training_approvals_history terbaru/terpilih
        $latest_approval_subquery = '(
            SELECT
                rth_inner.*
            FROM
                lnd_request_training_approvals_history rth_inner
            INNER JOIN (
                SELECT
                    trainingRequestId,
                    MAX(approved_date) AS max_approved_date,
                    MAX(approved) AS max_approved_for_tiebreaker
                FROM
                    lnd_request_training_approvals_history
                GROUP BY
                    trainingRequestId
            ) AS latest_rth ON rth_inner.trainingRequestId = latest_rth.trainingRequestId
                            AND rth_inner.approved_date = latest_rth.max_approved_date
                            AND rth_inner.approved = latest_rth.max_approved_for_tiebreaker
        ) AS rth';

        $this->db->join($latest_approval_subquery, 'a.requestTrainingId = rth.trainingRequestId', 'left');

        $this->db->join('lnd_request_training_trainee lrtt', 'a.id = lrtt.trainingRequestId', 'left');
        $this->db->join('employees e', 'a.trainer_name = e.id', 'left');
        $this->db->join('users u', 'rth.approved_to = u.username', 'left');
        $this->db->join('employees employeeApprover', 'u.number = employeeApprover.number', 'left');
        $this->db->join('users userReject', 'rth.approved_by = userReject.username', 'left');
        $this->db->join('departements departementsCreator', 'lrtt.departement = departementsCreator.name', 'left');

        if (!empty($suggestTrainingDate)) {
            $this->db->where('a.suggestDateTraining', $suggestTrainingDate);
        }
        if (!empty($trainingMaterial)) {
            $this->db->like('a.trainingActivities', $trainingMaterial);
        }
        if (!empty($requestTrainingId)) {
            $this->db->like('a.id', $requestTrainingId);
        }
        if (!empty($reasons)) {
            $this->db->like('a.reasons', $reasons);
        }
        if (!empty($departement)) {
            $this->db->where('departementsCreator.id', $departement);
        }

        // $this->db->group_by('a.id');
        $this->db->stop_cache(); // Stop caching the query
        
        // Hitung total data (tanpa limit dan offset)
        $totalRows = $this->db->count_all_results();
        
        // Ambil data dengan limit dan offset
        $this->db->order_by('suggestDate', 'ASC');
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

    public function history_approval() {
        // Ambil parameter dari GET request
        $trainingRequestId = $this->input->get('trainingRequestId');
        
        // Validasi parameter
        if (empty($trainingRequestId)) {
            echo json_encode([
                'total' => 0,
                'rows' => []
            ]);
            return;
        }

        // Query untuk mengambil data history approval
        $this->db->select('
            rth.*,
            u.name as approver_name
        ');
        $this->db->from('lnd_request_training_approvals_history rth');
        $this->db->join('users u', 'rth.approved_by = u.username', 'left');
        $this->db->where('rth.trainingRequestId', $trainingRequestId);
        $this->db->where('rth.approved > 1');
        $this->db->order_by('rth.approved_date', 'ASC');
        
        $records = $this->db->get()->result_array();
        
        // Mapping Data
        $result = [
            'total' => count($records),
            'rows' => $records
        ];

        // Kirim sebagai JSON
        echo json_encode($result);
    }

    public function get_data() {
        $data = $this->RequestTrainingModel->get_all_data();

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => [],
                'message' => 'Get Request Training data failed'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Request Training data successfully'
            ]));
        } 
    }

    public function get_detail($id) {
        $data = $this->RequestTrainingModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Get Request Training data failed'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Request Training data successfully'
            ]));
        } 
    }

    public function create_data() {
//        $rawInput = file_get_contents("php://input");
//        parse_str($rawInput, $data);

        $data = $this->input->post();
		$idGenerateDateTemp = $this->crud->autoidPrifix('lnd_request_training', 'requestTrainingId', 'T');
        $data['id'] = $this->uuid();
        $data['requestTrainingId'] = $idGenerateDateTemp;
		    if (!empty($_FILES['attachment'])) {
            $attachment = $this->LndModel->upload_v2('attachment', ['jpg', 'pdf', 'jpeg', 'png', 'gif'], 'assets/document/request-training/');
            log_message('debug', 'ATTACHMENT: ' . $attachment);
            if ($attachment) {
                $data['attachment'] = $attachment;
            }
        }
        if (!empty($data)) {
            // $this->idGenerateDate = $dataTemp->id;
            $dataTemp = $this->RequestTrainingModel->insert_data($data);
            $approval = $this->crud->approvalsLnd('lnd_request_training_approvals_history', 'trainingRequestId', $data['requestTrainingId']);

            if ($approval) {
                log_message('error', 'Approval berhasil dibuat: ' . json_encode($approval));
            } else {
                log_message('error', 'Gagal membuat approval: ' . json_encode($approval));
            }
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(201)
            ->set_output(json_encode([
                'code' => 201,
                'status' => ResponseStatus::CREATED,
                'data' => $dataTemp,
                'message' => 'Request Training created successfully'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Request Training creation failed.'
            ]));
        }
    }
    

    public function update_data($id) {
        // $rawInput = file_get_contents("php://input");
        // parse_str($rawInput, $data);
        $data = $this->input->post();

        if (!empty($data)) {
            // Upload attachment jika ada file baru
            if (!empty($_FILES['attachment'])) {
                $attachment = $this->LndModel->upload_v2('attachment', ['jpg', 'pdf', 'jpeg', 'png', 'gif'], 'assets/document/request-training/');
                if ($attachment) {
                    $data['attachment'] = $attachment;
                }
            }
			$dataRequestTrainingExisting = $this->RequestTrainingModel->get_detail_data($id);
			if(!empty($dataRequestTrainingExisting)) {
				$dataApprovalHistoryExisting = $this->RequestTrainingModel->get_detail_data_history_approval($dataRequestTrainingExisting['requestTrainingId']);
				if(!empty($dataApprovalHistoryExisting) && $dataApprovalHistoryExisting['status'] == -1) {
					$dataApprovalHistoryExisting['status'] = 0;
					$resUpdateHistory = $this->RequestTrainingModel->update_data_request_training_history($dataApprovalHistoryExisting['id'], $dataApprovalHistoryExisting);
				}
			}
            $dataTemp = $this->RequestTrainingModel->update_data($id, $data);
			if(!empty($dataTemp) && !empty($resUpdateHistory)) {
                return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $dataTemp,
                    'message' => 'Request Training with update approval successfully'
                ]));
			} else {
                return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $dataTemp,
                    'message' => 'Request Training updated successfully'
                ]));
			}
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Request Training updated failed.'
            ]));
        }
    }

    public function delete_data($id) {
        $data = $this->RequestTrainingModel->get_detail_data($id);

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
            $this->RequestTrainingModel->delete_data($id);
            // Cek apakah kolom requestTrainingId tersedia dan tidak null
            if (!empty($data['requestTrainingId'])) {
                $this->RequestTrainingModel->delete_data_history($data['requestTrainingId']);
            }
            $this->RequestTrainingModel->delete_data_trainee($id);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $id,
                'message' => 'Request Training delete successfully'
            ]));
        }
    }

    public function list()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $requestTrainingId = $this->input->get('requestTrainingId') ? $this->input->get('requestTrainingId') : "";
        $send = $this->crud->reads('lnd_request_training', ["requestTrainingId" => $post, "requestTrainingId" => $requestTrainingId]);
        echo json_encode($send);
    }

    public function detailTrainee() 
    {
        $trainingRequestId = $this->input->get('trainingRequestId', true); // Sanitize input GET

        $this->db->start_cache(); // Cache query sebelum count_all_results
        $this->db->select('e.fullName, e.national_id, e.date_sign, e.position, e.departement, e.departement_subs, DATE_FORMAT(e.date_sign, "%Y-%m-%d") as join_date');
        $this->db->from('lnd_request_training_trainee e');
        $this->db->where('e.trainingRequestId', $trainingRequestId);
        $this->db->stop_cache(); // Stop caching the query
        
        // Hitung total data (tanpa limit dan offset)
        $totalRows = $this->db->count_all_results();
        
        // Ambil data dengan limit dan offset
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

        // GET DATA EMPLOYEES
    public function getEmployees() 
    {
        $post = isset($_POST['q']) ? array("e.name" => $_POST['q']) : $this->input->get();

        $this->db->select('e.id, e.name, e.number as employeeId, p.name as position, d.name as departement, ds.name as departement_subs, e.date_sign');
        $this->db->from('employees e');
        $this->db->join('positions p', 'p.id = e.position_id');
        $this->db->join('departements d', 'd.id = e.departement_id');
        $this->db->join('departement_subs ds', 'ds.id = e.departement_sub_id');
        $this->db->where('e.status', 0);
        
        if($post) {
            $this->db->like($post);
        }
        $query = $this->db->get();
        echo json_encode($query->result_object());
    }

    public function getTrainee($id) 
    {
        $send = $this->crud->reads('lnd_request_training_trainee', [], ["trainingRequestId" => $id]);
        echo json_encode($send);
    }

    public function create_data_trainee() {
        // Ambil request body secara manual
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);
        
        // Cek jika idGenerateDate ada, gunakan sebagai trainingRequestId
        if(!empty($this->idGenerateDate)) {
            $data['trainingRequestId'] = $this->idGenerateDate;
        } else if(empty($data['trainingRequestId'])) {
            $dataRequestTraining = $this->crud->read('lnd_request_training', [], [], "", 'createdTime', 'desc');
            $data['trainingRequestId'] = $dataRequestTraining->id;
        }

        // Validasi dan proses data
        if (!empty($data)) {
            if(!isset($data['id']) || $data['id'] == '') {
                $dataTemp = $this->RequestTrainingModel->insert_data_trainee($data);
                $this->idGenerateDate = null;
                return $this->output
                ->set_content_type('application/json')
                ->set_status_header(201)
                ->set_output(json_encode([
                    'code' => 201,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $data,
                    'message' => 'Request Training Trainee created successfully'
                ]));
            } else {
                $dataTemp = $this->RequestTrainingModel->update_data_trainee($data['id'], $data);
                $this->idGenerateDate = null;
                return $this->output
                ->set_content_type('application/json')
                ->set_status_header(201)
                ->set_output(json_encode([
                    'code' => 201,
                    'status' => ResponseStatus::CREATED,
                    'data' => $dataTemp,
                    'message' => 'Request Training Trainee updated successfully'
                ]));
            }
        } else {
            $this->idGenerateDate = null;
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Request Training Trainee creation failed.'
            ]));
        }
    }

    public function deleteTrainee()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('lnd_request_training_trainee', $data);
        echo $send;
    }

    public function reads() {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();

        $this->db->select('*');
        $this->db->from('lnd_request_training');
        if ($get) {
            $this->db->like($get);
        }
        $this->db->order_by('createdTime', 'desc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readsTrainee() {
        $post = isset($_POST['q']) ? array("a.name" => $_POST['q']) : $this->input->get();

        $this->db->select('a.name, a.id, a.number');
        $this->db->from('employees a');
        if ($post) {
            $this->db->like($post);
        }
        $this->db->group_by('a.name, a.id'); // Tambahkan a.id ke GROUP BY
        $this->db->order_by('a.created_date', 'desc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

	public function readsEmployeesLeaderUp() {
		$post = isset($_POST['q']) ? array("a.name" => $_POST['q']) : $this->input->get();

		$this->db->start_cache();
		$this->db->select('a.id, a.name, b.name as positionName');
		$this->db->from('employees a');
		$this->db->join('positions b', 'b.id = a.position_id', 'left');
		$this->db->where("(CAST(b.level AS UNSIGNED) <= 5 OR a.departement_sub_id = '20221213000007') AND a.status = 0", null, false);
		if(!empty($post)) {
			$this->db->like($post);
		}
		$this->db->stop_cache();
		$res = $this->db->get()->result_array();
		$this->db->flush_cache(); // Hapus cache query
		echo json_encode($res);
	}

  public function readsEmployeesStaffUp() {
		$post = isset($_POST['q']) ? array("a.name" => $_POST['q']) : $this->input->get();

		$this->db->start_cache();
		$this->db->select('a.id, a.name, b.name as positionName, a.number');
		$this->db->from('employees a');
		$this->db->join('positions b', 'b.id = a.position_id', 'left');
		$this->db->where("(CAST(b.level AS UNSIGNED) <= 6 OR a.departement_sub_id = '20221213000007') AND a.status = 0", null, false);
		if(!empty($post)) {
			$this->db->like($post);
		}
		$this->db->stop_cache();
		$res = $this->db->get()->result_array();
		$this->db->flush_cache(); // Hapus cache query
		echo json_encode($res);
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

	//PRINT & EXCEL DATA
	public function print($option = "", $id = '')
	{

		if ($option == "excel") {
			$format = date("Ymd");
			header("Content-type: application/vnd-ms-excel");
			header("Content-Disposition: attachment; filename=request_training_$format.xls");
			$lala = $id;
		}
		//Config
		$this->db->select('*');
		$this->db->from('config');
		$config = $this->db->get()->row();

		$escapedId = $this->db->escape($option == 'excel' ? $id : $option); // Will be quoted and escaped

		$sql = "
        SELECT
            a.*,
            DATE_FORMAT(a.suggestDateTraining, '%Y-%m-%d') as suggestDate,
            a.status as statusTraining,
            rth.status as statusApproval,
            rth.approved as statusApproved,
            rth.approval_data as approvedData,
            rth.approved_by as approved_by,
            rth.approved_date as approved_date,
            COALESCE(e.name, a.trainer_name) as trainerName,
            employeeApprover.gender as gender,
            u.name as approverName,
            userReject.name as inputter,
            departementsCreator.name as departmentInputter
        FROM lnd_request_training a
        LEFT JOIN (
            SELECT rth_inner.*
            FROM lnd_request_training_approvals_history rth_inner
            INNER JOIN (
                SELECT
                    trainingRequestId,
                    MAX(approved_date) AS max_approved_date,
                    MAX(approved) AS max_approved_for_tiebreaker
                FROM lnd_request_training_approvals_history
                GROUP BY trainingRequestId
            ) AS latest_rth
            ON rth_inner.trainingRequestId = latest_rth.trainingRequestId
            AND rth_inner.approved_date = latest_rth.max_approved_date
            AND rth_inner.approved = latest_rth.max_approved_for_tiebreaker
        ) AS rth ON a.requestTrainingId = rth.trainingRequestId
        LEFT JOIN employees e ON a.trainer_name = e.id
        LEFT JOIN users u ON rth.approved_to = u.username
        LEFT JOIN employees employeeApprover ON u.number = employeeApprover.number
        LEFT JOIN users userReject ON rth.approved_by = userReject.username
        LEFT JOIN employees employeeReject ON userReject.number = employeeReject.number
        LEFT JOIN users userCreator ON userCreator.username = a.createdBy
        LEFT JOIN employees employeesCreator ON userCreator.number = employeesCreator.number
        LEFT JOIN departements departementsCreator ON departementsCreator.id = employeesCreator.departement_id
        WHERE a.id = $escapedId
    ";

		// Execute the query
		$query = $this->db->query($sql);
		$result = $query->row();

    // Ambil data approval history training
    $this->db->select('
            rth.*,
            DATE_FORMAT(rth.approved_date, "%Y-%m-%d") as approved_date_convert,
            u.name as approver_name
        ');
        $this->db->from('lnd_request_training_approvals_history rth');
        $this->db->join('users u', 'rth.approved_by = u.username', 'left');
        $this->db->where('rth.trainingRequestId', $result->requestTrainingId);
        $this->db->order_by('rth.approved', 'ASC');
    $historyApprovalTraining = $this->db->get()->result_array();

    // Inisialisasi variabel approval
    $approvedDateBod = '';
    $approvedDateDivHead = '';
    $approvedNameBod = '';
    $approvedNameDivHead = '';
    $approvedNamePIC = '';
    $approvedDatePIC = '';

    // Loop untuk mendapatkan data approval terakhir berdasarkan status
    // Cari nilai approved terbesar (maksimal) dari history approval
    $maxApproved = 0;
    foreach ($historyApprovalTraining as $approval) {
        if (is_numeric($approval['approved']) && intval($approval['approved']) > $maxApproved) {
            $maxApproved = intval($approval['approved']);
        }
    }

    // Validasi: BOD adalah approved terakhir (max), Div Head adalah approved terakhir - 1
    foreach ($historyApprovalTraining as $approval) {
        $approvedValue = is_numeric($approval['approved']) ? intval($approval['approved']) : 0;
        // PIC tetap diambil dari approved == 1
        if ($approvedValue === 2) {
            $approvedDatePIC = $approval['approved_date_convert'];
            $approvedNamePIC = $approval['approved_by'];
        }
        // Div Head adalah approved == maxApproved - 1
        if ($approvedValue === ($maxApproved - 1)) {
            $approvedDateDivHead = $approval['approved_date_convert'];
            $approvedNameDivHead = $approval['approved_by'];
        }
        // BOD adalah approved == maxApproved
        if ($approvedValue === $maxApproved) {
            $approvedDateBod = $approval['approved_date_convert'];
            $approvedNameBod = $approval['approved_by'];
        }
    }

    // Inisialisasi url QR code
    $qrUrlBOD = '';
    $qrUrlDivHead = '';
    $qrUrlPIC = '';

    // QR CODE UNTUK BOD
    $qrTextBOD = $approvedNameBod;
    if ($qrTextBOD != '') {
        $filenameBOD = 'qr_' . strtolower(str_replace(' ', '_', $qrTextBOD)) . '.png';
        $pathBOD = 'assets/image/qrcode/';
        $savePathBOD = FCPATH . $pathBOD . $filenameBOD;
        $paramsBOD = [
            'data' => $qrTextBOD,
            'level' => 'H',
            'size' => 2,
            'savename' => $savePathBOD
        ];
        $this->ciqrcode->generate($paramsBOD);
        $qrUrlBOD = base_url($pathBOD . $filenameBOD);
    }

    // QR CODE UNTUK DIV HEAD
    $qrTextDivHead = $approvedNameDivHead;
    if ($qrTextDivHead != '') {
        $filenameDivHead = 'qr_' . strtolower(str_replace(' ', '_', $qrTextDivHead)) . '.png';
        $pathDivHead = 'assets/image/qrcode/';
        $savePathDivHead = FCPATH . $pathDivHead . $filenameDivHead;
        $paramsDivHead = [
            'data' => $qrTextDivHead,
            'level' => 'H',
            'size' => 2,
            'savename' => $savePathDivHead
        ];
        $this->ciqrcode->generate($paramsDivHead);
        $qrUrlDivHead = base_url($pathDivHead . $filenameDivHead);
    }

    // QR CODE UNTUK PIC
    $qrTextPIC = $approvedNamePIC;
    if ($qrTextPIC != '') {
        $filenamePIC = 'qr_' . strtolower(str_replace(' ', '_', $qrTextPIC)) . '.png';
        $pathPIC = 'assets/image/qrcode/';
        $savePathPIC = FCPATH . $pathPIC . $filenamePIC;
        $paramsPIC = [
            'data' => $qrTextPIC,
            'level' => 'H',
            'size' => 2,
            'savename' => $savePathPIC
        ];
        $this->ciqrcode->generate($paramsPIC);
        $qrUrlPIC = base_url($pathPIC . $filenamePIC);
    }

		$html = '
		<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Permohonan Riquest Training</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        margin: 20px;
      }

      h2 {
        text-align: center;
        font-size: 16px;
        text-transform: uppercase;
      }

      .catatan-container {
        display: flex;
      }

      .arrow-box {
        position: relative;
        width: 100px;
        height: 45px;
        border: 1px solid black;
        margin-right: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
      }

      .arrow-box::after {
        content: "";
        position: absolute;
        right: -20px;
        top: 0;
        width: 0;
        height: 0;
        border-top: 22.5px solid transparent;
        border-bottom: 22.5px solid transparent;
        border-left: 20px solid black;
      }

      .catatan-text {
        font-size: 11px;
      }

      .catatan-text p {
        margin: 0 0 3px 0;
      }

      table {
        width: 100%;
        border-collapse: collapse;
      }

      td,
      th {
        border: 1px solid #000;
        padding: 4px;
        vertical-align: top;
      }

      .no-border {
        border: none !important;
      }

      .center {
        text-align: center;
      }

      .checkbox {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #000;
        margin-right: 4px;
        vertical-align: middle;
      }

	  .checkbox-checked  {
	    display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #000;
        margin-right: 4px;
        vertical-align: middle;
	  	color: #000;
	  }

      .signature {
        height: 80px;
        text-align: center;
        vertical-align: bottom;
      }

      .signature u {
        display: inline-block;
        margin-top: 40px;
      }

      .small-note {
        font-size: 10px;
        margin-top: 8px;
      }
    </style>
  </head>
  <body>
  <div style="position: relative;">
	  
		<table>
      <td class="no-border" colspan="8" style="text-align:right;">
			  <small>FRM-L&D-002 Rev.00</small>
      </td>
		</table>
  </div>
  <div>
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 12px; vertical-align: top; text-align: center; width: 10px;">
                <img src="' . $config->favicon . '" width="10">
            </td>
            <td colspan="2" style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>' . $config->description . '</small>
            </td>
            <td colspan="5" style="border-bottom:0">
              <div style="text-align: right;">
                  <small>Print Date ' . date("d M Y H:i:s") . '</small> <br>
                  <small>Print By ' . $this->session->username . '</small>
              </div>
            </td>
        </tr>
        <tr style="height:50px;">
          <td colspan="8" style="border-top:0; border-bottom:0;">
            &nbsp;
          </td>
        </tr>
        <tr style="height:50px;">
          <td colspan="8" style="border-top:0; border-bottom:0;">
            <h2 class="height:50px">PERMOHONAN REQUEST TRAINING</h2>
          </td>
        </tr>
    </table>

	  <table>
      <div class="catatan-container">
        <td style="padding-left: 10px; padding-top: 10px; padding-bottom: 0px;">
            <div class="arrow-box">Catatan</div>
        </td>
        <td colspan="7">
          <div class="catatan-text">
            <p>
              1. Formulir ini bisa digunakan untuk internal dan external training.
            </p>
            <p>
              2. Diisi oleh atasan (Dept. Head) untuk disetujui oleh management.
            </p>
            <p>3. Diserahkan kepada L&D Supervisor untuk ditindak lanjuti.</p>
            <p>
              4. Copy Certificate training diserahkan ke L&D supervisor untuk
              kepentingan up-dating dan Filling.
            </p>
          </div>
        </td>
      </div>
    </table>
    <table>
      <tr>
        <td colspan="2" style="border: none">Kepada</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none" width="50%">: BOD / L&D Divison Head</td>
        <td colspan="2" style="border-top: none; border-bottom: none;" width="20%"><center>Pemohon,</center></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Dari</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : '. $result->createdBy .'
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"><center>Dept. Head</center></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Dept</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : '. $result->departmentInputter .'
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"></td>
        
      </tr>
      <tr>
        <td colspan="2" style="border: none">Materi Training</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : '. $result->trainingActivities .'
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Nama Trainer</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : '. $result->trainerName .'
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Tgl. Training</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : '. $result->suggestDate .'
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Tempat</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          : PT. PIRANTI TEKNIK INDONESIA
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none;"></td>
      </tr>
      <tr>
        <td colspan="2" style="border: none">Jam</td>
        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none">
          :
        </td>
        <td colspan="2" style="border-top: none; border-bottom: none; text-align: center;">
        ';
        if($qrUrlPIC == '') {
          $html .= '<u>(............................)</u>';
        } else {
          $html .= '<img src="' . $qrUrlPIC . '" style="display: block; margin-left: auto; margin-right: auto;">';
        }
        $html .= '</td>
      </tr>
      <tr>
      	<td colspan="2" style="border-left: none; border-top: none; border-right: none">
      		Tick salah satu
      	</td>
      	<td colspan="4" style="border-left: none; border-top: none">
      	:
          <span class="checkbox">'. ($result->trainer === 'Internal' ? '✔' : '') .'</span> Trainer Internal &nbsp;&nbsp;&nbsp;
          <span class="checkbox-checked">'. ($result->trainer === 'External' ? '✔' : '') .'</span> Trainer External
        </td>
        <td colspan="2" style="border-top: none; text-align: center;">
        ';
        if($qrUrlPIC == '') {
          $html .= '<span style="display: inline-block; width: 100%; text-align: center;">Tgl: ....................</span>';
        } else {
          $html .= '<span style="display: inline-block; width: 100%; text-align: center;">Tgl: '.$approvedDatePIC.'</span>';
        }
        $html .= '</td>
	    </tr>
      <tr>
        <td colspan="8" style="border-bottom:0;">
          <p>
            <strong><u>EXTERNAL TRAINING</u></strong> (diisi hanya untuk external
            Training dan Sertakan proposal dan brosur)
          </p>
        </td>
      </tr>
      <tr>
          <td colspan="8" style="border-top:0; border-bottom:0;">
            &nbsp;
          </td>
        </tr>
      <tr>
        <td colspan="3" class="no-border">
          Perkiraan biaya untuk external training
        </td>
        <td colspan="2" class="no-border">
          Rp '. $result->trainerFees .'
        </td>
        <td colspan="3" style="border-top:0; border-bottom:0; border-left:0;">Penyelenggara :</td>
      </tr>
        <tr style="height:50px;">
          <td colspan="8" style="border-top:0; border-bottom:0;">
          <br/>
            <center><h2>CALON PESERTA TRAINING</h2></center>
            
          </td>
        </tr>
    </table>
    <table>
      <tr class="center">
        <th>No</th>
        <th colspan="2">Nama</th>
        <th>NIK</th>
        <th width="20%">Jabatan</th>
        <th width="15%">Bagian / Section</th>
        <th width="15%">Departemen</th>
        <th width="10%">Tanggal Masuk</th>
      </tr>';

		$this->db->select('*, DATE_FORMAT(date_sign, "%Y-%m-%d") as date_sign_converted');
		$this->db->from('lnd_request_training_trainee');
		$trainingIdForExcel = $option == 'excel' ? $id : $option;
		$this->db->where('trainingRequestId', $trainingIdForExcel);
		$traineeList = $this->db->get()->result_array();
		$no = 1;
		foreach ($traineeList as $trainee) {
		 $html .= '<tr>
			<td class="center">'. $no .'</td>
			<td colspan="2">'. $trainee['fullName'] .'aaa</td>
			<td>\''. strval($trainee['national_id']) .'</td>
			<td>'. $trainee['position'] .'</td>
			<td>'. $trainee['departement_subs'] .'</td>
			<td>'. $trainee['departement'] .'</td>
			<td>'. $trainee['date_sign_converted'] .'</td>
		  </tr>';
			$no++;
		}

      $html.= '<tr>
        <td class="center"></td>
        <td colspan="7">&nbsp;</td>
      </tr>
      <tr>
          <td colspan="8" style=" border-top:0; border-bottom:0;">
            <h2 style="text-align:left;">Alasan :</h2>
          </td>
      </tr>
      <tr>
        <td colspan="2"><span class="checkbox">'. ($result->reasons === 'Promotion' ? '✔' : '') .'</span> Promosi</td>
        <td colspan="3"><span class="checkbox">'. ($result->reasons === 'New Product' ? '✔' : '') .'</span> Produk baru</td>
        <td colspan="3"><span class="checkbox">'. ($result->reasons === 'New System' ? '✔' : '') .'</span> System baru</td>
      </tr>
      <tr>
        <td colspan="2"><span class="checkbox">'. ($result->reasons === 'Mutation' ? '✔' : '') .'</span> Mutasi</td>
        <td colspan="3"><span class="checkbox">'. ($result->reasons === 'New Technology' ? '✔' : '') .'</span> Technology baru</td>
        <td colspan="3"><span class="checkbox">'. ($result->reasons === 'Skill Upgrades' ? '✔' : '') .'</span> Peningkatan skill</td>
      </tr>
      <tr>
        <td colspan="8">
          <span class="checkbox"></span> Lain-lain:
          ..................................................
        </td>
      </tr>
      <tr>
          <td colspan="8" style="border-top:0; border-bottom:0;">
            
          </td>
        </tr>
      <tr>
          <td colspan="8" style="border-top:0; border-bottom:0;">
            <center><h2>APPROVAL</h2></center>
          </td>
        </tr>
    </table>';


		$html .= '<table>
      <tr>
        <td colspan="4" class="signature">
          BOD<br /><br /><br /><br />';
        if($qrUrlBOD == '') {
			$html .= '<u>(..................................)</u><br />Tgl: ....................';
		} else {
			$html .= '<img src = "' . $qrUrlBOD . '"<br /><br />Tgl:
			  '.$approvedDateBod.'';
		}
        $html .= '
        <br /></td>
        <td colspan="4" class="signature">
          L&D Div Head<br /><br /><br /><br />';

		if($qrUrlDivHead == '') {
			$html .= '<u>(..................................)</u><br />Tgl: ....................';
		} else {
			$html .= '<img src = "' . $qrUrlDivHead . '"<br /><br />Tgl: 
			  '.$approvedDateDivHead.'';
		}

        $html .= '</td>
      </tr>
    </table>

    <p class="small-note">
      <strong>Catatan :</strong><br />
      - Lampiran training diisi oleh bag. training jika schedule belum dibuat
      oleh pemohon.<br />
      - Pelaksanaan internal dan external training harus ada approval dari L&D
      Div Head dan BOD.
    </p>
    </div>
  </body>
</html>

		';
		echo $html;
	}
}
