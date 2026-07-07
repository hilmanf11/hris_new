<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Payroll_pkl extends CI_Controller

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

        $this->form_validation->set_rules('employee_id', 'Employee', 'required|min_length[1]|max_length[30]');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $data['permit_type'] = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);

            $data['allowance'] = $this->crud->reads('allowances', [], [], "", "name", "asc");

            $data['deduction'] = $this->crud->reads('deductions');

            $data['permit_type_d'] = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);

            $data['bpjs'] = $this->crud->reads('bpjs', ['status' => 0]);



            $this->load->view('template/header', $data);

            $this->load->view('payroll/payroll_pkl');

        } else {

            redirect('error_access');

        }

    }



    public function readService($dateSign = "", $dateout = "")

    {

        if ($dateSign == "") {

            $date = $this->input->post('date');

        } else {

            $date = $dateSign;

        }



        if ($dateout == "") {

            $end = date_create(); // waktu sekarang

        } else {

            $end = date_create($dateout);

        }



        $start  = date_create($date);

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



    //GET DATA

    public function read()

    {

        $filter_from = $this->input->get('filter_from');

        $readPayroll = $this->crud->reads('payroll_pkl', ["period_start" => $filter_from]);



        if (count($readPayroll) > 0) {

            echo json_encode(array("generate" => "EXIST", "status" => @$readPayroll[0]->status));

        } else {

            echo json_encode(array("generate" => "OK", "status" => @$readPayroll[0]->status));

        }

    }



    //GET DATATABLES

    public function datatables()

    {

        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_source = $this->input->get('filter_source');

            $filter_employee = $this->input->get('filter_employee');

            $filter_status = $this->input->get('filter_status');

            $page   = $this->input->post('page');

            $rows   = $this->input->post('rows');

            //Pagination 1-10

            $page   = isset($page) ? intval($page) : 1;

            $rows   = isset($rows) ? intval($rows) : 10;

            $offset = ($page - 1) * $rows;

            $result = array();

            

            //Select Query

            $this->db->select('a.*, b.national_id, d.number as account_coa, e.name as division_name');

            $this->db->from('payroll_pkl a');

            $this->db->join('employees b', 'a.employee_id = b.id');

            $this->db->join('account_coa c', 'b.division_id = c.division_id and b.departement_id = c.departement_id and b.position_id = c.position_id and b.contract_id = c.contract_id', 'left');

            $this->db->join('accounts d', "c.account_id = d.id and d.category = 'payroll'", 'left');

            $this->db->join('divisions e', 'b.division_id = e.id');

            if ($filter_from != "" && $filter_to != "") {

                $this->db->where('period_start =', $filter_from);

            }

            if (!empty($filter_status)) { $this->db->where('b.status', intval($filter_status)); }

            $this->db->like('a.employee_id', $filter_employee);

            $this->db->like('a.source_id', $filter_source);

            $this->db->group_by('a.employee_number');

            $this->db->having('COUNT(a.employee_number) = 1 OR (COUNT(a.employee_number) > 1 AND d.number IS NOT NULL)');

            $this->db->order_by('a.departement_name', 'ASC');

            $this->db->order_by('a.departement_sub_name', 'ASC');

            $this->db->order_by('a.employee_name', 'ASC');



            //Total Data

            $totalRows = $this->db->count_all_results('', false);

            //Limit 1 - 10

            $this->db->limit($rows, $offset);

            //Get Data Array

            $records = $this->db->get()->result_array();

            // die($this->db->last_query());

            $datas = array();

            $datas['total'] = $totalRows;

            $datas['rows'] = $records;

            echo json_encode($datas);

        }

    }



    public function generatePayroll()

    {

        if ($this->input->get()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_source = $this->input->get('filter_source');

            $filter_employee = $this->input->get('filter_employee');



            $query = $this->db->query("SELECT a.*, b.name as source_name, b.amount, b.boarding_fee, c.name as departement_name, d.name as departement_sub_name

                FROM employees a

                JOIN sources b ON a.source_id = b.id

                JOIN departements c ON a.departement_id = c.id

                JOIN departement_subs d ON a.departement_sub_id = d.id

                JOIN groups e ON a.group_id = e.id and e.name = 'PKL'

                WHERE a.deleted = 0 and a.status = 0

                AND a.source_id LIKE '%$filter_source%'

                AND a.id LIKE '%$filter_employee%'

                GROUP BY a.id

                ORDER BY b.name, a.name asc");

            $records = $query->result_array();



            $datas = array();

            $datas['total'] = count($records);

            $datas['rows'] = $records;

            echo json_encode($datas);

        }

    }



    //CREATE DATA

    public function create()

    {

        if ($this->input->post()) {

            $record = $this->input->post();

            $employee_id = $record['id'];



            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');



            //Convert tanggal ke time supaya hitungan tanggal nya mudah

            $start = strtotime($filter_from);

            $finish = strtotime($filter_to);



            $allowance_1 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "1"]);

            $allowance_2 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "2"]);

            $allowance_3 = $this->crud->read("allowance_students", [], ["group_id" => $record['group_id'], "months" => "3"]);



            $correctionPlus = $this->crud->query("SELECT SUM(amount) as total FROM corrections WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and employee_id = '$employee_id' and correction_type = 'PLUS' GROUP BY employee_id");

            $correctionMinus = $this->crud->query("SELECT SUM(amount) as total FROM corrections WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and employee_id = '$employee_id' and correction_type = 'MINUS' GROUP BY employee_id");

            //$cashCarries = $this->crud->query("SELECT SUM(amount) as total FROM cash_carries WHERE trans_date BETWEEN '$filter_from' and '$filter_to' and employee_id = '$employee_id' GROUP BY employee_id");



            $date_sign = date_create($record['date_sign']);

            // $payroll_end = date_create($filter_to);

            // $interval = date_diff($date_sign, $payroll_end);

            $internship = $record['amount'];



            $payroll_3 = 0;

            $payroll_2 = 0;

            $payroll_1 = 0;

            $attandance_count = 0;

            $working_date = "";

            $tidakabsen = 0;



            for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {

                $working_date = date('Y-m-d', $z);



                $this->db->select("a.employee_id, SUM(a.duration) as duration");

                $this->db->from('permits a');

                $this->db->join('permit_types b', 'a.permit_type_id = b.id');

                $this->db->where('a.status', 0);

                $this->db->where('b.attandance', 'YES');

                $this->db->where('a.employee_id', $record['id']);

                $this->db->where('a.permit_date', $working_date);

                $this->db->group_by('a.employee_id');

                $permit = $this->db->get()->row();

                // die($this->db->last_query());

                //Change Days

                $this->db->select("COUNT(*) as days");

                $this->db->from('change_days');

                $this->db->where('employee_id', $record['id']);

                $this->db->where("(start = '$working_date' or end = '$working_date')");

                $changeDays = $this->db->get()->row();

                $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;



                //Working Calendar

                $this->db->select('description');

                $this->db->from('calendars');

                $this->db->where('trans_date', $working_date);

                $holiday = $this->db->get()->row();



                $tidakabsen = @$permit->duration;



                //Attandance

                $this->db->select("*");

                $this->db->from('attandances');

                $this->db->where('number', $record['number']);

                $this->db->where("(date_in = '$working_date' or date_out = '$working_date')");

                $attandance = $this->db->get()->row();



                //Shift and Setting Group

                $tolerance_hour_min = date("H:i:s", strtotime('-2 Hour', strtotime(@$attandance->time_in)));

                $tolerance_hour_plus = date("H:i:s", strtotime('+2 Hour', strtotime(@$attandance->time_in)));

                $this->db->select("d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name");

                $this->db->from('shift_employees b');

                $this->db->join('shifts c', 'c.id = b.shift_id');

                $this->db->join('shift_details d', 'd.shift_id = c.id');

                $this->db->where('b.employee_id', $record['id']);

                if (@$attandance->time_in > "23:00:00") {

                    $this->db->where("d.start >= '$tolerance_hour_min'");

                } elseif (@$attandance->time_in != "") {

                    $this->db->where("d.start >= '$tolerance_hour_min' and d.start <= '$tolerance_hour_plus'");

                }

                $shift = $this->db->get()->row();



                if (@$holiday->description == null) {

                    if (@$shift->days == "6") {

                        //minggu doang libur

                        if (date('w', $z) !== '0') {

                            if ($attandance) {

                                $day = 1;

                            } else {

                                if($changeDays_amount > 0){

                                    $day = 1;

                                }else{

                                    $day = $tidakabsen;

                                }

                            }

                        } else {

                            $day = 0;

                        }

                    } else {

                        //sabtu dan minggu libur

                        if (date('w', $z) !== '0' && date('w', $z) !== '6') {

                            if ($attandance) {

                                $day = 1;

                            } else {

                                if($changeDays_amount > 0){

                                    $day = 1;

                                }else{

                                    $day = $tidakabsen;

                                }

                            }

                        } else {

                            $day = 0;

                        }

                    }

                } else {

                    $day = 0;

                }



                $payroll_end = date_create($working_date);

                $dateThree = date_create(date('Y-m-d', strtotime('+1 month', strtotime($record['date_sign']))));

                $dateFour = date_create(date('Y-m-d', strtotime('+2 month', strtotime($record['date_sign']))));



                $interval = date_diff($date_sign, $payroll_end);

                $intervalThree = date_diff($date_sign, $dateThree);

                $intervalFour = date_diff($date_sign, $dateFour);



                if ($interval->days <= $intervalThree->days) {

                    $payroll_3 += 0;

                    $payroll_2 += 0;

                    $payroll_1 += $day;

                } elseif ($interval->days <= $intervalFour->days) {

                    $payroll_3 += 0;

                    $payroll_2 += $day;

                    $payroll_1 += 0;

                } else {

                    $payroll_3 += $day;

                    $payroll_2 += 0;

                    $payroll_1 += 0;

                }



                $attandance_count += @$day;

            }



            $allowence3 = ($payroll_3 * @$allowance_3->amount);
            $allowence2 = ($payroll_2 * @$allowance_2->amount);
            $allowence1 = ($payroll_1 * @$allowance_1->amount);
            $allowence = ($allowence1 + $allowence2 + $allowence3);
            // die('('.$payroll_3.' * '.@$allowance_3->amount.') + ('.$payroll_2.' * '.@$allowance_2->amount.') + ('.$payroll_1.' * '.@$allowance_1->amount.')');

            // if ($attandance_count == 0) {

            //     $total_income = 0;

            //     $intern_fee = 0;

            //     $boarding = 0;

            // } else {

            $intern_fee = ($internship * @$attandance_count);

            $boarding = $record['boarding_fee'];

            $total_income = @($allowence + ($correctionPlus[0]->total - $correctionMinus[0]->total) + ($internship * @$attandance_count) + $boarding);

            //}



            $arr = array(

                "employee_id" => $record['id'],

                "employee_number" => $record['number'],

                "employee_name" => $record['name'],

                "period_start" => $filter_from,

                "period_end" => $filter_to,

                "date_sign" => $record['date_sign'],

                "services" => $this->readService($record['date_sign'], $filter_to),

                "source_id" => $record['source_id'],

                "source_name" => $record['source_name'],

                "departement_name" => $record['departement_name'],

                "departement_sub_name" => $record['departement_sub_name'],

                "attandance" => @$attandance_count,

                "month_1" => @$payroll_1,

                "month_2" => @$payroll_2,

                "month_3" => @$payroll_3,

                "allowence" => @$allowence,

                "intern_fee" => @$intern_fee,

                "boarding" => @$boarding,

                "correction_plus" => @$correctionPlus[0]->total,

                "correction_minus" => @$correctionMinus[0]->total,

                "total_income" => @$total_income,

            );



            if (@$arr == null) {

                $result = [];

            } else {

                $result = @$arr;

            }



            // $checkPayroll = $this->crud->read("payroll_pkl", [], ["employee_id" => $record['id'], "period_start" => $filter_from]);

            // if (!empty($checkPayroll->employee_id)) {

            //     $send = $this->crud->update('payroll_pkl', ["employee_id" => $record['id'], "period_start" => $filter_from], $result);

            //     echo $send;

            // } else {

            //     $send = $this->crud->create('payroll_pkl', $result);

            //     echo $send;

            // }

            $checkPayroll = $this->crud->read("payroll_pkl", [], ["employee_id" => $record['id'], "period_start" => $filter_from]);
            
            if (!empty($checkPayroll->employee_id)) {
                $this->crud->delete("payroll_pkl", ["employee_id" => $record['id'], "period_start" => $filter_from]);
            }
            
            $send = $this->crud->create('payroll_pkl', $result);
            echo $send;

        } else {

            echo json_encode(array("title" => "Error", "message" => "Cannot Process your request", "theme" => "error"));

        }

    }



    //UPDATE DATA

    public function update()

    {

        $filter_from = $this->input->post('filter_from');

        $period_start = date("Y-m", strtotime($filter_from));

        $this->db->where('period_start', $period_start);

        $send = $this->db->update('payroll_pkl', ["status" => 1]);

        echo $send;

    }



    //DELETE DATA

    public function delete()

    {

        $post = $this->input->post();



        $this->db->select('a.*');

        $this->db->from('payroll_pkl a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('a.period_start', $post['filter_from']);

        $this->db->where('a.period_end', $post['filter_to']);

        if ($post['filter_source'] != "") {

            $this->db->where('b.source_id', $post['filter_source']);

        }

        if ($post['filter_employee'] != "") {

            $this->db->where('b.id', $post['filter_employee']);

        }

        $payroll_pkl = $this->db->get()->result_object();



        foreach ($payroll_pkl as $payroll) {

            $this->db->delete("payroll_pkl", ['id' => $payroll->id]);

        }

    }



    public function delete_id()

    {

        $data = $this->input->post();

        $delete = $this->crud->delete("payroll_pkl", ['id' => $data['id']]);

        echo $delete;

    }



    //PRINT & EXCEL DATA

    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=payroll_pkl_$format.xls");

        }



        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $filter_source = $this->input->get('filter_source');

        $filter_employee = $this->input->get('filter_employee');

        $filter_status = $this->input->get('filter_status');

        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        //Select Query

        $this->db->select('a.*, b.national_id, d.number as coa, e.name as division_name');

        $this->db->from('payroll_pkl a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('account_coa c', 'b.division_id = c.division_id and b.departement_id = c.departement_id and b.position_id = c.position_id and b.contract_id = c.contract_id', 'left');

        $this->db->join('accounts d', "c.account_id = d.id and d.category = 'payroll'", 'left');

        $this->db->join('divisions e', 'b.division_id = e.id');

        if (!empty($filter_status)) { $this->db->where('b.status', intval($filter_status)); }

        if ($filter_from != "" && $filter_to != "") {

            $this->db->where('a.period_start =', $filter_from);

        }



        $this->db->like('a.employee_id', $filter_employee);

        $this->db->like('a.source_id', $filter_source);

        $this->db->group_by('a.employee_number');

        $this->db->having('COUNT(a.employee_number) = 1 OR (COUNT(a.employee_number) > 1 AND d.number IS NOT NULL)');

        $this->db->order_by('a.departement_name', 'ASC');

        $this->db->order_by('a.departement_sub_name', 'ASC');

        $this->db->order_by('a.employee_name', 'ASC');

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

                            <small>DATA PAYROLL PKL</small>

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

                <th>NIK</th>

                <th>Employee Name</th>

                <th>COA No</th>

                <th>Join Date</th>

                <th>Fit of Services</th>

                <th>Division</th>

                <th>Departement</th>

                <th>Departement Sub</th>

                <th>Sources</th>

                <th>Attandance</th>

                <th>Allowence</th>

                <th>Correction Plus</th>

                <th>Correction Minus</th>

                <th>Income</th>

            </tr>';

        $no = 1;

        foreach ($records as $data) {

            $html .= '  <tr>

                            <td>' . $no . '</td>

                            <td style="mso-number-format:\@;">' . $data['employee_number'] . '</td>

                            <td style="mso-number-format:\@;">' . $data['national_id'] . '</td>

                            <td>' . $data['employee_name'] . '</td>

                            <td>' . $data['coa'] . '</td>

                            <td>' . date("d F Y", strtotime($data['date_sign'])) . '</td>

                            <td>' . $data['services'] . '</td>

                            <td>' . $data['division_name'] . '</td>

                            <td>' . $data['departement_name'] . '</td>

                            <td>' . $data['departement_sub_name'] . '</td>

                            <td>' . $data['source_name'] . '</td>

                            <td>' . $data['attandance'] . '</td>

                            <td>' . number_format($data['allowence']) . '</td>

                            <td>' . number_format($data['correction_plus']) . '</td>

                            <td>' . number_format($data['correction_minus']) . '</td>

                            <td>' . number_format($data['total_income']) . '</td>

                        </tr>';

            $no++;

        }



        $html .= '</table></body></html>';

        echo $html;

    }

}

