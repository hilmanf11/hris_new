<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Competence extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load any models or libraries needed
        $this->load->model('CompetenceModel');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('crud');
        // $this->load->library('response'); // Load the response library

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
            $this->load->view('lnd/competence', $data);
        } else {
            redirect('error_session');
        }
    }

    public function datatables()
    {
        // Ambil parameter dari request
        $competence_id = $this->input->get('competenceId', true); // Sanitize input GET
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        // Pagination
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        // Query Builder
        $this->db->start_cache(); // Cache query sebelum count_all_results
        $this->db->select('*');
        $this->db->from('lnd_competence');
        
        if (!empty($competence_id)) {
            $this->db->like('competenceId', $competence_id);
        }
        $this->db->stop_cache(); // Stop caching the query

        // Hitung total data (tanpa limit dan offset)
        $totalRows = $this->db->count_all_results();
        // Ambil data dengan limit dan offset
        $this->db->order_by('index', 'ASC'); // Pindahkan order_by setelah count_all_results

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
        $this->db->from('lnd_competence');
        $this->db->order_by('index', 'ASC');
        $records = $this->db->get()->result_array();
        $logo = base_url('assets/image/logo_bei_2.jpg');

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="'.$logo.'" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>PT BANSHU ELECTRIC INDONESIA</b><br>
                            <small>MASTER COMPETENCE</small>
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

    public function get_detail($id) {
        $data = $this->CompetenceModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'code' => 404,
                    'status' => ResponseStatus::NOT_FOUND,
                    'data' => null,
                    'message' => 'Get Competence data failed'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $data,
                    'message' => 'Get Competence data successfully'
                ]));
        } 
    }

    public function create_data() {
        // Ambil request body secara manual
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);
        // Ubah pengecekan menjadi OR (atau) pada kondisi where
        $competenceCheck = $this->db
            ->where('LOWER(name)', strtolower($data["name"]))
            ->or_where('`index`', $data['index'])
            ->get('lnd_competence')
            ->result_array();
        if(!empty($competenceCheck)) {
            // die(json_encode(array("title" => "Duplicate", "message" => "Your Competence Name has been registered", "theme" => "error")));
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Duplicate, Your Competence Name or index has been registered.'
            ]));
        }
        // Generate competenceId
        $idGenerateDate = $this->crud->autoidPrifix('lnd_competence', 'competenceId', 'C'); 
        $data['id'] = $this->uuid();
        $data['competenceId'] = $idGenerateDate;
        // Validasi dan proses data
        if (!empty($data)) {
            $dataTemp = $this->CompetenceModel->insert_data($data);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'code' => 200,
                    'status' => ResponseStatus::SUCCESS,
                    'data' => $dataTemp,
                    'message' => 'Competence created successfully'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'code' => 400,
                    'status' => ResponseStatus::BAD_REQUEST,
                    'data' => null,
                    'message' => 'Competence creation failed.'
                ]));
        }
    }
    

    public function update_data($id) {
        $rawInput = file_get_contents("php://input");
        parse_str($rawInput, $data);
        // $payloadId   = base64_decode($id);

        if (!empty($data)) {
            $dataTemp = $this->CompetenceModel->update_data($id, $data);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => null,
                'message' => 'Competence updated successfully'
            ]));
        } else {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode([
                'code' => 400,
                'status' => ResponseStatus::BAD_REQUEST,
                'data' => null,
                'message' => 'Competence updated failed.'
            ]));
        }
    }

    public function delete_data($id) {
        $data = $this->CompetenceModel->get_detail_data($id);

        if(empty($data)) {
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'code' => 404,
                'status' => ResponseStatus::NOT_FOUND,
                'data' => null,
                'message' => 'Data not found.'
            ]));
        } else {
            $this->CompetenceModel->delete_data($id);
            return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'code' => 200,
                'status' => ResponseStatus::SUCCESS,
                'data' => $id,
                'message' => 'Competence delete successfully.'
            ]));
        }
    }

    public function list()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $competenceId = $this->input->get('competenceId') ? $this->input->get('competenceId') : "";
        $send = $this->crud->reads('lnd_competence', ["competenceId" => $post, "competenceId" => $competenceId], [], "", "index", "asc");
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

    public function upload()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $this->db->where('name', $data['name']);
            $this->db->or_where('index', $data['index']);
            $existingData = $this->db->get('lnd_competence')->row_array();

            if (!empty($existingData)) {
                echo json_encode(array("title" => "Data Duplicated", "message" => "silakan cek Competence Name => " . (isset($data['name']) ? $data['name'] : json_encode($data)), "theme" => "error"));
                return;
            }

            $idGenerateDate = $this->crud->autoidPrifix('lnd_competence', 'competenceId', 'C'); 
            $data['competenceId'] = $idGenerateDate;
            $tempIndex = $data["index"];

            $queryIndex = $this->db->query("SELECT a.index FROM lnd_competence AS a WHERE a.index = '$tempIndex' ");
            $resIndex = $queryIndex->row_array();

            if (!empty($resIndex)) {
                echo json_encode(array("title" => "Not Found", "message" => "Index  " . $data['index'] . " already exists ", "theme" => "error"));
            } else if(empty($data['name'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Competence Name cannot be Null", "theme" => "error"));            
            } else if(empty($data['index'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Index cannot be Null", "theme" => "error"));
            } else {
                $data['id'] = $this->uuid();
                $send = $this->crud->create('lnd_competence', $data);
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