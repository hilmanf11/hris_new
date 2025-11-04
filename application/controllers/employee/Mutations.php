<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Mutations extends CI_Controller

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

        $this->form_validation->set_rules('employee_id', 'Employee ID', 'required|min_length[1]|max_length[30]');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());



            $this->load->view('template/header', $data);

            $this->load->view('employee/mutations');

        } else {

            redirect('error_access');

        }

    }



    //GET DATA

    public function reads()

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->reads('mutations', ["number" => $post]);

        echo json_encode($send);

    }

    public function readPositions()
    {
        $this->db->distinct()->select(
             "TRIM(id) AS id,"
            ."TRIM(name) AS name,"
            ."TRIM(level) AS level"
        )->from('positions')->order_by('name');
        echo json_encode($this->db->get()->result());
    }

    //GET DATATABLES

    public function datatables()

    {

        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $filter_divisions = $this->input->get('filter_divisions');

        $filter_departements = $this->input->get('filter_departements');

        $filter_departement_subs = $this->input->get('filter_departement_subs');

        $filter_employees = $this->input->get('filter_employees');

        $filter_approval = $this->input->get('filter_approval');

        $filter_status = $this->input->get('filter_status');

        $aprvDepartement = $this->checkApprovalAccess('mutations');



        $page = $this->input->post('page');

        $rows = $this->input->post('rows');

        //Pagination 1-10

        $page   = isset($page) ? intval($page) : 1;

        $rows   = isset($rows) ? intval($rows) : 10;

        $offset = ($page - 1) * $rows;

        $result = array();


        // Build subquery for latest mutations with filters
        $subquery = "SELECT MAX(id) as max_id FROM mutations 
                    WHERE deleted = 0 
                    AND trans_date >= ? 
                    AND trans_date <= ?";
        
        $subParams = array($filter_from, $filter_to);
        
        // Apply filters to subquery
        if (!empty($aprvDepartement)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_id LIKE ?)";
            $subParams[] = '%' . $aprvDepartement . '%';
        }
        
        if (!empty($filter_divisions)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE division_id LIKE ?)";
            $subParams[] = '%' . $filter_divisions . '%';
        }
        
        if (!empty($filter_departements)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_id LIKE ?)";
            $subParams[] = '%' . $filter_departements . '%';
        }
        
        if (!empty($filter_departement_subs)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_sub_id LIKE ?)";
            $subParams[] = '%' . $filter_departement_subs . '%';
        }
        
        if (!empty($filter_employees)) {
            $subquery .= " AND employee_id LIKE ?";
            $subParams[] = '%' . $filter_employees . '%';
        }
        
        if (!empty($filter_status)) {
            $subquery .= " AND status LIKE ?";
            $subParams[] = '%' . $filter_status . '%';
        }
        
        // Apply approval filter to subquery
        if ($filter_approval == "0") {
            $subquery .= " AND (approved_to = '' OR approved_to IS NULL)";
        } elseif ($filter_approval == "1") {
            $subquery .= " AND (approved_to != '' AND approved_to IS NOT NULL)";
        }
        
        $subquery .= " GROUP BY employee_id";
        
        // Main query
        $sql = "SELECT a.*, b.number as employee_number, b.name as employee_name, 
                c.name as division_name, d.name as departement_name, e.name as departement_sub_name,
                e.proses, e.sub_proses, COALESCE(TRIM(f.name), '') AS position
                FROM mutations a
                JOIN employees b ON a.employee_id = b.id
                JOIN divisions c ON a.division_id = c.id
                JOIN departements d ON a.departement_id = d.id
                JOIN departement_subs e ON a.departement_sub_id = e.id
                LEFT JOIN positions f ON b.position_id = f.id
                WHERE a.deleted = 0 
                AND a.trans_date >= ?
                AND a.trans_date <= ?";
        
        $params = array($filter_from, $filter_to);
        
        // Apply additional filters to main query
        if (!empty($aprvDepartement)) {
            $sql .= " AND b.departement_id LIKE ?";
            $params[] = '%' . $aprvDepartement . '%';
        }
        
        if (!empty($filter_divisions)) {
            $sql .= " AND b.division_id LIKE ?";
            $params[] = '%' . $filter_divisions . '%';
        }
        
        if (!empty($filter_departements)) {
            $sql .= " AND b.departement_id LIKE ?";
            $params[] = '%' . $filter_departements . '%';
        }
        
        if (!empty($filter_departement_subs)) {
            $sql .= " AND b.departement_sub_id LIKE ?";
            $params[] = '%' . $filter_departement_subs . '%';
        }
        
        if (!empty($filter_employees)) {
            $sql .= " AND b.id LIKE ?";
            $params[] = '%' . $filter_employees . '%';
        }
        
        if (!empty($filter_status)) {
            $sql .= " AND a.status LIKE ?";
            $params[] = '%' . $filter_status . '%';
        }
        
        // Apply approval filter to main query
        if ($filter_approval == "0") {
            $sql .= " AND (a.approved_to = '' OR a.approved_to IS NULL)";
        } elseif ($filter_approval == "1") {
            $sql .= " AND (a.approved_to != '' AND a.approved_to IS NOT NULL)";
        }
        
        // Join with subquery to get only latest mutations
        $sql .= " AND a.id IN (" . $subquery . ") ORDER BY b.name ASC";
        
        // Merge parameters
        $params = array_merge($params, $subParams);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as subquery";
        $totalRows = $this->db->query($countSql, $params)->row()->total;
        
        // Get data with limit
        $sql .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $rows;
        
        $records = $this->db->query($sql, $params)->result_array();



        //Mapping Data

        $result['total'] = $totalRows;

        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);

    }



    //CREATE DATA

    public function create()

    {

        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post = $this->input->post();

                $departement_id = $this->session->departement_id;

                $approval = $this->crud->read('approvals', [], ["table_name" => "mutations", "departement_id" => $departement_id]);

                // Tambahkan data ID lama ke post data
                $post['old_position'] = $this->input->post('position_old');
                $post['new_position'] = $this->input->post('position_new');
                $post['division_old_id'] = $this->input->post('division_old_id');
                $post['departement_old_id'] = $this->input->post('departement_old_id');
                $post['departement_sub_old_id'] = $this->input->post('departement_sub_old_id');

                unset($post['position_old']);
                unset($post['position_new']);

                if ($approval) {

                    $send   = $this->crud->create('mutations', $post);

                } else {

                    $postEmployee = array(

                        "position_id" => $post['position'],

                        "division_id" => $post['division_id'],

                        "departement_id" => $post['departement_id'],

                        "departement_sub_id" => $post['departement_sub_id']

                    );

                    unset($post['position']);

                    if ($post['type'] == "PERMANENT") {

                        $this->crud->update('employees', ["id" => $post['employee_id']], $postEmployee);

                    }


                    // die(var_dump($post));
                    $send   = $this->crud->create('mutations', $post);

                }

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



            // Tambahkan data ID lama ke post data

            $post['division_old_id'] = $this->input->post('division_old_id');

            $post['departement_old_id'] = $this->input->post('departement_old_id');

            $post['departement_sub_old_id'] = $this->input->post('departement_sub_old_id');



            $send = $this->crud->update('mutations', ['id' => $id], $post);



            $postEmployee = array(

                "division_id" => $post['division_id'],

                "departement_id" => $post['departement_id'],

                "departement_sub_id" => $post['departement_sub_id']

            );



            if ($post['type'] == "PERMANENT") {

                $this->crud->update('employees', ["id" => $post['employee_id']], $postEmployee);

            }



            echo $send;

        } else {

            show_error("Cannot Process your request");

        }

    }



    //DELETE DATA

    public function delete()

    {

        $data = $this->input->post();

        $send = $this->crud->delete('mutations', $data);

        echo $send;

    }



    //UPLOAD DATA

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

                'departement_sub_number' => $data->val($i, 3),

                'trans_date' => $data->val($i, 4),

                'type' => $data->val($i, 5),

                'description' => $data->val($i, 6),

            );

        }



        $datas['total'] = count($datas);

        echo json_encode($datas);



        unlink($_FILES['file_upload']['name']);

    }



    public function uploadclearFailed()

    {

        @unlink('failed/mutations.txt');

    }



    public function uploadcreateFailed()

    {

        if ($this->input->post()) {

            $message = $this->input->post('message');

            $textFailed = fopen('failed/mutations.txt', 'a');

            fwrite($textFailed, $message . "\n");

            fclose($textFailed);

        }

    }



    public function uploadDownloadFailed()

    {

        $file = "failed/mutations.txt";



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

            //Cek Process Number

            $employee = $this->crud->read('employees', [], ["number" => $data['number']]);

            $departement_sub = $this->crud->read('departement_subs', [], ["number" => $data['departement_sub_number']]);



            if (empty($employee)) {

                echo json_encode(array("title" => "Not Found", "message" => "Employee ID " . $data['number'] . " Not Found", "theme" => "error"));

            } elseif (empty($departement_sub)) {

                echo json_encode(array("title" => "Not Found", "message" => "Departement Sub ID " . $data['departement_sub_number'] . " Not Found", "theme" => "error"));

            } else {

                $post = array(

                    "division_id" => $departement_sub->division_id,

                    "departement_id" => $departement_sub->departement_id,

                    "departement_sub_id" => $departement_sub->id,

                    "employee_id" => $employee->id,

                    "trans_date" => $data['trans_date'],

                    "type" => $data['type'],

                    "description" => $data['description'],

                );



                $postEmployee = array(

                    "division_id" => $departement_sub->division_id,

                    "departement_id" => $departement_sub->departement_id,

                    "departement_sub_id" => $departement_sub->id

                );



                if ($data['type'] == "PERMANENT") {

                    $this->crud->update('employees', ["id" => $employee->id], $postEmployee);

                }



                $send   = $this->crud->create('mutations', $post);

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

            header("Content-Disposition: attachment; filename=mutations_$format.xls");

        }



        //Filter Data

        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $filter_divisions = $this->input->get('filter_divisions');

        $filter_departements = $this->input->get('filter_departements');

        $filter_departement_subs = $this->input->get('filter_departement_subs');

        $filter_employees = $this->input->get('filter_employees');

        $filter_approval = $this->input->get('filter_approval');

        $filter_status = $this->input->get('filter_status');

        $aprvDepartement = $this->checkApprovalAccess('mutations');



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        // Build subquery for latest mutations with filters
        $subquery = "SELECT MAX(id) as max_id FROM mutations 
                    WHERE deleted = 0 
                    AND trans_date >= ? 
                    AND trans_date <= ?";
        
        $subParams = array($filter_from, $filter_to);
        
        // Apply filters to subquery
        if (!empty($aprvDepartement)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_id LIKE ?)";
            $subParams[] = '%' . $aprvDepartement . '%';
        }
        
        if (!empty($filter_divisions)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE division_id LIKE ?)";
            $subParams[] = '%' . $filter_divisions . '%';
        }
        
        if (!empty($filter_departements)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_id LIKE ?)";
            $subParams[] = '%' . $filter_departements . '%';
        }
        
        if (!empty($filter_departement_subs)) {
            $subquery .= " AND employee_id IN (SELECT id FROM employees WHERE departement_sub_id LIKE ?)";
            $subParams[] = '%' . $filter_departement_subs . '%';
        }
        
        if (!empty($filter_employees)) {
            $subquery .= " AND employee_id LIKE ?";
            $subParams[] = '%' . $filter_employees . '%';
        }
        
        if (!empty($filter_status)) {
            $subquery .= " AND status LIKE ?";
            $subParams[] = '%' . $filter_status . '%';
        }
        
        // Apply approval filter to subquery
        if ($filter_approval == "0") {
            $subquery .= " AND (approved_to = '' OR approved_to IS NULL)";
        } elseif ($filter_approval == "1") {
            $subquery .= " AND (approved_to != '' AND approved_to IS NOT NULL)";
        }
        
        $subquery .= " GROUP BY employee_id";
        
        // Main query
        $sql = "SELECT a.*, b.number as employee_number, b.name as employee_name, 
                c.name as division_name, d.name as departement_name, e.name as departement_sub_name
                FROM mutations a
                JOIN employees b ON a.employee_id = b.id
                JOIN divisions c ON a.division_id = c.id
                JOIN departements d ON a.departement_id = d.id
                JOIN departement_subs e ON a.departement_sub_id = e.id
                WHERE a.deleted = 0 
                AND a.trans_date >= ?
                AND a.trans_date <= ?";
        
        $params = array($filter_from, $filter_to);
        
        // Apply additional filters to main query
        if (!empty($aprvDepartement)) {
            $sql .= " AND b.departement_id LIKE ?";
            $params[] = '%' . $aprvDepartement . '%';
        }
        
        if (!empty($filter_divisions)) {
            $sql .= " AND b.division_id LIKE ?";
            $params[] = '%' . $filter_divisions . '%';
        }
        
        if (!empty($filter_departements)) {
            $sql .= " AND b.departement_id LIKE ?";
            $params[] = '%' . $filter_departements . '%';
        }
        
        if (!empty($filter_departement_subs)) {
            $sql .= " AND b.departement_sub_id LIKE ?";
            $params[] = '%' . $filter_departement_subs . '%';
        }
        
        if (!empty($filter_employees)) {
            $sql .= " AND b.id LIKE ?";
            $params[] = '%' . $filter_employees . '%';
        }
        
        if (!empty($filter_status)) {
            $sql .= " AND a.status LIKE ?";
            $params[] = '%' . $filter_status . '%';
        }
        
        // Apply approval filter to main query
        if ($filter_approval == "0") {
            $sql .= " AND (a.approved_to = '' OR a.approved_to IS NULL)";
        } elseif ($filter_approval == "1") {
            $sql .= " AND (a.approved_to != '' AND a.approved_to IS NOT NULL)";
        }
        
        // Join with subquery to get only latest mutations
        $sql .= " AND a.id IN (" . $subquery . ") ORDER BY b.name ASC";
        
        // Merge parameters
        $params = array_merge($params, $subParams);
        
        $records = $this->db->query($sql, $params)->result_array();

        // Get complete history for each employee
        $all_records = [];
        foreach ($records as $data) {
            // Add the current mutation
            $all_records[] = $data;
            
            // Get complete history for this employee
            $employee_id = $data['employee_id'];
            
            // Get all mutations for this employee (excluding current one to avoid duplication)
            $this->db->select('a.*, b.number as employee_number, b.name as employee_name, a.mutation_type, c.name as division_name, d.name as departement_name, e.name as departement_sub_name');
            $this->db->from('mutations a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('divisions c', 'a.division_id = c.id');
            $this->db->join('departements d', 'a.departement_id = d.id');
            $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
            $this->db->where('a.employee_id', $employee_id);
            $this->db->where('a.deleted', 0);
            $this->db->where('a.id !=', $data['id']); // Exclude current mutation
            $this->db->order_by('a.trans_date', 'DESC');
            $history_mutations = $this->db->get()->result_array();
            
            // Add history mutations
            foreach ($history_mutations as $history) {
                $all_records[] = $history;
            }
            
            // Add initial position if exists
            $employee = $this->crud->read('employees', [], ["id" => $employee_id]);
            
            // Check if there's a PERMANENT mutation with old_id data
            $permanent_mutation = null;
            $sorted_mutations = $history_mutations;
            usort($sorted_mutations, function($a, $b) {
                return strtotime($a['trans_date']) - strtotime($b['trans_date']);
            });
            
            foreach ($sorted_mutations as $mutation) {
                if ($mutation['type'] == 'PERMANENT' && !empty($mutation['division_old_id'])) {
                    $permanent_mutation = $mutation;
                    break;
                }
            }
            
            if ($permanent_mutation) {
                // Get initial position from old_id
                $division_old = $this->crud->read('divisions', [], ["id" => $permanent_mutation['division_old_id']]);
                $departement_old = $this->crud->read('departements', [], ["id" => $permanent_mutation['departement_old_id']]);
                $departement_sub_old = $this->crud->read('departement_subs', [], ["id" => $permanent_mutation['departement_sub_old_id']]);
                
                $all_records[] = [
                    'id' => 'initial',
                    'employee_id' => $employee_id,
                    'employee_number' => $employee->number,
                    'employee_name' => $employee->name,
                    'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                    'mutation_type' => 'INITIAL POSITION',
                    'description' => 'Initial Position',
                    'type' => 'PERMANENT',
                    'division_name' => $division_old ? $division_old->name : '-',
                    'departement_name' => $departement_old ? $departement_old->name : '-',
                    'departement_sub_name' => $departement_sub_old ? $departement_sub_old->name : '-'
                ];
            } else {
                // If no PERMANENT mutation, get from employees table
                $this->db->select('b.number as employee_number, b.name as employee_name, c.name as division_name, d.name as departement_name, e.name as departement_sub_name');
                $this->db->from('employees b');
                $this->db->join('divisions c', 'b.division_id = c.id');
                $this->db->join('departements d', 'b.departement_id = d.id');
                $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
                $this->db->where('b.id', $employee_id);
                $initial_position = $this->db->get()->row_array();

                if ($initial_position) {
                    $all_records[] = array_merge($initial_position, [
                        'id' => 'initial',
                        'employee_id' => $employee_id,
                        'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                        'mutation_type' => 'INITIAL POSITION',
                        'description' => 'Initial Position',
                        'type' => 'PERMANENT'
                    ]);
                }
            }
        }

        // Sort all records by employee name and trans_date
        usort($all_records, function($a, $b) {
            if ($a['employee_name'] != $b['employee_name']) {
                return strcmp($a['employee_name'], $b['employee_name']);
            }
            return strtotime($b['trans_date']) - strtotime($a['trans_date']);
        });

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}.employee-header {background-color: #4CAF50; color: white; font-weight: bold;}</style><body>

        <center>

            <div style="float: left; font-size: 12px; text-align: left;">

                <table style="width: 100%;">

                    <tr>

                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                            <img src="' . $config->favicon . '" width="30">

                        </td>

                        <td style="font-size: 14px; text-align: left; margin:2px;">

                            <b>' . $config->name . '</b><br>

                            <small>MUTATION EMPLOYEE HISTORY</small>

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

                <th>Trans Date</th>

                <th>Type</th>

                <th>Mutation Type</th>

                <th>Division</th>

                <th>Departement</th>

                <th>Departement Sub</th>

                <th>Note</th>

            </tr>';

        $no = 1;
        $current_employee = '';

        foreach ($all_records as $data) {
            // Add employee header if it's a new employee
            // if ($current_employee != $data['employee_name']) {
            //     $current_employee = $data['employee_name'];
            //     $html .= '<tr class="employee-header">
            //             <td colspan="10" style="text-align: center; font-weight: bold; background-color: #4CAF50; color: white;">
            //                 EMPLOYEE: ' . $data['employee_name'] . ' (' . $data['employee_number'] . ')
            //             </td>
            //         </tr>';
            // }

            $html .= '<tr>

                    <td>' . $no . '</td>

                    <td style="mso-number-format:\'@\';">' . $data['employee_number'] . '</td>

                    <td>' . $data['employee_name'] . '</td>

                    <td>' . $data['trans_date'] . '</td>

                    <td>' . $data['type'] . '</td>

                    <td>' . $data['mutation_type'] . '</td>

                    <td>' . $data['division_name'] . '</td>

                    <td>' . $data['departement_name'] . '</td>

                    <td>' . $data['departement_sub_name'] . '</td>

                    <td>' . $data['description'] . '</td>';

            $no++;
        }



        $html .= '</table></body></html>';

        echo $html;

    }



    // HISTORY DATA UNTUK EXPAND ROW
    public function history()
    {
        $employee_id = $this->input->get('employee_id');
        $exclude_id = $this->input->get('exclude_id'); // id mutasi terbaru

        // Ambil data employee untuk posisi awal
        $employee = $this->crud->read('employees', [], ["id" => $employee_id]);
        
        // Ambil semua mutasi employee (termasuk yang sedang di-expand)
        $this->db->select('a.*, b.number as employee_number, b.name as employee_name, a.mutation_type, c.name as division_name, d.name as departement_name, e.name as departement_sub_name');
        $this->db->from('mutations a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('divisions c', 'a.division_id = c.id');
        $this->db->join('departements d', 'a.departement_id = d.id');
        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');
        $this->db->where('a.employee_id', $employee_id);
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('a.created_date', 'DESC');
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
                'employee_id' => $employee_id,
                'employee_number' => $employee->number,
                'employee_name' => $employee->name,
                'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                'mutation_type' => 'INITIAL POSITION',
                'description' => 'Initial Position',
                'type' => 'PERMANENT',
                'division_name' => $division_old ? $division_old->name : '-',
                'departement_name' => $departement_old ? $departement_old->name : '-',
                'departement_sub_name' => $departement_sub_old ? $departement_sub_old->name : '-'
            ];
        } else {
            // Jika tidak ada mutasi PERMANENT, ambil dari tabel employees
            $this->db->select('b.number as employee_number, b.name as employee_name, c.name as division_name, d.name as departement_name, e.name as departement_sub_name');
            $this->db->from('employees b');
            $this->db->join('divisions c', 'b.division_id = c.id');
            $this->db->join('departements d', 'b.departement_id = d.id');
            $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
            $this->db->where('b.id', $employee_id);
            $initial_position = $this->db->get()->row_array();

            if ($initial_position) {
                $result[] = array_merge($initial_position, [
                    'id' => 'initial',
                    'employee_id' => $employee_id,
                    'trans_date' => $employee->date_sign ?? date('Y-m-d'),
                    'mutation_type' => 'INITIAL POSITION',
                    'description' => 'Initial Position',
                    'type' => 'PERMANENT'
                ]);
            }
        }

        echo json_encode(['rows' => $result]);
    }
}

