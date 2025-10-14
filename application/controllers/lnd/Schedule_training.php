<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . '../vendor/autoload.php');

// Use PhpSpreadsheet classes
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Schedule_training extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('ScheduleTrainingModel');
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
            $this->load->view('lnd/schedule-training', $data);
        } else {
            redirect('error_session');
        }
    }

    public function datatables()
    {
        // Ambil parameter dari request
		$trainingName = $this->input->get('trainingName', true); // Sanitize input GET
		$registerDate = $this->input->get('registerDate', true); // Sanitize input GET
		$category = $this->input->get('category', true); // Sanitize input GET
		$trainee = $this->input->get('trainee', true); // Sanitize input GET
		$trainingDateFrom = $this->input->get('trainingDateFrom', true); // Sanitize input GET
		$trainingDateEnd = $this->input->get('trainingDateEnd', true); // Sanitize input GET

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        // Pagination
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
		// Query Builder
        $this->db->start_cache(); // Cache query sebelum count_all_results
        $this->db->select('a.*, a.id as id_training, b.*, rt.trainer as trainer_type, e.name, e.id as departementId, COALESCE(ta.id, rt.id) as trainingActivityId, COALESCE(ta.trainingActivity, rt.trainingActivities) as trainingActivity, st.trainer_name, st.trainer_id, st.trainer_id as trainingTrainerId');
        $this->db->from('lnd_schedule_training a');
        $this->db->join('lnd_schedule_training_dates b', 'a.id = b.training_id', 'left');
		$this->db->join('departements e', 'e.id = a.trainee', 'left');
		$this->db->join('lnd_request_training rt', 'rt.id = a.trainingName', 'left');
		$this->db->join('lnd_training_activity ta', 'ta.id = a.trainingName', 'left');
		$this->db->join('lnd_schedule_trainers st', 'a.id = st.training_id', 'left');

		if (!empty($registerDate)) {
			$this->db->where('a.registerDate', $registerDate);
		}
		if (!empty($trainingName)) {
			$this->db->like('a.trainingName', $trainingName);
		}
		if (!empty($category)){
			if($category == 'New') {
				$this->db->like('a.category', 'New');
			} else if ($category == 'Mutasi') {
				$this->db->like('a.category', 'Mutasi');
			} else {
				$this->db->like('a.category', 'Departement');
			}
		}
		if(!empty($trainee)){
			$this->db->like('a.trainee', $trainee);
		}
		if(!empty($trainingDateFrom) && !empty($trainingDateEnd)){
			$this->db->where("b.training_date BETWEEN '$trainingDateFrom' AND '$trainingDateEnd'");
		}
        $this->db->stop_cache(); // Stop caching the query
        
        // Hitung total data (tanpa limit dan offset)
//        $totalRows = $this->db->count_all_results();
        
        // Ambil data dengan limit dan offset
        $this->db->order_by('a.induction', 'ASC');
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();
        $this->db->flush_cache(); // Hapus cache query

		$grouped = [];

		foreach ($records as $row) {
			// === Grouping key per row ===
			$key = $row['trainingName'] . '|' . $row['trainee'] . '|' . $row['registerDate']; // You can customize this

			if (!isset($grouped[$key])) {
				$grouped[$key] = [
                    'id_training' => $row['id_training'],
					'induction' => $row['induction'],
					'trainingName' => $row['trainingActivity'],
		         	'trainer' => $row['trainer_type'],
					'trainee' => empty($row['name']) ? $row['category'] : $row['name'],
					'remarks' => $row['remarks'],
					'totalTrainee' => $row['totalTrainee'],
					'duration' => $row['duration'],
					'registerDate' => $row['registerDate'],
					'category' => $row['category'],
					'trainingActivityId' => $row['trainingActivityId'],
					'departementId' => $row['departementId'],
					'createdBy' => $row['createdBy'],
					'createdTime' => $row['createdTime'],
					'updatedBy' => $row['updatedBy'],
					'updatedTime' => $row['updatedTime'],
				];
			}

			$trainingDate = $row['training_date'];   // Example: 2025-04-22
			$batchCount = $row['batch_count'];       // Example: 1
			$weekLabel = $row['week_label'];         // Example: W1

			if ($trainingDate && $weekLabel) {
				$date = new DateTime($trainingDate);
				$monthName = $date->format('F');         // April
				$year = $date->format('Y');              // 2025
				$shortDate = $date->format('j M');       // 22 Apr

				$fieldName = "{$monthName}_{$year}_{$weekLabel}"; // April_2025_W1

				// Format tanggal asli ke YYYY-MM-DD
				$originalDateFormatted = $date->format('Y-m-d');

				// Jika sudah ada, tambahkan tanggal baru
				if (!empty($grouped[$key][$fieldName])) {
					$existingTrainingDates = explode(', ', $grouped[$key][$fieldName]);
					$existingOriginalDates = isset($grouped[$key]['originalTrainingDate']) ? explode(', ', $grouped[$key]['originalTrainingDate']) : [];

					if(!in_array($shortDate, $existingTrainingDates)) {
						$grouped[$key][$fieldName] .= ', ' . $shortDate;
						// Tambahkan tanggal asli dengan format YYYY-MM-DD
						if (!in_array($originalDateFormatted, $existingOriginalDates)) {
							$grouped[$key]['originalTrainingDate'] .= ', ' . $originalDateFormatted;
						}
					}
				} else {
					$grouped[$key][$fieldName] = $shortDate;
					$grouped[$key]['originalTrainingDate'] = $originalDateFormatted;
				}
			}

			$trainerName = $row['trainer_name'];
			$trainerId = $row['trainer_id'];
			$trainingTrainerid = $row['trainingTrainerId'];
			$trainerType = isset($row['trainer_type']) ? $row['trainer_type'] : null;
			
			// Validasi: Jika trainer_type == External, abaikan pengecekan trainerId (boleh kosong)
			if ($trainerType === 'External' && $trainerName) {
				if (!empty($grouped[$key]['trainerName'])) {
					$existingTrainers = explode(', ', $grouped[$key]['trainerName']);
					if (!in_array($trainerName, $existingTrainers)) {
						$grouped[$key]['trainerName'] .= ', ' . $trainerName;
						$grouped[$key]['trainingTrainerId'] .= ', '; // External tidak punya id, jadi dikosongkan
					}
				} else {
					$grouped[$key]['trainerName'] = $trainerName;
					$grouped[$key]['trainingTrainerId'] = '';
				}
			} elseif ($trainerName && $trainerId) {
				if (!empty($grouped[$key]['trainerName'])) {
					$existingTrainers = explode(', ', $grouped[$key]['trainerName']);

					// Only add if not already in the list
					if (!in_array($trainerName, $existingTrainers)) {
						$grouped[$key]['trainerName'] .= ', ' . $trainerName;
						$grouped[$key]['trainingTrainerId'] .= ', ' . $trainingTrainerid;
					}
				} else {
					$grouped[$key]['trainerName'] = $trainerName;
					$grouped[$key]['trainingTrainerId'] = $trainingTrainerid;
				}
			}
			if(strpos($row['induction'], 'L&amp;D') !== false) {
				$idTempIncorrectValue = $row['id_training'];
				$originalIncorrectValue = $row['induction'];
				$decodeIncorrectValue = html_entity_decode($row['induction']);

				if($originalIncorrectValue != $decodeIncorrectValue) {
					$this->db->where('id', $idTempIncorrectValue);
					$this->db->update('lnd_schedule_training', ['induction' => $decodeIncorrectValue]);
				}
			}
		}

		// Push all grouped data to final $data array
		$data = array_values($grouped);

		// Mapping Data
        $result = [
            'total' => count($data),
            'rows' => $data
        ];

        // Kirim sebagai JSON
        echo json_encode($result);
    }

    public function get_data() {
        $data = $this->ScheduleTrainingModel->get_all_data();

        if(empty($data)) {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => [],
                'message' => 'Get Schedule Training data failed'
            ]));
        } else {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Schedule Training data successfully'
            ]));
        } 
    }

    public function get_detail($id) {
        $data = $this->ScheduleTrainingModel->get_detail_data($id);

        if(empty($data)) {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Get Schedule Training data failed'
            ]));
        } else {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Schedule Training data successfully'
            ]));
        } 
    }

    public function get_form_test($id) {
        $data = $this->ScheduleTrainingModel->get_detail_form_test($id);

        if(empty($data)) {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Get master form test failed'
            ]));
        } else {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $data,
                'message' => 'Get Master Form Test successfully'
            ]));
        } 
    }

    public function create_data() {
        // Ambil raw input
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);

        // Check and decode training_dates (assumed as JSON string in POST)
        $trainingDates = [];
        $combineTrainer = [];
        if (!empty($data['training_dates'])) {
            $trainingDates = json_decode($data['training_dates'], true);
            unset($data['training_dates']); // Remove from main data to avoid DB issue
        }

        if (!empty($data['trainerName'])) {
            $trainerIdArr = $data['trainerName'];

            if (isset($data['trainer']) && $data['trainer'] === 'External') {
                // Jika trainer eksternal, langsung gunakan nama dari input trainerName[]
                foreach ($trainerIdArr as $extTrainerName) {
                    $combineTrainer[] = [
                        'id' => '', // Tidak ada ID untuk eksternal
                        'name' => $extTrainerName,
                    ];
                }
            } else {
                foreach ($trainerIdArr as $value) {
                    $trainer = $this->crud->read('employees', ['id' => $value]);
                    $combineTrainer[] = [
                        'id' => !empty($trainer->id) ? $trainer->id : '',
                        'name' => $trainer->name,
                    ];
                }
            }
            unset($data['trainerName']);
        }

        // Validasi dan proses data
        if (!empty($data)) {
            $dataTemp = $this->ScheduleTrainingModel->insert_data($data, $trainingDates, $combineTrainer);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(201)
                ->set_output(json_encode([
                    'code' => 201,
                    'status' => ResponseStatus::CREATED,
                    'data' => [$dataTemp, $combineTrainer],
                    'message' => 'Schedule Training created successfully'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => null,
                    'message' => 'Schedule Training creation failed.'
                ]));
        }
    }

    public function update_data($id) {
		$rawInput = file_get_contents("php://input");
		parse_str($rawInput, $data);

		$trainingDates = [];
		$combineTrainer = [];

		// Extract training dates
		if (!empty($data['training_dates'])) {
			$trainingDates = json_decode($data['training_dates'], true);
			unset($data['training_dates']); // Prevent DB insert issue
		}


		if (!empty($data['trainerName'])) {
            $trainerIdArr = $data['trainerName'];

            if (isset($data['trainer']) && $data['trainer'] === 'External') {
                // Jika trainer eksternal, langsung gunakan nama dari input trainerName[]
                foreach ($trainerIdArr as $extTrainerName) {
                    $combineTrainer[] = [
                        'id' => '', // Tidak ada ID untuk eksternal
                        'name' => $extTrainerName,
                    ];
                }
            } else {
                foreach ($trainerIdArr as $value) {
                    $trainer = $this->crud->read('employees', ['id' => $value]);
                    $combineTrainer[] = [
                        'id' => !empty($trainer->id) ? $trainer->id : '',
                        'name' => $trainer->name,
                    ];
                }
            }
            unset($data['trainerName']);
			unset($data['trainingTrainerId']);
        }

		// Proceed with update
		if (!empty($data)) {
			$dataTemp = $this->ScheduleTrainingModel->update_data($id, $data, $trainingDates, $combineTrainer);
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $dataTemp,
                'message' => 'Schedule Training updated successfully'
            ]));
		} else {
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Schedule Training update failed.'
            ]));
		}
    }

    public function delete_data($id) {
        $data = $this->ScheduleTrainingModel->get_detail_data($id);

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
            $this->ScheduleTrainingModel->delete_data($id);
			return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $id,
                'message' => 'Schedule Training delete successfully'
            ]));
        }
    }

    public function list()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $trainingActivityId = $this->input->get('trainingActivityId') ? $this->input->get('trainingActivityId') : "";
        $send = $this->crud->reads('lnd_schedule_training', ["trainingActivityId" => $post, "trainingActivityId" => $trainingActivityId]);
        $queryTrainingActivity = $this->crud->query("SELECT a.*, b.name as competenceName FROM lnd_schedule_training a JOIN lnd_competence b ON a.competenceId = b.id ");
        echo json_encode($queryTrainingActivity);
    }

    // GET DATA COMPTENCE
    public function readsCompetence() 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('lnd_competence', [], [], "", "index", "asc");
        echo json_encode($send);
    }

    // GET DATA DEPARTEMENTS
    public function readsDepartements() 
    {
		$post = isset($_POST['q']) ? $_POST['q'] : "";

		$departements = $this->crud->query("SELECT dep.id, dep.name, divs.name as division 
		FROM departements dep
		JOIN divisions divs ON dep.division_id = divs.id WHERE dep.name LIKE '%$post%'");
        echo json_encode($departements);
    }

	public function readsTrainingActivity()
	{
		$induction = $this->input->get('induction') ? $this->input->get('induction') : "";

		// Query 1: From lnd_training_activity
		$activity = $this->crud->query("SELECT a.*, b.name as competenceName 
        FROM lnd_training_activity a 
        JOIN lnd_competence b ON a.competenceId = b.id 
        WHERE a.induction = '$induction' 
        ORDER BY a.index ASC");

		// Query 2: From lnd_request_training
		$request = $this->crud->query("SELECT a.*, a.trainingActivities as trainingActivity, a.reasons as competenceName 
        FROM lnd_request_training a 
        JOIN lnd_request_training_approvals_history b 
            ON b.trainingRequestId = a.requestTrainingId 
        WHERE a.induction = '$induction' AND b.approval_data = 'COMPLETED' 
        ORDER BY a.requestTrainingId ASC");

		// Combine both into one response
		$combined = array_merge($activity, $request);

		echo json_encode($combined);
	}

	public function readsEmployeesLeaderUp()
	{
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
		$total_row_sheet_training_activity = $data->rowcount($sheet_index = 1);
		$total_row_sheet_trainer = $data->rowcount($sheet_index = 2);
		$total_row_sheet_trainee = $data->rowcount($sheet_index = 3);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'registerDate' => $data->val($i, 2),
				'trainingDates' => array_filter([
					$data->val($i, 3),
					$data->val($i, 5),
					$data->val($i, 7),
				]),
				'batchTrainingDates' => array_filter([
					$data->val($i, 4),
					$data->val($i, 6),
					$data->val($i, 8),
				]),
				'induction' => $data->val($i, 9),
//				'trainingName' => $data->val($i, 10),
				'category' => $data->val($i, 11),
//				'trainerNames' => array_filter([
//					$data->val($i, 12),
//					$data->val($i, 13),
//					$data->val($i, 14)
//				]),
//				'trainee' => $data->val($i, 15),
				'remarks' => $data->val($i, 16),
				'totalTrainee' => $data->val($i, 17),
				'duration' => $data->val($i, 18),
            );
        }

		for ($i = 3; $i <= $total_row_sheet_training_activity; $i++) {
			$index = 0;
			$datas[$index]['trainingName'] = $data->val($i, 2,1);
			$index++;
		}

		for ($i = 3; $i <= $total_row_sheet_trainer; $i++) {
			$index = 0;
			$datas[$index]['trainerNames'][] = $data->val($i, 2,2);
			$index++;
		}

		for ($i = 3; $i <= $total_row_sheet_trainee; $i++) {
			$index = 0;
			$datas[$index]['trainee'][] = $data->val($i, 2,3);
			$index++;
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
            $trainingNameId = $data["trainingName"];

            $queryTrainingActivity = $this->db->query("SELECT a.id FROM lnd_training_activity AS a WHERE a.trainingActivityId = '$trainingNameId' ");
            $resTrainingActivity = $queryTrainingActivity->row_array();
            $data['trainingName'] = $resTrainingActivity ? $resTrainingActivity['id'] : null;
			$dataTrainingDates = [];
			$dataTrainers = [];

			if(!empty($data['trainingDates'])){
				for ($i = 0; $i < count($data['trainingDates']); $i++) {
					$value = $data['trainingDates'][$i];

					$day = (int)date('j', strtotime($value)); // Extract day from date (1–31)
					if ($day >= 1 && $day <= 8) {
						$displayWeekType = 'W1';
					} elseif ($day >= 9 && $day <= 16) {
						$displayWeekType = 'W2';
					} elseif ($day >= 17 && $day <= 24) {
						$displayWeekType = 'W3';
					} elseif ($day >= 25 && $day <= 31) {
						$displayWeekType = 'W4';
					} else {
						$displayWeekType = 'Unknown'; // fallback in case of invalid day
					}
					$dataTrainingDates[$i]['training_date'] = $value;
					$dataTrainingDates[$i]['week'] = $displayWeekType;
					$dataTrainingDates[$i]['batch_count'] = $data['batchTrainingDates'][$i];
				}

				unset($data['batchTrainingDates']);
				unset($data['trainingDates']);
			}

			if(!empty($data['trainerNames'])){
				for ($i = 0; $i < count($data['trainerNames']); $i++) {
					$value = $data['trainerNames'][$i];
					$tempTrainerName = $this->crud->read('employees', ['number' => $value]);
					if ($tempTrainerName) {
						$dataTrainers[] = [
							'id' => $tempTrainerName->id,
							'name' => $tempTrainerName->name,
						];
					}
				}
				unset($data['trainerNames']);
			}

			if(empty($dataTrainingDates)){
				echo json_encode(array("title" => "Not Found", "message" => "Training Date Not Found", "theme" => "error"));
			} else if(empty($dataTrainers)){
				echo json_encode(array("title" => "Not Found", "message" => "Trainer Name Not Found", "theme" => "error"));
			} else if(empty($data['registerDate'])){
				echo json_encode(array("title" => "Not Found", "message" => "Register Date Not Found", "theme" => "error"));
			} else if(empty($data['induction'])){
				echo json_encode(array("title" => "Not Found", "message" => "Induction Not Found", "theme" => "error"));
			} else if(empty($data['trainingName'])){
				echo json_encode(array("title" => "Not Found", "message" => "Training Name Not Found", "theme" => "error"));
			} else if(empty($data['category'])){
				echo json_encode(array("title" => "Not Found", "message" => "Category Not Found", "theme" => "error"));
			} else if(empty($data['duration'])){
				echo json_encode(array("title" => "Not Found", "message" => "Duration Not Found", "theme" => "error"));
			} else {
				$dataTemp = $this->ScheduleTrainingModel->insert_data($data, $dataTrainingDates, $dataTrainers);
				if($dataTemp) {
					echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));;
				}
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
            header("Content-Disposition: attachment; filename=schedule_training_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

		$this->db->select("
		    a.*, 
		    a.id as id_training, 
		    GROUP_CONCAT(DISTINCT st.trainer_name SEPARATOR ', ') as trainers, 
		    GROUP_CONCAT(DISTINCT b.training_date SEPARATOR ', ') as training_dates, 
		    COALESCE(e.name, a.category) as trainee_name, 
		    e.id as departementId, 
		    COALESCE(ta.id,rt.id) as trainingActivityId, 
		    COALESCE(ta.trainingActivity,rt.trainingActivities) as trainingActivity
		");
		$this->db->from('lnd_schedule_training a');
		$this->db->join('lnd_schedule_training_dates b', 'a.id = b.training_id', 'left');
		$this->db->join('departements e', 'e.id = a.trainee', 'left');
		$this->db->join('lnd_training_activity ta', 'ta.id = a.trainingName', 'left');
		$this->db->join('lnd_request_training rt', 'rt.id = a.trainingName', 'left');
		$this->db->join('lnd_schedule_trainers st', 'a.id = st.training_id', 'left');
		$this->db->group_by('a.id');
		$this->db->order_by('a.induction', 'ASC');
        $records = $this->db->get()->result_array();

// Helper functions
		function dateToIndex($date) {
			$dt = DateTime::createFromFormat('Y-m-d', $date);
			if (!$dt) return -1;
			$month = (int)$dt->format('n');
			$day = (int)$dt->format('j');
			$week = ceil($day / 8);
			if ($week > 4) $week = 4;
			return ($month - 1) * 4 + ($week - 1);
		}

		function roman($n) {
			$map = ['M'=>1000,'CM'=>900,'D'=>500,'CD'=>400,'C'=>100,'XC'=>90,'L'=>50,'XL'=>40,'X'=>10,'IX'=>9,'V'=>5,'IV'=>4,'I'=>1];
			$result = '';
			foreach ($map as $r => $v) {
				while ($n >= $v) {
					$result .= $r;
					$n -= $v;
				}
			}
			return $result;
		}

// Group by induction
		$groupedRecords = [];
		foreach ($records as $row) {
			$group = $row['induction'] ?? 'Others';
			if (!isset($groupedRecords[$group])) $groupedRecords[$group] = [];
			$groupedRecords[$group][] = [
				'trainingActivity' => $row['trainingActivity'],
				'trainers' => $row['trainers'],
				'trainee_name' => $row['trainee_name'],
				'totalTrainee' => $row['totalTrainee'],
				'duration' => $row['duration'],
				'training_dates' => $row['training_dates'],
//				'training_actuals' => $row['training_dates'],
				'remarks' => $row['remarks'],
			];
		}

// Start HTML
		$months = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
		$html = '<html><head></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
		<center>
            <div style="float: right; font-size: 12px; text-align: right;">
                FRM-L&D-006 Rev.00
            </div>
        </center>
';
		$html .= '<html><head><meta charset="UTF-8"></head><body>';
		$html .= '<table border="1" style="border-collapse: collapse; font-family: Arial; font-size: 12px; width: 100%;">';

		$html .= '<tr>
			<td colspan="56" style="position: relative; text-align: center; font-size: 16px; font-weight: bold;">
				<div style="position: absolute; top: 0; left: 0; font-size: 12px; text-align: left;">
					<img src="' . $config->favicon . '" width="30">
					<b>' . $config->name . '</b><br>
					<small>' . $config->description . '</small>
				</div>
				PT BANSHU ELECTRIC INDONESIA<br>SCHEDULE TRAINING
				<div style="position: absolute; top: 0; right: 0; font-size: 12px; text-align: right;">
            	    Print Date ' . date("d M Y H:m:s") . ' <br>
            	    Print By ' . $this->session->username . '
            	</div>
			</td>
        </tr>
        <tr></tr>
        ';

		$html .= '<tr style="background: #a9d08e; text-align: center; font-weight: bold;">
            <td rowspan="2">No.</td>
            <td rowspan="2">Materi Training</td>
            <td rowspan="2">Trainer</td>
            <td rowspan="2">Peserta</td>
            <td rowspan="2">∑ MP</td>
            <td rowspan="2">Durasi (Menit)</td>
            <td rowspan="2">Remarks</td>
            <td rowspan="2">Plan/Act</td>';
		foreach ($months as $m) {
			$html .= "<td colspan='4'>{$m}</td>";
		}
		$html .= '</tr><tr style="background: #a9d08e; text-align: center;">';
		for ($i = 1; $i <= 12; $i++) {
			for ($j = 1; $j <= 4; $j++) {
				$html .= "<td>M{$j}</td>";
			}
		}
		$html .= '</tr>';

// Content Rows
		$sectionNo = 1;

		foreach ($groupedRecords as $sectionTitle => $records) {
			$html .= '<tr><td colspan="56" style="font-weight: bold; background-color: #9BC2E6; text-align: left;">'
				. roman($sectionNo++) . '. ' . strtoupper($sectionTitle) . '</td></tr>';
			$no = 1;

			foreach ($records as $data) {
				$plan_map = array_fill(0, 48, '');
				foreach (explode(',', $data['training_dates']) as $dt) {
					$idx = dateToIndex(trim($dt));
					if ($idx >= 0) $plan_map[$idx] = date('j-M', strtotime($dt));
				}

				$act_map = array_fill(0, 48, '');
//				foreach (explode(',', $data['training_actuals']) as $dt) {
//					$idx = dateToIndex(trim($dt));
//					if ($idx >= 0) $act_map[$idx] = date('j-M', strtotime($dt));
//				}

				$html .= '<tr>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $no . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['trainingActivity'] . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['trainers'] . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['trainee_name'] . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['totalTrainee'] . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['duration'] . '</td>';
				$html .= '<td rowspan="2" style="text-align: center; vertical-align: middle;">' . $data['remarks'] . '</td>';

				$html .= '<td style="text-align: center;">Plan</td>';
				foreach ($plan_map as $val) {
					$bg = $val !== '' ? 'background-color: #9BC2E6;' : '';
					$html .= '<td style="text-align: center; ' . $bg . '">' . $val . '</td>';
				}

				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td style="text-align: center;">Act</td>';
				foreach ($act_map as $val) {
					$html .= '<td style="text-align: center;">' . $val . '</td>';
				}
				$html .= '</tr>';

				$no++;
			}
		}

		$html .= '</table></body></html>';
		echo $html;
    }

	public function download_template_schedule_training() {

		// Create new spreadsheet
		$spreadsheet = new Spreadsheet();
		$spreadsheet->removeSheetByIndex(0);
		// Sheet 1: Schedule
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle('Schedule Training');
		$sheet
			->setCellValue('A1', 'Template Schedule Training')
			->setCellValue('A2', 'No')
			->setCellValue('B2', 'REGISTER DATE')
			->setCellValue('C2', 'TRAINING DATES 1')
			->setCellValue('D2', 'BATCH TRAINING DATES 1')
			->setCellValue('E2', 'TRAINING DATES 2 (Optional)')
			->setCellValue('F2', 'BATCH TRAINING DATES 2 (Optional)')
			->setCellValue('G2', 'TRAINING DATES 3 (Optional)')
			->setCellValue('H2', 'BATCH TRAINING DATES 3 (Optional)')
			->setCellValue('I2', 'INDUCTION')
			->setCellValue('J2', 'TRAINING NAME (Training Activity ID)')
			->setCellValue('K2', 'CATEGORY')
			->setCellValue('L2', 'TRAINER NAME (Employee ID)')
			->setCellValue('M2', 'TRAINER NAME (Employee ID Optional)')
			->setCellValue('N2', 'TRAINER NAME (Employee ID Optional)')
			->setCellValue('O2', 'TRAINEE (Fill Department ID if Category is Department')
			->setCellValue('P2', 'REMARKS')
			->setCellValue('Q2', 'TOTAL TRAINEE')
			->setCellValue('R2', 'DURATION')
			->setMergeCells(['A1:K1'])
			->getStyle('A1')->getAlignment()->setHorizontal('center')
			->setVertical('center');
		foreach (range('A', 'R') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		// Sheet 2: Trainer
		$this->db->start_cache(); // Cache query sebelum count_all_results
		$this->db->select('a.*');
		$this->db->from('lnd_training_activity a');
		$records = $this->db->get()->result_array();

		$sheet2 = $spreadsheet->createSheet();
		$sheet2->setTitle('Master Training Activity');
		$sheet2->setCellValue('A1', 'Master Training Activity')
			->setCellValue('A2', 'No')
			->setCellValue('B2', 'ID')
			->setCellValue('C2', 'Induction')
			->setCellValue('D2', 'Training Activity')
			->setMergeCells(['A1:D1'])
			->getStyle('A1')->getAlignment()->setHorizontal('center');
		foreach (range('A', 'D') as $col) {
			$sheet2->getColumnDimension($col)->setAutoSize(true);
		}
		$indexTrainingActivity = 3;
		foreach ($records as $row => $data) {
			$sheet2->setCellValue("A" . $indexTrainingActivity, $row+1);
			$sheet2->setCellValue("B" . $indexTrainingActivity, $data['trainingActivityId']);
			$sheet2->setCellValue("C" . $indexTrainingActivity, $data['induction']);
			$sheet2->setCellValue("D" . $indexTrainingActivity, $data['trainingActivity']);
			$indexTrainingActivity++;
		}

		// Sheet 3: Participant
		$sheet3 = $spreadsheet->createSheet();
		$sheet3->setTitle('Trainer Name');
		$sheet3->setCellValue('A1', 'Trainer Name')
			->setCellValue('A2', 'No')
			->setCellValue('B2', 'Employee ID')
			->setCellValue('C2', 'Name')
			->setMergeCells(['A1:C1'])
			->getStyle('A1')->getAlignment()->setHorizontal('center');
		foreach (range('A', 'C') as $col) {
			$sheet3->getColumnDimension($col)->setAutoSize(true);
		}

		// Sheet 4: Material
		$sheet4 = $spreadsheet->createSheet();
		$sheet4->setTitle('Trainee');
		$sheet4->setCellValue('A1', 'Trainee (if Category is Department)')
			->setCellValue('A2', 'No')
			->setCellValue('B2', 'ID')
			->setCellValue('C2', 'Division')
			->setCellValue('D2', 'Department')
			->setMergeCells(['A1:D1'])
			->getStyle('A1')->getAlignment()->setHorizontal('center');
		foreach (range('A', 'D') as $col) {
			$sheet4->getColumnDimension($col)->setAutoSize(true);
		}

		// Set active sheet index back to the first sheet
		$spreadsheet->setActiveSheetIndexByName('Schedule Training');

		// Set filename with today's date
		$filename = "template_schedule_training_" . date("Ymd") . ".xls";

		// Set HTTP headers for Excel file download
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header('Cache-Control: max-age=0');

		// Write and output Excel
		$writer = new Xls($spreadsheet);
		$writer->save('php://output');
		exit;
	}
}
