<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Payrolls extends CI_Controller
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
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['users'] = $this->crud->reads('users', [], ["actived" => 0, "deleted" => 0], "", "name", "asc");
            $data['session_name'] = $this->session->name;

            $this->load->view('template/header');
            $this->load->view('dashboard/payrolls', $data);
        } else {
            redirect('error_session');
        }
    }

    public function myPayroll()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $mp = $this->crud->query("SELECT
            SUM(total_mp) AS total_mp,
            SUM(total_bruto) AS total_bruto,
            SUM(total_income) AS total_income,
            SUM(total_ter) AS total_ter
        FROM (
            SELECT COUNT(employee_id) as total_mp, SUM(income) as total_bruto, SUM(net_income) as total_income, SUM(ter) as total_ter FROM payrolls WHERE period_start = '$period_start' and period_end = '$period_end'
            UNION ALL
            SELECT COUNT(employee_id) as total_mp, SUM(income) as total_bruto, SUM(net_income) as total_income, SUM(ter) as total_ter FROM payroll_harian_lepas WHERE period_start = '$period_start' and period_end = '$period_end'
        ) x");
        // die($this->db->last_query());

        $pkl = $this->crud->query("SELECT SUM(total_income) as total_pkl, COUNT(employee_id) as total_mp FROM payroll_pkl
        WHERE period_start like '%$period_start%' and period_end like '%$period_end%'");

        echo json_encode(array("total_mp" => (@$mp[0]->total_mp + @$pkl[0]->total_mp), "total_pkl" => @$pkl[0]->total_pkl, "total_income" => @$mp[0]->total_income, "total_ter" => @$mp[0]->total_ter));
    }

    public function chartDepartement()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT departement_name, SUM(income) AS income FROM (
            SELECT c.name as departement_name, SUM(a.net_income) as income 
            FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name
            UNION ALL
            SELECT c.name as departement_name, SUM(a.net_income) as income 
            FROM payroll_harian_lepas a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name
            UNION ALL
            SELECT c.name as departement_name, SUM(a.total_income) as income
            FROM payroll_pkl a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY departement_name ORDER BY departement_name");
        // die($this->db->last_query());
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->departement_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function chartPosition()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT position_name, SUM(income) AS income FROM (
            SELECT position_name, SUM(net_income) as income FROM payrolls
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name
            UNION ALL
            SELECT position_name, SUM(net_income) as income FROM payroll_harian_lepas
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name
            UNION ALL
            SELECT c.name, SUM(a.total_income) as income FROM payroll_pkl a
              LEFT JOIN employees b ON a.employee_id = b.id
              LEFT JOIN positions c ON b.position_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY position_name ORDER BY position_name");
        
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->position_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function chartGroup()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT group_name, SUM(income) AS income FROM (
            SELECT group_name, SUM(net_income) as income FROM payrolls
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY group_name
            UNION ALL
            SELECT group_name, SUM(net_income) as income FROM payroll_harian_lepas
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY group_name
            UNION ALL
            SELECT c.name, SUM(a.total_income) as income FROM payroll_pkl a
              LEFT JOIN employees b ON a.employee_id = b.id
              LEFT JOIN groups c ON b.group_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY group_name ORDER BY group_name");
        
        $name = array();
        $income = array();
        foreach ($query as $data) {
            $name[] = $data->group_name;
            $income[] = (int) $data->income;
        }

        die(json_encode(array("name" => $name, "income" => $income)));
    }

    public function tableDepartement()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT name, SUM(total) AS total, SUM(total_mp) AS total_mp FROM (
            SELECT c.name, SUM(a.net_income) as total, COUNT(a.employee_id) as total_mp
            FROM payrolls a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name
            UNION ALL
            SELECT c.name, SUM(a.net_income) as total, COUNT(a.employee_id) as total_mp
            FROM payroll_harian_lepas a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' 
            GROUP BY c.name
            UNION ALL
            SELECT c.name as departement_name, SUM(a.total_income) as income, COUNT(a.employee_id) as total_mp
            FROM payroll_pkl a
                JOIN employees b ON a.employee_id = b.id
                JOIN departements c ON b.departement_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY name ORDER BY total DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;
        $result['footer'] = [(object) ['name' => 'Grand Total', 'total' => 0, 'total_mp' => 0]];
        foreach ($query as $index => $row) {
            $result['footer'][0]->name = 'Grand Total';
            $result['footer'][0]->total += $row->total;
            $result['footer'][0]->total_mp += $row->total_mp;
        }

        die(json_encode($result));
    }

    public function tablePosition()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT name, SUM(total) AS total, SUM(total_mp) AS total_mp FROM (
            SELECT position_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp
            FROM payrolls
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name
            UNION ALL
            SELECT position_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp
            FROM payroll_harian_lepas
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY position_name
            UNION ALL
            SELECT c.name, SUM(a.total_income) as income, COUNT(a.employee_id) as total_mp FROM payroll_pkl a
              LEFT JOIN employees b ON a.employee_id = b.id
              LEFT JOIN positions c ON b.position_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY name ORDER BY total DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;
        $result['footer'] = [(object) ['name' => 'Grand Total', 'total' => 0, 'total_mp' => 0]];
        foreach ($query as $index => $row) {
            $result['footer'][0]->name = 'Grand Total';
            $result['footer'][0]->total += $row->total;
            $result['footer'][0]->total_mp += $row->total_mp;
        }

        die(json_encode($result));
    }

    public function tableGroup()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        
        $query = $this->crud->query("SELECT name, SUM(total) AS total, SUM(total_mp) AS total_mp FROM (
            SELECT group_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp FROM payrolls 
            WHERE period_start = '$period_start' and period_end = '$period_end' 
            GROUP BY group_name
            UNION ALL
            SELECT group_name as name, SUM(net_income) as total, COUNT(employee_id) as total_mp FROM payroll_harian_lepas
            WHERE period_start = '$period_start' and period_end = '$period_end' GROUP BY group_name
            UNION ALL
            SELECT c.name, SUM(a.total_income) as income, COUNT(a.employee_id) as total_mp FROM payroll_pkl a
              LEFT JOIN employees b ON a.employee_id = b.id
              LEFT JOIN groups c ON b.group_id = c.id
            WHERE a.period_start = '$filter_from' and a.period_end = '$filter_to' 
            GROUP BY c.name
        ) x GROUP BY name ORDER BY total DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;
        $result['footer'] = [(object) ['name' => 'Grand Total', 'total' => 0, 'total_mp' => 0]];
        foreach ($query as $index => $row) {
            $result['footer'][0]->name = 'Grand Total';
            $result['footer'][0]->total += $row->total;
            $result['footer'][0]->total_mp += $row->total_mp;
        }

        die(json_encode($result));
    }

    public function chartPkl()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        $query = $this->crud->query("SELECT source_name, SUM(total_income) as income FROM payroll_pkl
        WHERE period_start like '%$period_start%' and period_end like '%$period_end%' 
        GROUP BY source_name ORDER BY source_name ASC");
        
        $name = array();
        foreach ($query as $data) {
            $name[] = ["name" => $data->source_name, "y" => (int) $data->income];
        }

        die(json_encode($name));
    }

    public function tablePkl()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        
        $query = $this->crud->query("SELECT source_name as name, SUM(total_income) as total, COUNT(employee_id) as total_mp
        FROM payroll_pkl 
        WHERE period_start like '%$period_start%' and period_end like '%$period_end%' 
        GROUP BY source_name ORDER BY SUM(total_income) DESC");

        $result['total'] = count($query);
        $result['rows'] = $query;
        $result['footer'] = [(object) ['name' => 'Grand Total', 'total' => 0, 'total_mp' => 0]];
        foreach ($query as $index => $row) {
            $result['footer'][0]->name = 'Grand Total';
            $result['footer'][0]->total += $row->total;
            $result['footer'][0]->total_mp += $row->total_mp;
        }

        die(json_encode($result));
    }
}
