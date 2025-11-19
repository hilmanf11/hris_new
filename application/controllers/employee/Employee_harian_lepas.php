<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_harian_lepas extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[employees.number]');
    }

    //HALAMAN UTAMA
    public function index($id_menu = "")
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $id_menu;

            $this->load->view('template/header', $data);
            $this->load->view('employee/employee_harian_lepas');
        } else {
            redirect('error_access');
        }
    }

    //DETAIL
    public function details($number, $id_menu)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['employee'] = $this->crud->read('employees', [], ["id" => $number]);
            $data['division'] = $this->crud->read('divisions', [], ["id" => $data['employee']->division_id]);
            $data['departement'] = $this->crud->read('departements', [], ["id" => $data['employee']->departement_id]);
            $data['departement_sub'] = $this->crud->read('departement_subs', [], ["id" => $data['employee']->departement_sub_id]);
            $data['contract'] = $this->crud->read('contracts', [], ["id" => $data['employee']->contract_id]);
            $data['position'] = $this->crud->read('positions', [], ["id" => $data['employee']->position_id]);
            $data['group'] = $this->crud->read('groups', [], ["id" => $data['employee']->group_id]);
            $data['marital'] = $this->crud->read('maritals', [], ["id" => $data['employee']->marital_id]);
            $data['religion'] = $this->crud->read('religions', [], ["id" => $data['employee']->religion_id]);
            $data['resignation'] = $this->crud->read('resignations', [], ["employee_id" => $number]);
            $data['service'] = $this->readService($data['employee']->date_sign);
            $data['source'] = $this->crud->read('sources', [], ["id" => $data['employee']->source_id]);
            $data['id_menu'] = $id_menu;

            $this->load->view('template/header', $data);
            $this->load->view('employee/employee_details_harian_lepas');
        }
    }

    //GET DATA
    public function reads($status = 0)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();
        $aprvDepartement = $this->checkApprovalAccess('employees');

        $session_id = $this->session->id;
        $session_dept = $this->session->departement_id;

        $user = $this->crud->read("users", [], ["id" => $session_id]);
        if ($user->access == "0") {
            $departement_id = "";
        } else {
            $departement_id = $session_dept;
        }

        $this->db->select('*');
        $this->db->from('employees');
        $this->db->where_in('contract_id', [20221119000003, 20250704000001]);
        $this->db->where('group_id', 20221119000002);
        $this->db->where('status', $status);
        if ($get) {
            $this->db->like($get);
        }
        $this->db->like('departement_id', $departement_id);
        $this->db->group_start();
        $this->db->like('number', $post);
        $this->db->or_like('name', $post);
        $this->db->group_end();
        $this->db->order_by('name', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readEmployeeOld()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->distinct()->select(
            "TRIM(a.number) AS number,".
            "TRIM(a.name) AS name,".
            "DATE(a.date_sign) AS sign,".
            "DATE(a.date_expired) AS expired,".
            "TRIM(a.division_id) AS division,".
            "TRIM(a.departement_id) AS dept,".
            "TRIM(a.departement_sub_id) AS sub_dept,".
            "TRIM(a.contract_id) AS contract,".
            "TRIM(a.position_id) AS position,".
            "TRIM(a.group_id) AS group_id,".
            "TRIM(a.source_id) AS source,".
            "TRIM(a.address) AS address,".
            "TRIM(a.place_birth) AS place_birth,".
            "DATE(a.birthday) AS birthday,".
            "TRIM(a.gender) AS gender,".
            "TRIM(a.blood) AS blood,".
            "TRIM(a.religion_id) AS religion_id,".
            "TRIM(a.marital_id) AS marital_id,".
            "TRIM(a.national_id) AS national_id,".
            "TRIM(a.tax_id) AS tax_id,".
            "TRIM(a.jamsostek) AS jamsostek,".
            "DATE(a.jamsostek_date) AS jamsostek_date,".
            "TRIM(a.jkn) AS jkn,".
            "TRIM(a.jkn_date) AS jkn_date,".
            "TRIM(a.jkn_family) AS jkn_family,".
            "TRIM(a.telphone) AS telphone,".
            "TRIM(a.mobile_phone) AS mobile_phone,".
            "TRIM(a.email) AS email,".
            "TRIM(a.bank_name) AS bank_name,".
            "TRIM(a.bank_no) AS bank_no,".
            "TRIM(a.bank_branch) AS bank_branch,".
            "COALESCE(a.status, 0) AS status,".
            "COALESCE(TRIM(b.id), '') AS resign"
        )->from("employees a")->join("resignations b", "a.id = b.employee_id", "left");
        if (!empty($post)) {
            $this->db->group_start()
                ->like("a.number", $post)
                ->or_like("a.name", $post)
            ->group_end();
        }

        $data = [];
        foreach ($this->db->order_by("name")->get()->result() as $key => $row) {
            $push = $row->status == '0';

            if (!$push) { $push = !empty($row->resign); }

            if ($push) { array_push($data, $row); }
        }
        echo json_encode($data);
    }

    public function readGroupFilters()
    {
        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("groups");
        if (!isset($_GET['old'])) { $this->db->where_in("id", ["20221119000002", "20251024000001"]); }
        echo json_encode($this->db->order_by("name")->get()->result());
    }

    public function readFulls()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();
        $aprvDepartement = $this->checkApprovalAccess('employees');

        $this->db->select('a.*, c.name as division_name, d.name as departement_name, e.name as departement_sub_name, f.name as position_name, a.division_id as division_old_id, a.departement_id as departement_old_id, a.departement_sub_id as departement_sub_old_id');
        $this->db->from('employees a');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->join('positions f', 'a.position_id = f.id');
        $this->db->where('a.status', 0);
        $this->db->like('a.departement_id', $aprvDepartement);
        $this->db->group_start();
        $this->db->like('a.name', $post);
        $this->db->or_like('a.number', $post);
        $this->db->group_end();
        $this->db->order_by('a.name', 'asc');
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readFamilys($number)
    {
        $reads = $this->crud->reads("employee_familys", [], ["number" => $number]);
        echo json_encode($reads);
    }

    public function readEducation($number)
    {
        $reads = $this->crud->reads("employee_educations", [], ["number" => $number]);
        echo json_encode($reads);
    }

    public function readExperience($number)
    {
        $reads = $this->crud->reads("employee_experiences", [], ["number" => $number]);
        echo json_encode($reads);
    }

    public function readTraining($number)
    {
        $reads = $this->crud->reads("employee_trainings", [], ["number" => $number]);
        echo json_encode($reads);
    }

    public function readCarrer($number)
    {
        $reads = $this->crud->reads("employee_carrers", [], ["number" => $number]);
        echo json_encode($reads);
    }

    public function readContract($number)
    {
        $reads = $this->db->distinct()->select(
             "MAX(a.created_date) AS max_create,"
            ."COALESCE(TRIM(b.name), '') AS position,"
            ."COALESCE(TRIM(c.name), '') AS contract,"
            ."DATE(a.date_expired) AS date,"
            ."MAX(COALESCE(TRIM(a.attachment), '')) AS attachment"
        )->from("agreements a")
            ->join("positions b", "a.position_id = b.id")
            ->join("contracts c", "a.contract_id = c.id")
        ->where("a.number", $number)->group_by("b.name, c.name, a.date_expired")->order_by("date", "DESC")->get()->result();
        // die($this->db->last_query());
        echo json_encode($reads);
    }

    public function readGroups()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('groups', [], ["id" => "20221119000002"]);
        echo json_encode($send);
    }

    public function readContracts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select('*');
        $this->db->from('contracts');
        $this->db->where_in('id', [20221119000003, 20250704000001]);
        $send = $this->db->get()->result_object();
        
        echo json_encode($send);
    }

    //GET DATE SERVICE
    public function readService($dateSign = "")
    {
        if ($dateSign == "") {
            $date = $this->input->post('date');
        } else {
            $date = $dateSign;
        }

        $start  = date_create($date);
        $end = date_create(); // waktu sekarang
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

    //GET LATEST CUTOFF PERIOD
    private function getLatestCutoffPeriod()
    {
        $this->db->select('*');
        $this->db->from('cutoff');
        $this->db->where('deleted', 0);
        $this->db->order_by('start', 'desc');
        $this->db->limit(1);
        return $this->db->get()->row();
    }


    function formatDate($date)
    {
        // menggunakan class Datetime
        $datetime = DateTime::createFromFormat('d/m/Y', $date);

        if ($datetime) {
            return $datetime->format('Y-m-d');
        } else {
            return $date;
        }
    }

    //GET EMPLOYEE NUMBER
    public function readNumber()
    {
        $departement_id = $this->input->post('departement_id');
        $contract_id = $this->input->post('contract_id');
        $source_id = $this->input->post('source_id');
        $date_sign = base64_decode($this->input->post('date_sign'));

        $departement = $this->crud->read("departements", [], ["id" => $departement_id]);
        $contract = $this->crud->read("contracts", [], ["id" => $contract_id]);
        $source = $this->crud->read("sources", [], ["id" => $source_id]);
        $group = $this->crud->read("groups", [], ["id" => @$source->group_id]);

        $date = date("my", strtotime($date_sign));
        if (@$group->name != "PKL") {
            if (@$group->name == "MAGANG") {
                $numberFormat = $source->number_id . $contract->number . $date;
            } else {
                $numberFormat = $departement->number_id . $contract->number . $date;
            }

            $this->db->select("max(number) as kode");
            $this->db->from("employees");
            $this->db->like("number", $numberFormat);
            $row = $this->db->get()->row();

            $requestcode = (int) @substr($row->kode, -4);
            $requestcode++;

            $autoID = $numberFormat . sprintf("%04s", $requestcode);
        } else {
            $numberFormat = $source->number_id . $date;

            $this->db->select("max(number) as kode");
            $this->db->from("employees");
            $this->db->like("number", $numberFormat);
            $row = $this->db->get()->row();

            $requestcode = (int) @substr($row->kode, -4);
            $requestcode++;

            $autoID = $numberFormat . sprintf("%04s", $requestcode);
        }

        die(json_encode(array("id" => $autoID)));
    }

    //GET DATATABLES
    public function datatables()
    {   
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_positions = $this->input->get('filter_positions');
        $filter_contracts = $this->input->get('filter_contracts');
        $filter_groups = $this->input->get('filter_groups');
        $filter_religions = $this->input->get('filter_religions');
        $filter_maritals = $this->input->get('filter_maritals');
        $filter_services = $this->input->get('filter_services');
        $filter_expired = $this->input->get('filter_expired');
        $filter_shift = $this->input->get('filter_shift');
        $filter_status = $this->input->get('filter_status');
        $filter_resign = $this->input->get('filter_resign');
        $filter_from_join = base64_decode($this->input->get('filter_from_join'));
        $filter_to_join = base64_decode($this->input->get('filter_to_join'));

        $aprvDepartement = $this->checkApprovalAccess('employees');
        $today = date("Y-m-d");

        if ($filter_expired == "0") {
            $whereExpired = $this->db->where("a.date_expired < '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "1") {
            $whereExpired = $this->db->where("a.date_expired = '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "2") {
            $tomorrow =  date("Y-m-d", strtotime("+1 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired = '$tomorrow' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "3") {
            $weeks =  date("Y-m-d", strtotime("+7 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$weeks' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "4") {
            $months =  date("Y-m-d", strtotime("+1 month", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$months' and a.date_expired != '0000-00-00'");
        } else {
            $whereExpired = "";
        }

        if ($filter_services == "1") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) <", 365);
            $whereService2 = "";
        } elseif ($filter_services == "2") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 365);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 730);
        } elseif ($filter_services == "5") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 730);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 1825);
        } elseif ($filter_services == "8") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 1825);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 2944);
        } elseif ($filter_services == "10") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 2944);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 3650);
        } elseif ($filter_services == "11") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 3650);
            $whereService2 = "";
        } else {
            $whereService1 = "";
            $whereService2 = "";
        }

        $page   = $this->input->post('page');
        $rows   = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.*,
                c.name as division_name, 
                d.name as departement_name, 
                e.name as departement_sub_name,
                e.proses,
                e.sub_proses,
                (case when a.job_type is null then e.type else a.job_type end) as type, 
                g.name as position_name,
                h.name as contract_name,
                i.resign_date,
                (CASE WHEN i.resign_date IS NOT NULL THEN LAST_DAY(i.resign_date) ELSE NULL END) as cutoff_date,
                j.name as shift_name');
        $this->db->from('employees a');
        $this->db->join('divisions c', 'c.id = a.division_id');
        $this->db->join('departements d', 'd.id = a.departement_id');
        $this->db->join('departement_subs e', 'e.id = a.departement_sub_id');
        $this->db->join('positions g', 'g.id = a.position_id', 'left');
        $this->db->join('contracts h', 'h.id = a.contract_id', 'left');
        $this->db->join('resignations i', 'a.id = i.employee_id', 'left');
        $this->db->join('shift_employees f', 'a.id = f.employee_id', 'left');
        $this->db->join('shifts j', 'f.shift_id = j.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.contract_id', [20221119000003, 20250704000001]);
        $this->db->where('a.group_id', 20221119000002);
        if($filter_from_join != ""  && $filter_to_join != ""){
            $this->db->where("a.date_sign between '$filter_from_join' and '$filter_to_join'");
        }
        if($filter_resign == "1"){
            $this->db->where("i.resign_date is not null");
        }
        if ($filter_status == "") {
            $this->db->where('a.status', 0);
        } else {
            $this->db->like('a.status', $filter_status);
        }
        $this->db->like('a.departement_id', $aprvDepartement);
        $whereService1;
        $whereService2;
        $whereExpired;
        $this->db->like("a.division_id", $filter_divisions);
        $this->db->like("a.departement_id", $filter_departements);
        $this->db->like("a.departement_sub_id", $filter_departement_subs);
        $this->db->like("a.id", $filter_employees);
        $this->db->like("a.position_id", $filter_positions);
        $this->db->like("a.contract_id", $filter_contracts);
        $this->db->like("a.group_id", $filter_groups);
        $this->db->like("a.religion_id", $filter_religions);
        $this->db->like("a.marital_id", $filter_maritals);
        if($filter_shift != ""){
            $this->db->like("j.id", $filter_shift);
        }
        $this->db->order_by('a.name', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $data  = array();
        foreach ($records as $record) {
            $data[] =  array_merge($record, array("service" => $this->readService($record['date_sign'])));
        }

        $result['total'] = $totalRows;
        $result = @array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $regis_bf = $post['regis_bf'] == 'No';
                $national_id = [];
                $tax_id = [];
                $email = [];
                $mobile_phone = [];
                $bank_no = [];
                if ($regis_bf) {
                    $national_id = $this->crud->read('employees', [], ["national_id" => $post['national_id']]);
                    $tax_id = $this->crud->read('employees', [], ["tax_id" => $post['tax_id']]);
                    if ($post['email'] != '-')
                        { $email = $this->crud->read('employees', [], ["email" => $post['email']]); }
                    if ($post['mobile_phone'] != '0')
                        { $mobile_phone = $this->crud->read('employees', [], ["mobile_phone" => $post['mobile_phone']]); }
                    if ($post['bank_no'] != '-')
                        { $bank_no = $this->crud->read('employees', [], ["bank_no" => $post['bank_no']]); }
                }

                if ($national_id) {
                    echo json_encode(array("title" => "Duplicate", "message" => "National ID Duplicate", "theme" => "error"));
                    // } elseif ($tax_id) {
                    //     echo json_encode(array("title" => "Duplicate", "message" => "Tax ID Duplicate", "theme" => "error"));
                } elseif ($email) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Email Duplicate", "theme" => "error"));
                } elseif ($mobile_phone) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Mobile Phone Duplicate", "theme" => "error"));
                } elseif ($bank_no) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Bank No Duplicate", "theme" => "error"));
                } else {
                    $image_id = $this->crud->upload('image_id', ['png', 'jpg', 'jpeg'], 'assets/image/employee/id/');
                    $image_profile = $this->crud->upload('image_profile', ['png', 'jpg', 'jpeg'], 'assets/image/employee/profile/');
                    unset($post['regis_bf']);
                    $post_final = array_merge($post, ["image_id" => $image_id, "image_profile" => $image_profile]);

                    $employees = $this->crud->create('employees', $post_final);
                    if ($employees) {
                        $postAgreement = array(
                            "number" => $post['number'],
                            "position_id" => $post['position_id'],
                            "contract_id" => $post['contract_id'],
                            "group_id" => $post['group_id'],
                            "date_sign" => $post['date_sign'],
                            "date_expired" => $post['date_expired']
                        );

                        $this->crud->create('agreements', $postAgreement);
                        $this->crud->update('employee_candidates', ["id" => $post['candidate_id']], ["status" => 2]);
                        echo $employees;
                    }
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createFamily()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['id'] == ''){
                $send = $this->crud->create('employee_familys', $post);
                echo $send;
            }else{
                $send = $this->crud->update('employee_familys', ["id" => $post['id']], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createEducation()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['id'] == ''){
                $send = $this->crud->create('employee_educations', $post);
                echo $send;
            }else{
                $send = $this->crud->update('employee_educations', ["id" => $post['id']], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createExperience()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['id'] == ''){
                $send = $this->crud->create('employee_experiences', $post);
                echo $send;
            }else{
                $send = $this->crud->update('employee_experiences', ["id" => $post['id']], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createTraining()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['id'] == ''){
                $send = $this->crud->create('employee_trainings', $post);
                echo $send;
            }else{
                $send = $this->crud->update('employee_trainings', ["id" => $post['id']], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createCarrer()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['id'] == ''){
                $send = $this->crud->create('employee_carrers', $post);
                echo $send;
            }else{
                $send = $this->crud->update('employee_carrers', ["id" => $post['id']], $post);
                echo $send;
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
            unset($post['regis_bf']);
            $image_id = $this->crud->upload('image_id', ['png', 'jpg', 'jpeg'], 'assets/image/employee/id/', ["id" => $id], "employees", "image_id");
            $image_profile = $this->crud->upload('image_profile', ['png', 'jpg', 'jpeg'], 'assets/image/employee/profile/', ["id" => $id], "employees", "image_profile");
            $post_final = array_merge($post, ["image_id" => $image_id, "image_profile" => $image_profile]);

            $send = $this->crud->update('employees', ["id" => $id], $post_final);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employees', $data);
        echo $send;
    }

    public function deleteFamily()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_familys', $data);
        echo $send;
    }

    public function deleteEducation()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_educations', $data);
        echo $send;
    }

    public function deleteExperience()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_experiences', $data);
        echo $send;
    }

    public function deleteTraining()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_trainings', $data);
        echo $send;
    }

    public function deleteCarrer()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('employee_carrers', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            if ($data->val($i, 6) == "") {
                $date_expired = null;
            } else {
                $date_expired = $this->formatDate($data->val($i, 6));
            }

            $datas[] = array(
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'nickname' => $data->val($i, 4),
                'date_sign' => $this->formatDate($data->val($i, 5)),
                'date_expired' => $date_expired,
                'contract_number' => $data->val($i, 7),
                'division_number' => $data->val($i, 8),
                'departement_number' => $data->val($i, 9),
                'departement_sub_number' => $data->val($i, 10),
                'position_number' => $data->val($i, 11),
                'level' => $data->val($i, 12),
                'group_number' => $data->val($i, 13),
                'source_number' => $data->val($i, 14),
                'address' => $data->val($i, 15),
                'place_birth' => $data->val($i, 16),
                'birthday' => $this->formatDate($data->val($i, 17)),
                'gender' => $data->val($i, 18),
                'blood' => $data->val($i, 19),
                'religion_number' => $data->val($i, 20),
                'marital_number' => $data->val($i, 21),
                'national_id' => $data->val($i, 22),
                'tax_id' => $data->val($i, 23),
                'jamsostek' => $data->val($i, 24),
                'jamsostek_date' => $data->val($i, 25),
                'jkn' => $data->val($i, 26),
                'jkn_date' => $data->val($i, 27),
                'telphone' => $data->val($i, 28),
                'mobile_phone' => $data->val($i, 29),
                'emergency_no' => $data->val($i, 30),
                'email' => $data->val($i, 31),
                'maps' => $data->val($i, 32),
                'bank_no' => $data->val($i, 33),
                'bank_name' => $data->val($i, 34),
                'bank_branch' => $data->val($i, 35),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadFamily()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 1);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2, 1),
                'name' => $data->val($i, 3, 1),
                'place' => $data->val($i, 4, 1),
                'birthday' => $this->formatDate($data->val($i, 5, 1)),
                'relation' => $data->val($i, 6, 1),
                'profesion' => $data->val($i, 7, 1),
                'contact' => $data->val($i, 8, 1)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadEducation()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 2);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2, 2),
                'level' => $data->val($i, 3, 2),
                'degree' => $data->val($i, 4, 2),
                'school' => $data->val($i, 5, 2),
                'start' => $this->formatDate($data->val($i, 6, 2)),
                'end' => $this->formatDate($data->val($i, 7, 2)),
                'qpa' => $data->val($i, 8, 2)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadExperience()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 3);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2, 3),
                'name' => $data->val($i, 3, 3),
                'type' => $data->val($i, 4, 3),
                'start' => $this->formatDate($data->val($i, 5, 3)),
                'end' => $this->formatDate($data->val($i, 6, 3)),
                'position' => $data->val($i, 7, 3),
                'salary' => $data->val($i, 8, 3)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadTraining()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 4);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2, 4),
                'name' => $data->val($i, 3, 4),
                'start' => $this->formatDate($data->val($i, 4, 4)),
                'description' => $data->val($i, 5, 4),
                'profesion' => $data->val($i, 6, 4),
                'contact' => $data->val($i, 7, 4)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadCarrer()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_excel']['name']);
        move_uploaded_file($_FILES['file_excel']['tmp_name'], $target);
        chmod($_FILES['file_excel']['name'], 0777);
        $file = $_FILES['file_excel']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 5);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'number' => $data->val($i, 2, 5),
                'name' => $data->val($i, 4, 5),
                'start' => $this->formatDate($data->val($i, 5, 5)),
                'description' => $data->val($i, 6, 5),
                'profesion' => $data->val($i, 7, 5),
                'contact' => $data->val($i, 8, 5)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_excel']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);
            $divisions = $this->crud->read('divisions', ["number" => $data['division_number']]);
            $departements = $this->crud->read('departements', ["number" => $data['departement_number']]);
            $departement_subs = $this->crud->read('departement_subs', ["number" => $data['departement_sub_number'], "division_id" => @$divisions->id, "departement_id" => @$departements->id]);
            $positions = $this->crud->read('positions', ["number" => $data['position_number']]);
            $contracts = $this->crud->read('contracts', ["number" => $data['contract_number']]);
            $groups = $this->crud->read('groups', ["number" => $data['group_number']]);
            $sources = $this->crud->read('sources', ["number" => $data['source_number'], "group_id" => $groups->id]);
            $religions = $this->crud->read('religions', ["number" => $data['religion_number']]);
            $maritals = $this->crud->read('maritals', ["number" => $data['marital_number']]);

            $employeesData = array(
                'division_id' => @$divisions->id,
                'departement_id' => @$departements->id,
                'departement_sub_id' => @$departement_subs->id,
                'contract_id' => @$contracts->id,
                'position_id' => @$positions->id,
                'group_id' => @$groups->id,
                'source_id' => @$sources->id,
                'marital_id' => @$maritals->id,
                'religion_id' => @$religions->id,
                'number' => $data['number'],
                'name' => $data['name'],
                'nickname' => $data['nickname'],
                'date_sign' => $data['date_sign'],
                'date_expired' => $data['date_expired'],
                'address' => $data['address'],
                'place_birth' => $data['place_birth'],
                'birthday' => $data['birthday'],
                'gender' => $data['gender'],
                'blood' => $data['blood'],
                'national_id' => $data['national_id'],
                'tax_id' => $data['tax_id'],
                'jamsostek' => $data['jamsostek'],
                'jamsostek_date' => $data['jamsostek_date'],
                'jkn' => $data['jkn'],
                'jkn_date' => $data['jkn_date'],
                'telphone' => $data['telphone'],
                'mobile_phone' => $data['mobile_phone'],
                'emergency_no' => $data['emergency_no'],
                'email' => $data['email'],
                'maps' => $data['maps'],
                'bank_no' => $data['bank_no'],
                'bank_name' => $data['bank_name'],
                'bank_branch' => $data['bank_branch']
            );

            if (empty($divisions)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Division ID Not Found", "theme" => "error"));
            } elseif (empty($departements)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Departement ID Not Found", "theme" => "error"));
            } elseif (empty($departement_subs)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Departement Sub ID Not Found", "theme" => "error"));
            } elseif (empty($positions)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Position ID Not Found", "theme" => "error"));
            } elseif (empty($contracts)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Contract ID Not Found", "theme" => "error"));
            } elseif (empty($groups)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Group ID Not Found", "theme" => "error"));
            } elseif (empty($religions)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Religion ID Not Found", "theme" => "error"));
            } elseif (empty($maritals)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . " | " . $data['name'] . "</b> Marital Status ID Not Found", "theme" => "error"));
            } elseif (!empty($employees)) {
                $sendEmployees = $this->crud->update('employees', ["number" => $data['number']], $employeesData);
                echo $sendEmployees;
            } else {
                $agreementsData = array(
                    'number' => $data['number'],
                    'contract_id' => $contracts->id,
                    'position_id' => $positions->id,
                    'group_id' => $groups->id,
                    'date_sign' => $data['date_sign'],
                    'date_expired' => $data['date_expired']
                );

                $sendEmployees = $this->crud->create('employees', $employeesData);
                $sendAgreement = $this->crud->create('agreements', $agreementsData);
                echo $sendEmployees;
            }
        }
    }

    public function uploadCreateFamily()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);

            if (empty($employees)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . "</b> Employee ID Cannot Found", "theme" => "error"));
            } else {
                $send = $this->crud->create('employee_familys', $data);
                echo $send;
            }
        }
    }

    public function uploadCreateEducation()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);

            if (empty($employees)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . "</b> Employee ID Cannot Found", "theme" => "error"));
            } else {
                $send = $this->crud->create('employee_educations', $data);
                echo $send;
            }
        }
    }

    public function uploadCreateExperience()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);

            if (empty($employees)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . "</b> Employee ID Cannot Found", "theme" => "error"));
            } else {
                $send = $this->crud->create('employee_experiences', $data);
                echo $send;
            }
        }
    }

    public function uploadCreateTraining()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);

            if (empty($employees)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . "</b> Employee ID Cannot Found", "theme" => "error"));
            } else {
                $send = $this->crud->create('employee_trainings', $data);
                echo $send;
            }
        }
    }

    public function uploadCreateCarrer()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $employees = $this->crud->read('employees', ["number" => $data['number']]);

            if (empty($employees)) {
                echo json_encode(array("title" => "Failed", "message" => "<b>" . $data['number'] . "</b> Employee ID Cannot Found", "theme" => "error"));
            } else {
                $send = $this->crud->create('employee_carrers', $data);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {

        
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=employee_$format.xls");
        }

        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_positions = $this->input->get('filter_positions');
        $filter_contracts = $this->input->get('filter_contracts');
        $filter_groups = $this->input->get('filter_groups');
        $filter_religions = $this->input->get('filter_religions');
        $filter_maritals = $this->input->get('filter_maritals');
        $filter_services = $this->input->get('filter_services');
        $filter_expired = $this->input->get('filter_expired');
        $filter_shift = $this->input->get('filter_shift');
        $filter_status = $this->input->get('filter_status');
        $filter_resign = $this->input->get('filter_resign');
        $filter_from_join = base64_decode($this->input->get('filter_from_join'));
        $filter_to_join = base64_decode($this->input->get('filter_to_join'));

        $aprvDepartement = $this->checkApprovalAccess('employees');

        $today = date("Y-m-d");
        if ($filter_expired == "0") {
            $whereExpired = $this->db->where("a.date_expired < '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "1") {
            $whereExpired = $this->db->where("a.date_expired = '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "2") {
            $tomorrow =  date("Y-m-d", strtotime("+1 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired = '$tomorrow' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "3") {
            $weeks =  date("Y-m-d", strtotime("+7 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$weeks' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "4") {
            $months =  date("Y-m-d", strtotime("+1 month", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$months' and a.date_expired != '0000-00-00'");
        } else {
            $whereExpired = "";
        }

        if ($filter_services == "1") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) <", 365);
            $whereService2 = "";
        } elseif ($filter_services == "2") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 365);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 730);
        } elseif ($filter_services == "5") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 730);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 1825);
        } elseif ($filter_services == "8") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 1825);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 2944);
        } elseif ($filter_services == "10") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 2944);
            $whereService2 = $this->db->where("datediff(current_date(), a.date_sign) <", 3650);
        } elseif ($filter_services == "11") {
            $whereService1 = $this->db->where("datediff(current_date(), a.date_sign) >", 3650);
            $whereService2 = "";
        } else {
            $whereService1 = "";
            $whereService2 = "";
        }

        $this->db->select('a.*, 
                c.number as division_number, 
                c.name as division_name, 
                d.number as departement_number, 
                d.name as departement_name, 
                e.number as departement_sub_number, 
                e.name as departement_sub_name,
                (case when a.job_type is null then e.type else a.job_type end) as type,
                g.level,
                g.number as position_number,
                g.name as position_name,
                h.number as contract_number,
                h.name as contract_name,
                i.number as group_number,
                i.name as group_name,
                j.number as religion_number,
                j.name as religion_name,
                k.number as marital_number,
                k.name as marital_name,
                l.number as source_number,
                l.name as source_name,
                n.name as shift_name');
        $this->db->from('employees a');
        $this->db->join('divisions c', 'c.id = a.division_id');
        $this->db->join('departements d', 'd.id = a.departement_id');
        $this->db->join('departement_subs e', 'e.id = a.departement_sub_id');
        $this->db->join('positions g', 'g.id = a.position_id', 'left');
        $this->db->join('contracts h', 'h.id = a.contract_id', 'left');
        $this->db->join('groups i', 'i.id = a.group_id', 'left');
        $this->db->join('religions j', 'j.id = a.religion_id', 'left');
        $this->db->join('maritals k', 'k.id = a.marital_id', 'left');
        $this->db->join('sources l', 'l.id = a.source_id', 'left');
        $this->db->join('resignations m', 'a.id = m.employee_id', 'left');
        $this->db->join('shift_employees f', 'a.id = f.employee_id', 'left');
        $this->db->join('shifts n', 'f.shift_id = n.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.contract_id', [20221119000003, 20250704000001]);
        $this->db->where('a.group_id', 20221119000002);
        if($filter_from_join != ""  && $filter_to_join != ""){
            $this->db->where("a.date_sign between '$filter_from_join' and '$filter_to_join'");
        }
        if($filter_resign == "1"){
            $this->db->where("m.resign_date is not null");
        }
        $this->db->like('a.status', $filter_status);
        $this->db->like('a.departement_id', $aprvDepartement);
        $whereService1;
        $whereService2;
        $whereExpired;
        $this->db->like("a.division_id", $filter_divisions);
        $this->db->like("a.departement_id", $filter_departements);
        $this->db->like("a.departement_sub_id", $filter_departement_subs);
        $this->db->like("a.id", $filter_employees);
        $this->db->like("a.position_id", $filter_positions);
        $this->db->like("a.contract_id", $filter_contracts);
        $this->db->like("a.group_id", $filter_groups);
        $this->db->like("a.religion_id", $filter_religions);
        $this->db->like("a.marital_id", $filter_maritals);
        if($filter_shift != ""){
            $this->db->like("n.id", $filter_shift);
        }
        $this->db->order_by('a.name', 'ASC');
        $this->db->group_by('a.number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <style> .str{ mso-number-format:\@; } </style>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                Human Resource Information System
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center><br>
        <center>
            <h3>Data Employee</h3>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Employee ID</th>
                <th>Fullname</th>
                <th>Prefered Name</th>
                <th>ID Division</th>
                <th>Division</th>
                <th>ID Departement</th>
                <th>Departement</th>
                <th>ID Departement Sub</th>
                <th>Departement Sub</th>
                <th>Job Type</th>
                <th>ID Position</th>
                <th>Position</th>
                <th>Level</th>
                <th>ID Employee Type</th>
                <th>Employee Type</th>
                <th>ID Group</th>
                <th>Group</th>
                <th>ID Source</th>
                <th>Source</th>
                <th>Shift Type</th>
                <th>Join Date</th>
                <th>Contract Expired</th>
                <th>Fit Of Service</th>
                <th>Address</th>
                <th>Place Birth</th>
                <th>Birthday</th>
                <th>Gender</th>
                <th>Blood</th>
                <th>Religion</th>
                <th>Marital</th>
                <th>National ID</th>
                <th>Tax ID</th>
                <th>Jamsostek No</th>
                <th>Jamsostek Date</th>
                <th>JKN No</th>
                <th>JKN Date</th>
                <th>Telphone</th>
                <th>Mobile Phone</th>
                <th>Emergency No</th>
                <th>Email</th>
                <th>Driving Licensed No</th>
                <th>Driving Licensed Date</th>
                <th>STNK No</th>
                <th>STNK Date</th>
                <th>Bank Name</th>
                <th>Bank Account</th>
                <th>Image ID</th>
                <th>Image Profile</th>
                <th>Status</th>
                <th>Status Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == "1"){
                $style = "style='background:#FFDCDC;'";
            }else{
                $style = "";
            }

            $html .= '<tr '.$style.'>
                    <td>' . $no . '</td>
                    <td class="str">' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['nickname'] . '</td>
                    <td><b style="color:red;">' . $data['division_number'] . '</b></td>
                    <td>' . $data['division_name'] . '</td>
                    <td><b style="color:red;">' . $data['departement_number'] . '</b></td>
                    <td>' . $data['departement_name'] . '</td>
                    <td><b style="color:red;">' . $data['departement_sub_number'] . '</b></td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td><b style="color:red;">' . $data['position_number'] . '</b></td>
                    <td>' . $data['position_name'] . '</td>
                    <td>' . $data['level'] . '</td>
                    <td><b style="color:red;">' . $data['contract_number'] . '</b></td>
                    <td>' . $data['contract_name'] . '</td>
                    <td><b style="color:red;">' . $data['group_number'] . '</b></td>
                    <td>' . $data['group_name'] . '</td>
                    <td><b style="color:red;">' . $data['source_number'] . '</b></td>
                    <td>' . $data['source_name'] . '</td>
                    <td>' . $data['shift_name'] . '</td>
                    <td>' . $data['date_sign'] . '</td>
                    <td>' . $data['date_expired'] . '</td>
                    <td>' . $this->readService($data['date_sign']) . '</td>
                    <td>' . $data['address'] . '</td>
                    <td>' . $data['place_birth'] . '</td>
                    <td>' . $data['birthday'] . '</td>
                    <td>' . $data['gender'] . '</td>
                    <td>' . $data['blood'] . '</td>
                    <td>' . $data['religion_name'] . '</td>
                    <td>' . $data['marital_name'] . '</td>
                    <td class="str">' . $data['national_id'] . '</td>
                    <td class="str">' . $data['tax_id'] . '</td>
                    <td class="str">' . $data['jamsostek'] . '</td>
                    <td>' . $data['jamsostek_date'] . '</td>
                    <td class="str">' . $data['jkn'] . '</td>
                    <td>' . $data['jkn_date'] . '</td>
                    <td>' . $data['telphone'] . '</td>
                    <td class="str">' . $data['mobile_phone'] . '</td>
                    <td>' . $data['emergency_no'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['driving_no'] . '</td>
                    <td>' . $data['driving_date'] . '</td>
                    <td>' . $data['stnk_no'] . '</td>
                    <td>' . $data['stnk_date'] . '</td>
                    <td>' . $data['bank_name'] . '</td>
                    <td class="str">' . $data['bank_no'] . '</td>
                    <td>' . $data['image_id'] . '</td>
                    <td>' . $data['image_profile'] . '</td>
                    <td>' . $data['status'] . '</td>
                    <td>' . $data['status_date'] . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_employee_detail($number)
    {
        $number = base64_decode($number);
        // Data utama karyawan
        $employee = $this->crud->read('employees', [], ["number" => $number]);
        $division = $this->crud->read('divisions', [], ["id" => $employee->division_id]);
        $departement = $this->crud->read('departements', [], ["id" => $employee->departement_id]);
        $departement_sub = $this->crud->read('departement_subs', [], ["id" => $employee->departement_sub_id]);
        $contract = $this->crud->read('contracts', [], ["id" => $employee->contract_id]);
        $position = $this->crud->read('positions', [], ["id" => $employee->position_id]);
        $group = $this->crud->read('groups', [], ["id" => $employee->group_id]);
        $source = $this->crud->read('sources', [], ["id" => $employee->source_id]);
        $religion = $this->crud->read('religions', [], ["id" => $employee->religion_id]);
        $marital = $this->crud->read('maritals', [], ["id" => $employee->marital_id]);

        // Data tab
        $family = $this->crud->reads('employee_familys', [], ["number" => $employee->number]);
        $education = $this->crud->reads('employee_educations', [], ["number" => $employee->number]);
        $experience = $this->crud->reads('employee_experiences', [], ["number" => $employee->number]);
        $training = $this->crud->reads('employee_trainings', [], ["number" => $employee->number]);
        $career = $this->crud->reads('employee_carrers', [], ["number" => $employee->number]);

        // Data warning letter dan mutations
        $this->db->select('a.*, c.name as violation_name');
        $this->db->from('warning_letters a');
        $this->db->join('violations c', 'a.violation_id = c.id', 'left');
        $this->db->where('a.employee_id', $employee->id);
        $this->db->where('a.deleted', 0);
        // Filter warning letter hanya 6 bulan terakhir
        $six_months_ago = date('Y-m-d', strtotime('-6 months'));
        $this->db->where('a.issue_date >=', $six_months_ago);
        $this->db->order_by('a.issue_date', 'DESC');
        $warning = $this->db->get()->result();
        
        // Ambil data mutasi dan urutkan dari yang terbaru
        $this->db->select('a.*, b.name as division_to, c.name as departement_to, d.name as departement_sub_to, a.description, a.division_old_id, a.departement_old_id, a.departement_sub_old_id');
        $this->db->from('mutations a');
        $this->db->join('divisions b', 'a.division_id = b.id');
        $this->db->join('departements c', 'a.departement_id = c.id');
        $this->db->join('departement_subs d', 'a.departement_sub_id = d.id');
        $this->db->where('a.employee_id', $employee->id);
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.trans_date', 'DESC');
        $mutations = $this->db->get()->result_array();

        // Gabungkan data: mutasi + posisi awal
        $result = [];
        
        // Tambahkan semua mutasi (dari terbaru ke lama)
        $result = array_merge($result, $mutations);
        
        // Cari mutasi PERMANENT yang pertama kali dibuat untuk mendapatkan posisi awal
        $permanent_mutation = null;
        // Urutkan mutasi dari yang paling lama ke terbaru untuk mencari yang pertama
        $sorted_mutations = $mutations;
        usort($sorted_mutations, function($a, $b) {
            return strtotime($a['trans_date']) - strtotime($b['trans_date']);
        });
        
        foreach ($sorted_mutations as $mutation) {
            if ($mutation['type'] == 'PERMANENT' && !empty($mutation['division_old_id'])) {
                $permanent_mutation = $mutation;
                break; // Ambil yang pertama kali dibuat
            }
        }
        
        // Jika ada mutasi PERMANENT, ambil posisi awal dari old_id
        if ($permanent_mutation) {
            // Ambil nama division lama
            $division_old = $this->crud->read('divisions', [], ["id" => $permanent_mutation['division_old_id']]);
            $departement_old = $this->crud->read('departements', [], ["id" => $permanent_mutation['departement_old_id']]);
            $departement_sub_old = $this->crud->read('departement_subs', [], ["id" => $permanent_mutation['departement_sub_old_id']]);
            
            $result[] = [
                'id' => 'initial',
                'employee_id' => $employee->id,
                'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                'mutation_type' => 'INITIAL POSITION',
                'description' => 'Initial Position',
                'type' => 'PERMANENT',
                'division_to' => $division_old ? $division_old->name : '-',
                'departement_to' => $departement_old ? $departement_old->name : '-',
                'departement_sub_to' => $departement_sub_old ? $departement_sub_old->name : '-'
            ];
        } else {
            // Jika tidak ada mutasi PERMANENT, ambil dari tabel employees
            $this->db->select('b.*, c.name as division_to, d.name as departement_to, e.name as departement_sub_to');
            $this->db->from('employees b');
            $this->db->join('divisions c', 'b.division_id = c.id');
            $this->db->join('departements d', 'b.departement_id = d.id');
            $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
            $this->db->where('b.id', $employee->id);
            $initial_position = $this->db->get()->row_array();

            if ($initial_position) {
                $result[] = array_merge($initial_position, [
                    'id' => 'initial',
                    'employee_id' => $employee->id,
                    'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                    'mutation_type' => 'INITIAL POSITION',
                    'description' => 'Initial Position',
                    'type' => 'PERMANENT'
                ]);
            }
        }

        // Urutkan berdasarkan trans_date dari terbaru ke lama
        usort($result, function($a, $b) {
            return strtotime($b['trans_date']) - strtotime($a['trans_date']);
        });

        $mutations = $result;

        // Hitung service
        $service = $this->readService($employee->date_sign);

        $data['employee'] = $employee;
        $data['division'] = $division;
        $data['departement'] = $departement;
        $data['departement_sub'] = $departement_sub;
        $data['contract'] = $contract;
        $data['position'] = $position;
        $data['group'] = $group;
        $data['source'] = $source;
        $data['religion'] = $religion;
        $data['marital'] = $marital;
        $data['family'] = $family;
        $data['education'] = $education;
        $data['experience'] = $experience;
        $data['training'] = $training;
        $data['career'] = $career;
        $data['warning'] = $warning;
        $data['mutations'] = $mutations;
        $data['service'] = $service;

        $this->load->view('employee/employee_print_detail', $data);
    }
}
