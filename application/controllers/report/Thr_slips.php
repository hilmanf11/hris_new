<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Thr_slips extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->helper('url');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('session');

        $this->load->model('crud');

        $this->load->model('emails');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());



            $this->load->view('template/header', $data);

            $this->load->view('report/thr_slips');

        } else {

            redirect('error_access');

        }

    }



    public function datatables()

    {

        if ($this->input->get()) {

            $filter_year = $this->input->get('filter_year');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            //Select Query

            $this->db->select('

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                c.name as departement_name, 

                d.name as departement_sub_name, 

                e.name as group_name, 

                COUNT(b.id) as employee, 

                SUM(a.total_thr) as income');

            $this->db->from('thr a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period =', $filter_year);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id"));

            $this->db->order_by('departement_name', 'ASC');

            $this->db->order_by('income', 'ASC');
            // die($this->db->get_compiled_select());

            //Get Data Array

            $records = $this->db->get()->result_array();


            $this->db->select('

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                c.name as departement_name, 

                d.name as departement_sub_name, 

                e.name as group_name, 

                COUNT(b.id) as employee, 

                SUM(a.total_thr) as income');

            $this->db->from('thr_hl a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period =', $filter_year);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id"));

            $this->db->order_by('departement_name', 'ASC');

            $this->db->order_by('income', 'ASC');
            // die($this->db->get_compiled_select());

            foreach ($this->db->get()->result_array() as $index => $row) {
                $idx = array_search($row['departement_id'], array_column($records, 'departement_id'))
                    && array_search($row['departement_sub_id'], array_column($records, 'departement_sub_id'));
                if (gettype($idx) != 'boolean') { $records[$idx]['employee'] += floatval($row['employee']); }
                else { array_push($records, $row); }
            }


            echo json_encode($records);

        }

    }



    public function getData()

    {

        if ($this->input->get()) {

            $filter_year = $this->input->get('filter_year');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email

            FROM thr a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period = '$filter_year'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY b.name ASC");

            $records = $query->result_array();



            die(json_encode($records));

        }

    }



    public function sendMail()

    {

        error_reporting(0);

        $filter_year = $this->input->get('filter_year');

        $record = $this->input->post('data');



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $html = '<html>

                <head>

                    <title>Print Data</title>

                </head>

                <style>

                    body {font-family: Arial, Helvetica, sans-serif;}

                    #customers {border-collapse: collapse;width: 100%;font-size: 14px;}

                    #customers th {border: 1px solid black;padding: 5px;}

                    #customers td {padding-left: 5px; padding-right:5px;}

                    #customers tr:hover {background-color: #ddd;}

                    #customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}

                </style>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

                <body>';



        $html .= '  <center><div class="container" style="border:1px solid black; margin-bottom:20px; padding-top:10px; float:left;">

                        <table style="width: 100%;">

                            <tr>

                                <td width="80" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                    <img src="' . $config->favicon . '" width="40">

                                </td>

                                <td style="font-size: 20px; text-align: left; margin:2px;">

                                    <b>' . $config->name . '</b>

                                </td>

                                <td style="font-size: 20px; text-align: right; margin:2px;">

                                    THR SLIP

                                </td>

                            </tr>

                        </table>

                        <hr>

                        <div style="float:left; width:50%;">

                            <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                <tr>

                                    <th style="text-align:left; width: 30%;">Period</th>

                                    <th style="text-align:left; width: 5%;">:</th>

                                    <th style="text-align:left; width: 60%;">' . $filter_year . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">Employee ID</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">' . $record['employee_number'] . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">Employee Name</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">' . $record['employee_name'] . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">Departement</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">' . $record['departement_name'] . '</th>

                                </tr>

                            </table>

                        </div>

                        <div style="float:left; width:50%;">

                            <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                <tr>

                                    <th style="text-align:left;">Departement Sub</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">' . $record['departement_sub_name'] . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left; width: 30%;">National ID</th>

                                    <th style="text-align:left; width: 5%;">:</th>

                                    <th style="text-align:left;">' . $record['national_id'] . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">Tax ID</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">' . $record['tax_id'] . '</th>

                                </tr>

                                <tr>

                                    <th style="text-align:left;">Martial Status</th>

                                    <th style="text-align:left;">:</th>

                                    <th style="text-align:left;">(' . $record['marital'] . ') <small>' . $record['marital_name'] . '<small></th>

                                </tr>

                            </table>

                        </div>

                        <div style="float:left; width:100%;">

                            <div class="row">

                                <div class="col p-0">

                                    <table id="customers" style="width:100%; border-right: 1px solid black;">

                                        <tr>

                                            <th colspan="2" style="text-align:center">INCOME</th>

                                        </tr>

                                        <tr>

                                            <td style="text-align:left;" width="250">Basic Salary</td>

                                            <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>

                                        </tr>

                                        <tr>

                                            <td style="text-align:left;">Allowence </td>

                                            <td style="text-align:right;"><b>' . number_format($record['allowence']) . '</b></td>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">TOTAL INCOME <i>(a)</i></th>

                                            <th style="text-align:right;"><b>' . number_format($record['total']) . '</b></th>

                                        </tr>

                                        <tr>

                                            <th colspan="2" style="text-align:center;"><img src="http://hris.piranti-ind.com/hris-piranti/assets/image/thr.png" width="250"></th>

                                        </tr>

                                    </table>

                                </div>

                                <div class="col p-0">

                                    <table id="customers" style="width:100%; border-left: 1px solid black;">

                                        <tr>

                                            <th style="text-align:left;">FIT OF SERVICE</th>

                                            <th style="text-align:right;">'.$record['services'].'</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">THR <i>(b)</i></th>

                                            <th style="text-align:right;"><b>' . number_format($record['thr']) . '</b></th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">TER <i>(c)</i></th>

                                            <th style="text-align:right;"><b>' . number_format($record['pph']) . '</b></th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:left;">NET THR <i>(b - c)</i></th>

                                            <th style="text-align:right;"><b>' . number_format($record['total_thr']) . '</b></th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:center;">Print By</th>

                                            <th style="text-align:center;">Accept By</th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:center; height:70px;"></th>

                                            <th style="text-align:center; height:70px;"></th>

                                        </tr>

                                        <tr>

                                            <th style="text-align:center;">' . $this->session->name . '</th>

                                            <th style="text-align:center;">' . $record['employee_name'] . '</th>

                                        </tr>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div></center>';

        $html .= '</body></html>';



        $email = $this->emails->emailThrSlip($record['email'], $record['employee_name'], $config->name, base64_encode($html));

        if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {

            echo json_encode(array("title" => "Not Registered", "message" => "Email not register in Employee List", "theme" => "error"));

        } elseif ($email) {

            echo json_encode(array("title" => "Delivered", "message" => "Salary Slip successfully sent to " . $record['email'], "theme" => "success"));

        } else {

            echo json_encode(array("title" => "Failed", "message" => "Salary Slip unseccessfull sent to " . $record['email'], "theme" => "error"));

        }

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=salary_slip_$format.xls");

        }



        if ($this->input->get()) {

            $filter_year = $this->input->get('filter_year');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;


            $q = "SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email

            FROM thr a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period = '$filter_year'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY b.name ASC";
            // die($q);
            $query = $this->db->query($q);

            $records = $query->result_array();


            $q = "SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.tax_id, i.name as marital_name, b.email

            FROM thr_hl a

            JOIN employees b ON a.employee_id = b.id

            JOIN privilege_groups e ON b.group_id = e.group_id and e.username = '$username' and e.status = '1'

            LEFT JOIN maritals i ON a.marital = i.number

            WHERE a.period = '$filter_year'

            AND b.division_id LIKE '%$filter_division%'

            AND b.departement_id LIKE '%$filter_departement%'

            AND b.departement_sub_id LIKE '%$filter_departement_sub%'

            AND b.group_id LIKE '%$filter_group%'

            AND a.employee_id LIKE '%$filter_employee%'

            GROUP BY b.id

            ORDER BY b.name ASC";
            // die($q);
            $query = $this->db->query($q);

            foreach ($query->result_array() as $index => $row) {
                $row["total"] = $row["salary"];
                array_push($records, $row);
            }
            // foreach ($query->result_array() as $index => $row) {
            //     $idx = array_search($row['departement_id'], array_column($records, 'departement_id'));
            //     $records[$idx]['employee'] += floatval($row['employee']);
            // }

            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html>

                <head>

                    <title>Print Data</title>

                </head>

                <style>

                    body {font-family: Arial, Helvetica, sans-serif;}

                    #customers {border-collapse: collapse;width: 100%;font-size: 14px;}

                    #customers th {border: 1px solid black;padding: 5px;}

                    #customers td {padding-left: 5px; padding-right:5px;}

                    #customers tr:hover {background-color: #ddd;}

                    #customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}

                </style>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

                <body>';

            $no = 1;

            //Looping per Employee

            foreach ($records as $record) {

                $html .= '  <div class="container" style="border:1px solid black; margin-bottom:20px; padding-top:10px; float:left;">

                                    <table style="width: 100%;">

                                        <tr>

                                            <td width="80" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                                <img src="' . $config->favicon . '" width="40">

                                            </td>

                                            <td style="font-size: 20px; text-align: left; margin:2px;">

                                                <b>' . $config->name . '</b>

                                            </td>

                                            <td style="font-size: 20px; text-align: right; margin:2px;">

                                                THR SLIP

                                            </td>

                                        </tr>

                                    </table>

                                    <hr>

                                    <div style="float:left; width:50%;">

                                        <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                            <tr>

                                                <th style="text-align:left; width: 30%;">Period</th>

                                                <th style="text-align:left; width: 5%;">:</th>

                                                <th style="text-align:left; width: 60%;">' . $filter_year . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Employee ID</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['employee_number'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Employee Name</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['employee_name'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Departement</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['departement_name'] . '</th>

                                            </tr>

                                        </table>

                                    </div>

                                    <div style="float:left; width:50%;">

                                        <table style="width:100%; margin-bottom: 5px; font-size: 12px;">

                                            <tr>

                                                <th style="text-align:left;">Departement Sub</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['departement_sub_name'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left; width: 30%;">National ID</th>

                                                <th style="text-align:left; width: 5%;">:</th>

                                                <th style="text-align:left;">' . $record['national_id'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Tax ID</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">' . $record['tax_id'] . '</th>

                                            </tr>

                                            <tr>

                                                <th style="text-align:left;">Martial Status</th>

                                                <th style="text-align:left;">:</th>

                                                <th style="text-align:left;">(' . $record['marital'] . ') <small>' . $record['marital_name'] . '<small></th>

                                            </tr>

                                        </table>

                                    </div>

                                    <div style="float:left; width:100%;">

                                        <div class="row">

                                            <div class="col p-0">

                                                <table id="customers" style="width:100%; border-right: 1px solid black;">

                                                    <tr>

                                                        <th colspan="2" style="text-align:center">INCOME</th>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;" width="250">Basic Salary</td>

                                                        <td style="text-align:right;" width="150"><b>' . number_format($record['salary']) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <td style="text-align:left;">Allowence </td>

                                                        <td style="text-align:right;"><b>' . number_format($record['allowence']) . '</b></td>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">TOTAL INCOME <i>(a)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['total']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th colspan="2" style="text-align:center;"><img src="' . base_url("assets/image/thr.png") . '" width="250"></th>

                                                    </tr>

                                                </table>

                                            </div>

                                            <div class="col p-0">

                                                <table id="customers" style="width:100%; border-left: 1px solid black;">

                                                    <tr>

                                                        <th style="text-align:left;">FIT OF SERVICE</th>

                                                        <th style="text-align:right;">'.$record['services'].'</th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">THR <i>(b)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['thr']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">TER <i>(c)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['pph']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:left;">NET THR <i>(b - c)</i></th>

                                                        <th style="text-align:right;"><b>' . number_format($record['total_thr']) . '</b></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center;">Print By</th>

                                                        <th style="text-align:center;">Accept By</th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center; height:70px;"></th>

                                                        <th style="text-align:center; height:70px;"></th>

                                                    </tr>

                                                    <tr>

                                                        <th style="text-align:center;">' . $this->session->name . '</th>

                                                        <th style="text-align:center;">' . $record['employee_name'] . '</th>

                                                    </tr>

                                                </table>

                                            </div>

                                        </div>

                                    </div>

                                </div>';



                if ($no % 2 == 0) {

                    $html .= '</div>';

                }

                $no++;

            }



            $html .= '</body></html>';

            echo $html;

            $html .= '</table>';

        }

    }



    public function print_recap($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=report_summary_payroll_$format.xls");

        }



        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_group = $this->input->get('filter_group');

            $username = $this->session->username;



            $period_start = date("Y-m", strtotime($filter_from));

            $period_end = date("Y-m", strtotime($filter_to));



            //Select Query

            $this->db->select('

                b.departement_id,

                b.departement_sub_id,

                b.group_id,

                c.name as departement_name, 

                d.name as departement_sub_name, 

                e.name as group_name, 

                COUNT(b.id) as employee, 

                SUM(a.net_income) as income');

            $this->db->from('payrolls a');

            $this->db->join('employees b', "a.employee_id = b.id");

            $this->db->join('departements c', "b.departement_id = c.id");

            $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

            $this->db->join('groups e', "b.group_id = e.id");

            $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

            $this->db->where('a.deleted', 0);

            $this->db->where('a.period_start =', $period_start);

            $this->db->where('a.period_end =', $period_end);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('b.group_id', $filter_group);

            $this->db->group_by(array("c.id", "d.id", "e.id"));

            $this->db->order_by('departement_name', 'ASC');

            $this->db->order_by('income', 'ASC');

            //Get Data Array

            $payrolls = $this->db->get()->result_array();



            //Config Page

            $rows = 40;

            $page = ceil(count($payrolls) / $rows);



            //Config

            $this->db->select('*');

            $this->db->from('config');

            $config = $this->db->get()->row();



            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>';

            $no = 1;

            $hal = 1;

            for ($i = 0; $i < $page; $i++) {

                //Select Query

                $this->db->select('

                    b.departement_id,

                    b.departement_sub_id,

                    b.group_id,

                    c.name as departement_name, 

                    d.name as departement_sub_name, 

                    e.name as group_name, 

                    COUNT(b.id) as employee, 

                    SUM(a.total_thr) as income');

                $this->db->from('payrolls a');

                $this->db->join('employees b', "a.employee_id = b.id");

                $this->db->join('departements c', "b.departement_id = c.id");

                $this->db->join('departement_subs d', "b.departement_sub_id = d.id");

                $this->db->join('groups e', "b.group_id = e.id");

                $this->db->join('privilege_groups f', "b.group_id = f.group_id and f.username = '$username' and f.status = '1'");

                $this->db->where('a.deleted', 0);

                $this->db->where('a.period_start =', $period_start);

                $this->db->where('a.period_end =', $period_end);

                $this->db->like('b.id', $filter_employee);

                $this->db->like('b.division_id', $filter_division);

                $this->db->like('b.departement_id', $filter_departement);

                $this->db->like('b.departement_sub_id', $filter_departement_sub);

                $this->db->like('b.group_id', $filter_group);

                $this->db->group_by(array("c.id", "d.id"));

                $this->db->order_by('c.name', 'ASC');

                $this->db->order_by('SUM(a.total_thr)', 'ASC');

                $this->db->limit(50, ($i * 50));

                //Get Data Array

                $records = $this->db->get()->result_array();



                $html .= '  <center>

                            <div style="float: left; font-size: 12px; text-align: left;">

                                <table style="width: 100%;">

                                    <tr>

                                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                                            <img src="' . $config->favicon . '" width="30">

                                        </td>

                                        <td style="font-size: 14px; text-align: left; margin:2px;">

                                            <b>' . $config->name . '</b><br>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                            <div style="float: right; font-size: 12px; text-align: right;">

                                Print Date ' . date("d M Y H:m:s") . ' <br>

                                Print By ' . $this->session->username . ' <br>

                                Page ' . $hal . '/' . $page . '

                            </div>

                        </center>

                        <br><br><br>

                        <center>

                            <h3 style="margin:0;">REPORT RECAP SALARY SLIP</h3>

                            <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>

                            <br>

                        </center>

                        

                        <table id="customers" border="1">

                            <tr>

                                <th width="20" style="text-align:center;">No</th>

                                <th style="text-align:center;">Departement</th>

                                <th style="text-align:center;">Departement Sub</th>

                                <th style="text-align:center;">Employee</th>

                            </tr>';

                $totalEmployee = 0;

                $totalIncome = 0;

                foreach ($records as $data) {

                    $html .= '  <tr>

                                    <td style="text-align:center;">' . $no . '</td>

                                    <td>' . $data['departement_name'] . '</td>

                                    <td>' . $data['departement_sub_name'] . '</td>

                                    <td style="text-align:right;">' . number_format($data['employee']) . '</td>

                                </tr>';

                    $totalEmployee += $data['employee'];

                    $totalIncome += $data['income'];

                    $no++;

                }



                $hal++;

                if (($i + 1) != $page) {

                    $html .= '<div style="page-break-after:always;"></div>';

                }

            }



            $html .= '</body></html>';

            echo $html;

        }

    }

}

