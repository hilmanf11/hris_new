<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Employees extends CI_Controller

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

            $data['session_name'] = $this->session->name;



            $this->load->view('template/header');

            $this->load->view('dashboard/employees', $data);

        } else {

            redirect('error_session');

        }

    }



    public function chartDepartement()

    {

        $querys = $this->crud->query("SELECT a.name, COUNT(b.number) AS total FROM departements a
        LEFT JOIN employees b ON b.departement_id = a.id
        LEFT JOIN resignations c ON b.id = c.employee_id
        WHERE b.status = 0 AND c.resign_date IS NULL AND b.contract_id NOT IN ('20221119000003', '20250704000001') AND b.group_id != '20221119000002' GROUP BY a.name");

        $name = array();

        $number = array();

        $grand_total = 0;

        $html = "";

        foreach ($querys as $data) {

            $name[] = $data->name;

            $number[] = (int) $data->total;

            $grand_total += (int) $data->total;

            $html .= "<tr><td>".$data->name."</td><td>".$data->total."</td></tr>";

        }

        $html .= "<tr><td><b>Grand Total</b></td><td><b>".$grand_total."</b></td></tr>";

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html, "grand_total" => $grand_total));

    }



    public function chartGroups()

    {

        $querys = $this->crud->query("SELECT a.name, COUNT(b.number) as total FROM groups a
        LEFT JOIN employees b ON b.group_id = a.id
        LEFT JOIN resignations c ON b.id = c.employee_id
        WHERE b.status = 0 AND c.resign_date IS NULL AND b.contract_id NOT IN ('20221119000003', '20250704000001') AND b.group_id != '20221119000002' GROUP BY a.name");

        $name = array();

        $number = array();

        $grand_total = 0;

        $html = "";

        foreach ($querys as $data) {

            $name[] = $data->name;

            $number[] = (int) $data->total;

            $grand_total += (int) $data->total;

            $html .= "<tr><td>".$data->name."</td><td>".$data->total."</td></tr>";

        }

        $html .= "<tr><td><b>Grand Total</b></td><td><b>".$grand_total."</b></td></tr>";

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html, "grand_total" => $grand_total));

    }



    public function chartService()

    {

        $query = $this->crud->query("SELECT
            SUM(CASE WHEN (usia >= 15) and (usia <= 18) THEN 1 ELSE 0 END) as satu,
            SUM(CASE WHEN (usia >= 19) and (usia <= 25) THEN 1 ELSE 0 END) as dua,
            SUM(CASE WHEN (usia >= 26) and (usia <= 35) THEN 1 ELSE 0 END) as tiga,
            SUM(CASE WHEN (usia >= 36) and (usia <= 45) THEN 1 ELSE 0 END) as empat,
            SUM(CASE WHEN (usia >= 46) and (usia <= 55) THEN 1 ELSE 0 END) as lima,
            SUM(CASE WHEN (usia > 55) THEN 1 ELSE 0 END) as enam
        FROM (
            SELECT TIMESTAMPDIFF(YEAR, a.birthday, CURDATE()) AS usia
            FROM employees a LEFT JOIN resignations b ON a.id = b.employee_id
            WHERE a.status = 0 AND b.resign_date IS NULL AND a.contract_id NOT IN ('20221119000003', '20250704000001') AND a.group_id != '20221119000002'
        ) z");

        

        $name = array();

        $number = array();

        $html = "";

        $grand_total = 0;

        $labels = array("15 to 18", "19 to 25", "26 to 35", "36 to 45", "46 to 55", " > 56");

        foreach ($query as $row) {

            $values = array((int) $row->satu, (int) $row->dua, (int) $row->tiga, (int) $row->empat, (int) $row->lima, (int) $row->enam);

            $grand_total = array_sum($values);

            $name = $labels;

            $number = $values;

            for ($i = 0; $i < count($labels); $i++) {

                $percent = $grand_total > 0 ? round(($values[$i] / $grand_total) * 100, 2) : 0;

                $html .= "<tr><td>".$labels[$i]."</td><td>".$values[$i]."</td><td>".$percent."%</td></tr>";

            }

            $html .= "<tr><td><b>Grand Total</b></td><td><b>".$grand_total."</b></td><td><b>100%</b></td></tr>";

        }

        echo json_encode(array("name" => $name, "number" => $number, "html" => $html, "grand_total" => $grand_total));

    }



    public function chartGender()

    {

        $querys = $this->crud->query(
            "SELECT CASE WHEN a.gender = '' THEN 'EMPTY' ELSE a.gender END AS gender, COUNT(a.number) AS total
            FROM employees a LEFT JOIN resignations b ON a.id = b.employee_id
            WHERE a.status = 0 AND b.resign_date IS NULL AND a.contract_id NOT IN ('20221119000003', '20250704000001') AND a.group_id != '20221119000002' GROUP BY a.gender"
        );

        $name = array();



        foreach ($querys as $data) {

            $name[] = array("name" => $data->gender, "y" => (int) $data->total);

        }



        echo json_encode($name);

    }



    public function chartMarital()

    {

        // Ambil semua status marital
        $maritals = $this->crud->query("SELECT id, name FROM maritals WHERE deleted = 0 ORDER BY name ASC");
        // Hitung jumlah karyawan per status marital
        $data = $this->crud->query("SELECT a.id, a.name, COUNT(b.number) as total FROM maritals a
            LEFT JOIN employees b ON b.marital_id = a.id
            LEFT JOIN resignations c ON b.id = c.employee_id
        WHERE b.status = 0 AND c.resign_date IS NULL AND b.contract_id NOT IN ('20221119000003', '20250704000001') AND b.group_id != '20221119000002'
        GROUP BY a.id, a.name ORDER BY name ASC");
        $categories = array();
        $values = array();
        $total = 0;
        foreach ($data as $row) {
            $categories[] = $row->name;
            $values[] = (int)$row->total;
            $total += (int)$row->total;
        }
        // Hitung persentase
        $percentages = array();
        foreach ($values as $val) {
            $percentages[] = $total > 0 ? round(($val / $total) * 100, 2) : 0;
        }
        // Output untuk Highcharts bar chart
        echo json_encode([
            "categories" => $categories,
            "data" => $values,
            "percentages" => $percentages,
            "total" => $total
        ]);

    }



    public function chartDomicile()

    {

        $querys = $this->crud->query("SELECT
            SUM(CASE WHEN a.address LIKE '%purwakarta%' THEN 1 ELSE 0 END) as purwakarta,
            SUM(CASE WHEN a.address NOT LIKE '%purwakarta%' THEN 1 ELSE 0 END) as not_purwakarta
        FROM employees a LEFT JOIN resignations b ON a.id = b.employee_id
        WHERE a.status = 0 AND b.resign_date IS NULL AND a.contract_id NOT IN ('20221119000003', '20250704000001') AND a.group_id != '20221119000002'");

        $name = array();

        $number = array();

        foreach ($querys as $data) {

            $name[] = array("name" => "Purwakarta", "y" => (int) $data->purwakarta);

            $name[] = array("name" => "Outside", "y" => (int) $data->not_purwakarta);

        }

 

        echo json_encode($name);

    }



    public function chartEducation()

    {

        $querys = $this->crud->query("SELECT b.level, COUNT(a.number) as number FROM employees a
        JOIN (select number, level from employee_educations GROUP by number) b ON a.number = b.number
        LEFT JOIN resignations c ON a.id = c.employee_id
        WHERE a.status = 0 AND c.resign_date IS NULL AND a.contract_id NOT IN ('20221119000003', '20250704000001') AND a.group_id != '20221119000002' GROUP BY b.level");

        $name = array();

        $number = array();

        foreach ($querys as $data) {

            $name[] = array("name" => $data->level, "y" => (int) $data->number);

        }

        echo json_encode($name);
    }

}

