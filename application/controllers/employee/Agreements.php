<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Agreements extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Employee ID', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('employee/agreements');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('agreements', ["number" => $post]);
        echo json_encode($send);
    }

    public function readEmployees()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->db->group_start()
            ->like('number', $post)
            ->or_like('name', $post)
        ->group_end()->order_by('name', 'asc')->get('employees')->result();
        echo json_encode(['rows' => $send, 'total' => count($send)]);
    }

    public function readDivisions()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->db->group_start()
            ->like('id', $post)
            ->or_like('name', $post)
        ->group_end()->order_by('name', 'asc')->get('divisions')->result();
        if (count($send) == 1) { $send[0]->selected = true; }
        echo json_encode($send);
    }

    public function readDepartments()
    {
        $get = isset($_GET['id']) ? base64_decode($_GET['id']) : '';
        if (!empty($get)) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->db->where('division_id', $get)
            ->group_start()
                ->like('id', $post)
                ->or_like('name', $post)
            ->group_end()->order_by('name', 'asc')->get('departements')->result();
            if (count($send) == 1) { $send[0]->selected = true; }
            echo json_encode($send);
        }
    }

    public function readDepartmentSubs()
    {
        $get = isset($_GET['id']) ? base64_decode($_GET['id']) : '';
        if (!empty($get)) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->db->where('departement_id', $get)
            ->group_start()
                ->like('id', $post)
                ->or_like('name', $post)
            ->group_end()->order_by('name', 'asc')->get('departement_subs')->result();
            if (count($send) == 1) { $send[0]->selected = true; }
            echo json_encode($send);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_expired = $this->input->get('filter_expired');
        $filter_status = $this->input->get('filter_status');
        $aprvDepartement = $this->checkApprovalAccess('agreements');

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

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select(
             "a.*,"
            ."b.name as employee_name,"
            ."c.name as division_name,"
            ."d.name as departement_name,"
            ."e.name as departement_sub_name,"
            ."e.type,"
            ."f.name as position_name,"
            ."g.name as contract_name,"
            ."b.division_id,"
            ."b.departement_id,"
            ."b.departement_sub_id"
        )->from('agreements a')
            ->join('employees b', 'a.number = b.number')
            ->join('divisions c', 'b.division_id = c.id')
            ->join('departements d', 'b.departement_id = d.id')
            ->join('departement_subs e', 'b.departement_sub_id = e.id')
            ->join('positions f', 'f.id = a.position_id')
            ->join('contracts g', 'g.id = a.contract_id')
            ->join('groups h', 'h.id = a.group_id')
            ->where('a.deleted', 0)
            ->like('b.departement_id', $aprvDepartement)
            ->like('b.division_id', $filter_divisions)
            ->like('b.departement_id', $filter_departements)
            ->like('b.departement_sub_id', $filter_departement_subs)
            ->like('b.id', $filter_employees)
            ->like('a.status', $filter_status);
        $whereExpired;
        $this->db->order_by('b.name', 'ASC');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $approval = $this->crud->read('approvals', [], ["table_name" => "agreements"]);
                $attachment = $this->crud->upload('attachment', ['pdf', 'docx'], 'assets/document/agreement/');
                $postEmployee = [
                    "name" => $post['employee_name'],
                    "division_id" => $post['division_id'],
                    "departement_id" => $post['departement_id'],
                    "departement_sub_id" => $post['departement_sub_id'],
                    "position_id" => $post['position_id'],
                    "group_id" => $post['group_id'],
                    "contract_id" => $post['contract_id'],
                    "date_contract" => $post['date_sign'],
                    "date_expired" => $post['date_expired'],
                ];

                unset($post['employee_name']);
                unset($post['division_id']);
                unset($post['departement_id']);
                unset($post['departement_sub_id']);

                $this->crud->update('agreements', ["number" => $post['number']], ["status" => 1]);
                $send = $this->crud->create('agreements', array_merge($post, array("attachment" => $attachment)));
                if (empty($approval)) { $this->crud->update('employees', ["number" => $post['number']], $postEmployee); }
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
            $approval = $this->crud->read('approvals', [], ["table_name" => "agreements"]);
            $attachment = $this->crud->upload('attachment', ['pdf', 'docx'], 'assets/document/agreement/');
            $postEmployee = [
                "name" => $post['employee_name'],
                "division_id" => $post['division_id'],
                "departement_id" => $post['departement_id'],
                "departement_sub_id" => $post['departement_sub_id'],
                "position_id" => $post['position_id'],
                "group_id" => $post['group_id'],
                "contract_id" => $post['contract_id'],
                "date_contract" => $post['date_sign'],
                "date_expired" => $post['date_expired'],
            ];

            unset($post['employee_name']);
            unset($post['division_id']);
            unset($post['departement_id']);
            unset($post['departement_sub_id']);
            // die(var_dump($postEmployee));
            $this->crud->update('agreements', ["number" => $post['number']], ["status" => 1]);
            $this->crud->delete('agreements', ["id" => $id]);
            $send = $this->crud->create('agreements', array_merge($post, array("attachment" => $attachment)));
            if (empty($approval)) { $this->crud->update('employees', ["number" => $post['number']], $postEmployee); }
            // die($this->db->last_query());
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('agreements', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=agreements_$format.xls");
        }

        //Filter Data
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_expired = $this->input->get('filter_expired');
        $filter_status = $this->input->get('filter_status');
        $aprvDepartement = $this->checkApprovalAccess('agreements');

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

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,
            b.name as employee_name, 
            c.name as division_name, 
            d.name as departement_name, 
            e.name as departement_sub_name,
            e.type, 
            f.name as position_name,
            g.name as contract_name');
        $this->db->from('agreements a');
        $this->db->join('employees b', 'a.number = b.number');
        $this->db->join('divisions c', 'b.division_id = c.id');
        $this->db->join('departements d', 'b.departement_id = d.id');
        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');
        $this->db->join('positions f', 'f.id = a.position_id');
        $this->db->join('contracts g', 'g.id = a.contract_id');
        $this->db->join('groups h', 'h.id = a.group_id');
        $this->db->where('a.deleted', 0);
        $this->db->like('b.departement_id', $aprvDepartement);
        $whereExpired;
        $this->db->like('b.division_id', $filter_divisions);
        $this->db->like('b.departement_id', $filter_departements);
        $this->db->like('b.departement_sub_id', $filter_departement_subs);
        $this->db->like('b.id', $filter_employees);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('b.name', 'ASC');
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
                            <small>AGREEMENT EMPLOYEE</small>
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
                <th>Division</th>
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Position</th>
                <th>Employee Type</th>
                <th>Join Date</th>
                <th>Exp Date</th>
                <th>Note</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['employee_name'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['position_name'] . '</td>
                    <td>' . $data['contract_name'] . '</td>
                    <td>' . $data['date_sign'] . '</td>
                    <td>' . $data['date_expired'] . '</td>
                    <td>' . $data['note'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
