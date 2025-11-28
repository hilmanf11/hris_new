<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Form_test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('FormTestModel');
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
            $this->load->view('lnd/form-test', $data);
        } else {
            redirect('error_session');
        }
    }

    public function review($id) {
        //  Panggil model untuk mendapatkan data produk
        $data['test_type']="REVIEW";
        $data['title']="Review";
        $data['test_id']=$id;
        $data['data'] = $this->FormTestModel->get_detail_data($id);
        $data['data_feedback'] = $this->FormTestModel->get_detail_feedback($id);

        // Load view dengan data
        $this->load->view('template/header_mobile_lnd');
        $this->load->view('lnd/form-test', $data);
    }

    public function feedback($id) {
        //  Panggil model untuk mendapatkan data produk
        $data['test_type']="REVIEW-FEEDBACK";
        $data['title']="Review Feedback";
        $data['test_id']=$id;
        $data['data'] = $this->FormTestModel->get_detail_feedback($id);
        $data['data_feedback'] = $this->FormTestModel->get_detail_feedback($id);

        // Load view dengan data
        $this->load->view('template/header_mobile_lnd');
        $this->load->view('lnd/form-test', $data);
    }

    public function post_test($slug, $id) {
        //  Panggil model untuk mendapatkan data produk
        $data['test_type']="POST_TEST";
        $data['title']="Post Test";
        $data['test_id']=$id;
        $data['data'] = $this->FormTestModel->get_detail_data($id);
        $data['data_feedback'] = $this->FormTestModel->get_detail_feedback($id);

        // Load view dengan data
        $this->load->view('template/header_mobile_lnd');
        $this->load->view('lnd/form-test', $data);
    }

    public function pre_test($slug,$id) {
        //  Panggil model untuk mendapatkan data produk
        $data['test_type']="PRE_TEST";
        $data['title']="Pre Test";
        $data['test_id']=$id;
        $data['data'] = $this->FormTestModel->get_detail_data($id);
        $data['data_feedback'] = $this->FormTestModel->get_detail_feedback($id);

        // Load view dengan data
        $this->load->view('template/header_mobile_lnd');
        $this->load->view('lnd/form-test', $data);
    }

    public function storeAbsence() {

    }

    public function storeFeedback() {
        
        $this->output->set_content_type('application/json');
        if ($this->input->method() !== 'post') {
            $response = [
                'status' => 'error',
                'message' => 'Metode permintaan tidak diizinkan. Hanya POST yang diterima.'
            ];
            echo json_encode($response);
            return;
        }

        $data_to_insert = [
            'test_id'       => $this->input->post('test_id'),
            'feedback_id'       => $this->input->post('feedback_id'),
            'user_id'           => $this->input->post('user_id'),
            'json_response'     => $this->input->post('json_response'),
            'createdBy'         => $this->input->post('createdBy') ?? ($this->session->username ?? 'system')
        ];

        $insert_success = $this->FormTestModel->insert_feedback($data_to_insert);
        if ($insert_success) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $insert_success,
                    'message' => 'feedback test successfully'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => $this->db->error()['message'],
                    'message' => 'feedback test data failed'
                ]));
        }
    }

    public function insert_data() {
        // Mengatur header respons sebagai JSON
        $this->output->set_content_type('application/json');

        // Pastikan permintaan adalah POST
        if ($this->input->method() !== 'post') {
            $response = [
                'status' => 'error',
                'message' => 'Metode permintaan tidak diizinkan. Hanya POST yang diterima.'
            ];
            echo json_encode($response);
            return;
        }
        // --- Aturan Validasi Form ---
        // Sesuaikan aturan validasi ini sesuai dengan kebutuhan form Anda
        // Nama field di sini harus sesuai dengan nama parameter POST yang dikirim
        $this->form_validation->set_rules('test_id', 'Test ID', 'required|max_length[36]');
        $this->form_validation->set_rules('employee_id', 'Employee ID', 'required|max_length[30]');
        // Perhatikan: 'type_training' di payload Anda adalah 'test_type'. Sesuaikan atau pastikan frontend mengirim 'type_training'.
        // Saya akan menggunakan 'type_training' sesuai skema DB dan menambahkan 'test_type' sebagai fallback atau mapping.
        $this->form_validation->set_rules('type_training', 'Type Training', 'required|max_length[36]');
        $this->form_validation->set_rules('grade', 'Grade', 'numeric|less_than_equal_to[99999999.99]|greater_than_equal_to[0]');
        $this->form_validation->set_rules('trainer', 'Trainer Name', 'required|max_length[255]'); // Batasi panjang untuk POST
        $this->form_validation->set_rules('score_pre_test', 'Pre-Test Score', 'numeric|less_than_equal_to[100]|greater_than_equal_to[0]');
        $this->form_validation->set_rules('score_post_test', 'Post-Test Score', 'numeric|less_than_equal_to[100]|greater_than_equal_to[0]');
        // $this->form_validation->set_rules('json_response_detail', 'JSON Response Detail', 'callback_is_json');
        // $this->form_validation->set_rules('json_result_history', 'JSON Result History', 'callback_is_json');
        // $this->form_validation->set_rules('test_completed_date', 'Test Completed Date', 'valid_datetime'); // Tambahkan jika Anda ingin validasi tanggal

        // Menjalankan validasi
        if ($this->form_validation->run() == FALSE) {
            // Validasi gagal, kirim respons error
            $response = [
                'status' => 'error',
                'message' => 'Validasi data gagal.',
                'errors' => validation_errors() // Mengambil pesan error validasi
            ];
            echo json_encode($response);
            return;
        }

        // --- Menyiapkan Data Gabungan untuk Model ---
        // Ambil nilai langsung dari $this->input->post()
        $data_to_insert = [
            'test_id'               => $this->input->post('test_id'),
            'employee_id'           => $this->input->post('employee_id'),
            // Menggunakan 'test_type' dari payload jika 'type_training' tidak ada
            'type_training'         => $this->input->post('type_training') ?? $this->input->post('test_type'),
            // json_decode string JSON yang diterima dari POST
            'json_response_detail'  => $this->input->post('json_response_detail'),
            'grade'                 => $this->input->post('grade'),
            // test_completed_date dari frontend mungkin dalam format ISO (misal: "2025-05-26T03:55:51.347Z")
            // Kita perlu membersihkannya agar sesuai dengan format DATETIME MySQL (YYYY-MM-DD HH:MM:SS)
            'test_date'             => null,
            'test_completed_date'   => $this->input->post('test_completed_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('test_completed_date'))) : null,
            'trainer'               => $this->input->post('trainer'),
            'json_result_history'   => $this->input->post('json_result_history'),
            'score_pre_test'        => $this->input->post('score_pre_test'),
            'score_post_test'       => $this->input->post('score_post_test'),
            'history_feedback_id'   => $this->input->post('history_feedback_id') ? $this->input->post('history_feedback_id') : null,
            // Ambil createdBy/updatedBy dari POST jika ada, jika tidak, dari session, jika tidak ada juga, default 'system'
            'createdBy'             => $this->input->post('createdBy') ?? ($this->session->username ?? 'system'),
            'updatedBy'             => $this->input->post('updatedBy') ?? ($this->session->username ?? 'system'),
            'trainer_id_arr'        => $this->input->post('trainingTrainerId')
        ];

        if ($this->input->post('test_date')) {
            $date_obj = DateTime::createFromFormat('m/d/Y', $this->input->post('test_date'));
            if ($date_obj) {
                $data_to_insert['test_date'] = $date_obj->format('Y-m-d H:i:s');
            } else {
                // Fallback jika format MM/DD/YYYY tidak cocok, coba strtotime
                $data_to_insert['test_date'] = date('Y-m-d H:i:s', strtotime($this->input->post('test_date')));
            }
        }

        if($data_to_insert['type_training'] === 'PRE_TEST') {
            // Panggil fungsi baru di model untuk memasukkan data ke kedua tabel
            $insert_success = $this->FormTestModel->insert_lnd_data($data_to_insert);
        }else{
            $insert_success = $this->FormTestModel->update_lnd_data($data_to_insert);
        }


        if ($insert_success) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $insert_success,
                    'message' => 'Form test successfully'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(503)
                ->set_output(json_encode([
                    'code' => 503,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => $this->db->error()['message'],
                    'message' => 'Form test data failed'
                ]));
        }

        // Mengirim respons JSON
        // echo json_encode($response);
    }

    public function check_employee_test_status() {
        $this->output->set_content_type('application/json');

        $test_id = $this->input->get('test_id');
        $employee_id = $this->input->get('employee_id');
        $test_type = $this->input->get('test_type');

        // $has_completed = $this->FormTestModel->check_employee_test_status($test_id, $employee_id, $test_type);
        $this->db->select(
            "COALESCE(b.score_post_test, 0) AS post,".
            "COALESCE(b.score_pre_test, 0) AS pre,".
            "COALESCE(d.passing_grade, 0) AS grade,".
            "b.createdTime AS create"
        )->from("lnd_training_history a")
            ->join("lnd_test_form_detail b", "b.test_id = a.id", "left")
            ->join("lnd_master_form_test c", "c.id = a.test_id", "left")
            ->join("lnd_schedule_training d", "d.id = c.training_name", "left")
        ->where("a.test_id", $test_id)->where("a.employee_id", $employee_id)->where("b.type_training", $test_type)->order_by("create", "desc");
        $get = $this->db->get()->result();

        $has_completed = false;
        if (!empty($get)) {
            $has_completed = count($get) > 1;
            if (!$has_completed) {
                $has_completed = (
                    ($test_type == 'POST_TEST') ? floatval($get[0]->post) : floatval($get[0]->pre)
                ) >= floatval($get[0]->grade);
            }
        }

        if ($has_completed) {
            $response = [
                'status' => 'success',
                'completed' => true,
                'message' => 'Karyawan sudah mengisi tes ini.'
            ];
        } else {
            
            if($test_type === 'POST_TEST'){
                $check_has_pre_test = $this->FormTestModel->employe_has_pre_test($test_id, $employee_id);
                if($check_has_pre_test) {
                    $response = [
                        'status' => 'success',
                        'completed' => false,
                        'message' => 'Karyawan belum mengisi tes ini.'
                    ];
                }else{
                    $response = [
                        'status' => 'success',
                        'completed' => true,
                        'message' => 'Karyawan belum mengisi pre-test.'
                    ];
                }

            }else{
                $response = [
                    'status' => 'success',
                    'completed' => false,
                    'message' => 'Karyawan belum mengisi tes ini.'
                ];
            }
        }

        echo json_encode($response);
    }

    public function storeData() {
        // Ambil JSON string dari form multipart
        $json = $this->input->post('data');
        $data = json_decode($json, true);
        

        if (!$data) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Invalid JSON']));
        }

        // Validasi manual (opsional)
        if (empty($data['training_name']) || empty($data['department']) || empty($data['questionType'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['error' => 'Missing required fields']));
        }

        // Simpan ke DB (pass both JSON data & uploadedFiles)
        $save = $this->FormTestModel->insert_lnd_data($data);
        if($save) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $save,
                    'message' => 'Form test successfully'
                ]));
        }else{
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(503)
                ->set_output(json_encode([
                    'code' => 503,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => $data,
                    'message' => 'Form test data failed'
                ]));
        }
    }
}