<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Master_contract extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) { redirect('error_session'); }
        elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('employee/master_contract');
        } else { redirect('error_access'); }
    }

    public function datatables()
    {
        $this->db->distinct()->select(
             "TRIM(a.id) AS id,"
            ."TRIM(a.contracts) AS contract_id,"
            ."TRIM(a.position_id) AS position_id,"
            ."COALESCE(TRIM(b.name), '') AS position,"
            ."GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') AS contract,"
            ."TRIM(a.attachment) AS attachment,"
            ."COALESCE(TRIM(d.name), '') AS c_by,"
            ."DATE(a.created_date) AS c_date,"
            ."COALESCE(TRIM(e.name), '') AS u_by,"
            ."DATE(a.updated_date) AS u_date"
        )->from("master_contract a")
            ->join("positions b", "a.position_id = b.id")
            ->join("contracts c", "FIND_IN_SET(c.id, a.contracts)")
            ->join("users d", "a.created_by = d.username", "LEFT")
            ->join("users e", "a.updated_by = e.username", "LEFT")
        ->group_by("b.name, a.attachment")->order_by("position, contract");
        // die($this->db->get_compiled_select());
        $datas = $this->db->get()->result_object();
        $result = array('total' => count($datas), 'rows' => []);
        foreach ($datas as $key => $value) { array_push($result['rows'], $value); }

        echo json_encode($result);
    }

    public function forms()
    {
        $result = ['position' => [], 'contract' => []];

        $result['position'] = $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("positions")
        ->order_by("name")->get()->result_object();

        $result['contract'] = $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("contracts")
        ->order_by("name")->get()->result_object();

        echo json_encode($result);
    }

    public function save($act = '')
    {
        $result = ['stat' => 'ok'];

        if ($act == 'attachment') {
            $loc = 'assets/document/master_contract/';

            if (!is_dir('./'.$loc)) { mkdir('./'.$loc, 0777, TRUE); }

            $result['attachment'] = $this->crud->upload('attachment', ['xls', 'xlsx'], $loc);
        } else {
            $post = $this->input->post();
            $send_data = ['attachment' => base64_decode($post['attachment'])];

            $this->db->trans_start();

            if (!isset($post['id'])) {
                $send_data['position_id'] = base64_decode($post['position']);
                $send_data['contracts'] = base64_decode($post['contract']);

                $this->crud->create('master_contract', $send_data);
                $stat = $this->db->where($send_data)->get('master_contract')->num_rows() > 0;
            } else {
                $where = ['id' => base64_decode($post['id'])];

                $this->crud->update('master_contract', $where, $send_data);
                $stat = $this->db->where($where)->get('master_contract')->num_rows() > 0;
            }

            if (!$stat) {
                $result['stat'] = $this->db->error()['message'];
                // $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function delete()
    {
        $result = [];

        if ($this->input->post()) {
            $post = $this->input->post();
            $result['stat'] = 'ok';

            $this->db->trans_start();

            foreach (json_decode(base64_decode($post['id'])) as $key => $value) {
                $this->crud->delete('master_contract', ['id' => $value]);
                if ($this->db->where(['id' => $value])->get('master_contract')->num_rows() != 0)
                    { $result['stat'] = $this->db->get_compiled_select(); break; }
            }

            if ($result['stat'] != 'ok') { $this->db->trans_rollback(); }
            else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }
}
