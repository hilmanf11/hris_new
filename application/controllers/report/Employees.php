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



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());



            $this->load->view('template/header', $data);

            $this->load->view('report/employees');

        } else {

            redirect('error_access');

        }

    }
    public function readAge($date = "")
    {
        $start  = date_create($date);
        $end = date_create(); // waktu sekarang
        $diff  = date_diff($start, $end);

        return $diff->y.' Years';
    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=employees_$format.xls");

        }



        if ($this->input->get()) {

            $form = $this->input->get();



            if (@$form['filter_column'][0] == "") {

                die('<h3 style="color:red;">PLEASE CHOOSE DISPLAY COLUMN</h3>');

            } else {

                $this->db->select("a.*, 

                        (case when a.date_expired = '0000-00-00' then '-' else a.date_expired end) as date_expired,

                        b.users_id_from as status_check,

                        b.users_id_to as status_notification, 

                        c.name as division_name, 

                        d.name as departement_name, 

                        e.name as departement_sub_name,

                        e.type, 

                        g.name as position_name,

                        h.name as contract_name,

                        i.name as group_name,

                        j.name as source_name,

                        k.name as marital_name,

                        l.name as religion_name,

                        a.job_type,

                        m.resign_date AS date_resign,
                        n.name as shift_type,
                        p.level AS level_education,
                        p.degree,
                        a.date_sign AS sign_date,
                        a.birthday AS age");

                $this->db->from('employees a');

                $this->db->join('notifications b', "a.id = b.table_id and b.table_name = 'employees'", 'left');

                $this->db->join('divisions c', 'c.id = a.division_id');

                $this->db->join('departements d', 'd.id = a.departement_id');

                $this->db->join('departement_subs e', 'e.id = a.departement_sub_id');

                $this->db->join('agreements f', 'a.number = f.number and f.status = 0');

                $this->db->join('positions g', 'g.id = a.position_id', 'left');

                $this->db->join('contracts h', 'h.id = a.contract_id', 'left');

                $this->db->join('groups i', 'i.id = a.group_id', 'left');

                $this->db->join('sources j', 'j.id = a.source_id', 'left');

                $this->db->join('maritals k', 'k.id = a.marital_id', 'left');

                $this->db->join('religions l', 'l.id = a.religion_id', 'left');

                $this->db->join('resignations m', 'a.id = m.employee_id', 'left');
                $this->db->join('shift_employees q', 'a.id = q.employee_id', 'left');
                $this->db->join('shifts n', 'q.shift_id = n.id', 'left');
                $this->db->join("(".
                    "SELECT number, MAX(end) AS end_date FROM employee_educations GROUP BY number"
                .") o", 'a.number = o.number', 'left');
                $this->db->join('employee_educations p', 'a.number = p.number AND o.end_date = p.end', 'left');

                $this->db->where('a.deleted', 0);

                $this->db->like("a.division_id", $form['filter_division']);

                $this->db->like("a.departement_id", $form['filter_departement']);

                $this->db->like("a.departement_sub_id", $form['filter_departement_sub']);

                $this->db->like("a.id", $form['filter_employee']);

                $this->db->like("a.status", $form['filter_status']);

                $this->db->order_by('a.name', 'ASC');

                $records = $this->db->get()->result_array();



                $header = "<tr><th width='20'>No</th>";

                for ($i = 0; $i < count($form['filter_column']); $i++) {

                    $header .= "<th>" . strtoupper(strtr($form['filter_column'][$i], "_", " ")) . "</th>";

                }

                // $header .= "<th width='80'>STATUS</th>";

                $header .= "</tr>";



                //Config

                $this->db->select('*');

                $this->db->from('config');

                $config = $this->db->get()->row();



                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>

                <style> .str{ mso-number-format:\@; } </style>

                <body>

                <center>

                    <div style="float: left; font-size: 12px; text-align: left;">

                        <table style="width: 100%;">

                            <tr>

                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                    <img src="' . $config->favicon . '" width="30">

                                </td>

                                <td style="font-size: 14px; text-align: left; margin:2px;">

                                    <b>' . $config->name . '</b><br>

                                    <small>' . $config->description . '</small>

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div style="float: right; font-size: 12px; text-align: right;">

                        Print Date ' . date("d M Y H:i:s") . ' <br>

                        Print By ' . $this->session->username . '  

                    </div>

                </center><br><br><br>

                <center>

                    <h3 style="margin:0;">REPORT EMPLOYEE</h3>

                </center>

                <br>

                

                <table id="customers" border="1">';

                $html .= $header;



                $no = 1;

                $content = "";

                foreach ($records as $data) {

                    if ($data['status'] == 0) {

                        $status = "ACTIVE";

                        $style = "style='background: #82FF6C; text-align:center;'";

                    } else {

                        $status = "NON ACTIVE";

                        $style = "style='background: #FF796C; text-align:center;'";

                    }



                    $this->db->select('level');

                    $this->db->from('employee_educations');

                    $this->db->where('number', $data['number']);

                    $this->db->order_by('id', 'desc');

                    $employee_education = $this->db->get()->row();



                    $content = "<tr>

                                    <td>" . $no . "</td>";

                    for ($z = 0; $z < count($form['filter_column']); $z++) {

                        if ($form['filter_column'][$z] == "education"){

                            $content .= "<td style='mso-number-format:\@;'>" . @$employee_education->level . "</td>";

                        } elseif ($form['filter_column'][$z] == "job_type"){

                            $content .= "<td style='mso-number-format:\@;'>" . ($data['job_type'] ? $data['job_type'] : '-') . "</td>";

                        } elseif ($form['filter_column'][$z] == "status") {

                            $content .= "   <td " . $style . ">" . $status . "</td>";

                        } elseif ($form['filter_column'][$z] == "age") {

                            $content .= '   <td style="width: 200px;">' . $this->readAge($data['age']) . '</td>';

                        } else {

                            $content .= "<td style='mso-number-format:\@;'>" . $data[$form['filter_column'][$z]] . "</td>";

                        }

                    }

                    $html .= $content."</tr>";

                    $no++;

                }



                $html .= '</table></body></html>';

                echo $html;

            }

        }

    }

}

