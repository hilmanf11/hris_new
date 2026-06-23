<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Groups extends CI_Controller

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

        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[groups.number]');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());



            $this->load->view('template/header', $data);

            $this->load->view('employee/groups');

        } else {

            redirect('error_access');

        }

    }



    //GET DATA

    public function readAll()

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->reads('groups', ["name" => $post]);

        echo json_encode($send);

    }

    public function reads()

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $hl = ($this->input->get('hl')) ? '' : ' !=';

        $send = $this->crud->reads('groups', ["name" => $post], ['id'.$hl => 20221119000002]);

        echo json_encode($send);

    }

    public function read_groups()

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->where('name !=', 'HARIAN LEPAS');

        $send = $this->crud->reads('groups', ["name" => $post]);

        echo json_encode($send);

    }

    //GET DATATABLES

    public function datatables()

    {

        if ($this->input->post()) {

            $filters = json_decode($this->input->post('filterRules'));

            $page = $this->input->post('page');

            $rows = $this->input->post('rows');

            //Pagination 1-10

            $page   = isset($page) ? intval($page) : 1;

            $rows   = isset($rows) ? intval($rows) : 10;

            $offset = ($page - 1) * $rows;

            $result = array();

            //Select Query

            $this->db->select('*');

            $this->db->from('groups');

            $this->db->where('deleted', 0);

            if (@count($filters) > 0) {

                foreach ($filters as $filter) {

                    $this->db->like($filter->field, $filter->value);

                }

            }

            $this->db->order_by('name', 'ASC');

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

    }



    //CREATE DATA

    public function create()

    {

        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post   = $this->input->post();

                $send   = $this->crud->create('groups', $post);

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

            $send = $this->crud->update('groups', ["id" => $id], $post);

            echo $send;

        } else {

            show_error("Cannot Process your request");

        }

    }



    //DELETE DATA

    public function delete()

    {

        $data = $this->input->post();

        $send = $this->crud->delete('groups', $data);

        echo $send;

    }



    //PRINT & EXCEL DATA

    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=groups_$format.xls");

        }



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $this->db->select('*');

        $this->db->from('groups');

        $this->db->where('deleted', 0);

        $this->db->order_by('name', 'ASC');

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

                            <small>MASTER GROUP</small>

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

                <th>Code</th>

                <th>Name</th>

                <th>Description</th>

            </tr>';

        $no = 1;

        foreach ($records as $data) {

            $html .= '<tr>

                    <td>' . $no . '</td>

                    <td>' . $data['number'] . '</td>

                    <td>' . $data['name'] . '</td>

                    <td>' . $data['description'] . '</td>';

            $no++;

        }



        $html .= '</table></body></html>';

        echo $html;

    }

}

