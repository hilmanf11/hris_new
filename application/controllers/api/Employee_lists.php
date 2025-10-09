<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_lists extends CI_Controller
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
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'ok',
            'message' => 'Welcome to the API endpoint',
            'available_endpoints' => [
                'GET /hris-bri/api/employee_lists/list/',
                'GET Find name /hris-bri/api/employee_lists/list/name'
            ]
        ]));
    }

    public function list($param = '')
    {
        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("employees")
            ->where('departement_sub_id', '20221213000031')
            ->where('status', '0');
        if (!empty($param)) { $this->db->like('name', $param); }
        $this->db->order_by('name');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->db->get()->result_array()));
    }
}
