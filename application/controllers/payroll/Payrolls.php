<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Payrolls extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('banks');
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
            $data['deduction'] = $this->crud->reads('deductions', [], [], "", "name", "asc");
            $data['permit_type_d'] = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
            $data['bpjs'] = $this->crud->reads('bpjs', ['status' => 0]);
            $this->load->view('template/header', $data);
            $this->load->view('payroll/payrolls');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function read()
    {
        $filter_from = $this->input->get('filter_from');
        $period_start = date("Y-m", strtotime($filter_from));
        $readPayroll = $this->crud->reads('payrolls', ["period_start" => $period_start]);
        if (count($readPayroll) > 0) {
            echo json_encode(array("generate" => "EXIST", "status" => @$readPayroll[0]->status));
        } else {
            echo json_encode(array("generate" => "OK", "status" => @$readPayroll[0]->status));
        }
    }
    public function readApproval()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to = $this->input->post('filter_to');
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        $readPayroll = $this->crud->read('payrolls', [], ["period_start" => $period_start, "period_end" => $period_end]);
        if (empty($readPayroll->approved_to)) {
            echo json_encode(["status" => "APPROVE"]);
        } else {
            echo json_encode(["status" => "CHECKED"]);
        }
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $filter_status = $this->input->get('filter_status');
            $username = $this->session->username;
            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));
            $this->db->select('a.*, b.national_id, d.name as source_name, e.employee_id as status_resign, g.number as account_no');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('sources d', 'b.source_id = d.id', 'left');
            $this->db->join('privilege_groups c', "(b.group_id = c.group_id or b.group_id is null or b.group_id = '') and c.username = '$username' and c.status = '1'");
            $this->db->join('resignations e', 'e.employee_id = b.id', 'left');
            $this->db->join('account_coa f', 'b.division_id = f.division_id and b.departement_id = f.departement_id and b.position_id = f.position_id and b.contract_id = f.contract_id', 'left');
            $this->db->join('accounts g', "f.account_id = g.id and g.category = 'payroll'", 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.period_start =', $period_start);
                $this->db->where('a.period_end =', $period_end);
            }
            if($filter_status == "0"){
                $this->db->where('e.employee_id', null);
            }elseif($filter_status == "1"){
                $this->db->where('e.employee_id !=', null);
            }
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.contract_id', $filter_employee_type);
            $this->db->like('c.group_id', $filter_group);
            $this->db->order_by('a.name', 'ASC');
            $this->db->group_by('a.employee_id');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr = array(
                    "id" => $record['id'],
                    "employee_id" => $record['employee_id'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "period_start" => $record['period_start'],
                    "period_end" => $record['period_end'],
                    "status_resign" => $record['status_resign'],
                    "division_name" => $record['division_name'],
                    "departement_name" => $record['departement_name'],
                    "departement_sub_name" => $record['departement_sub_name'],
                    "contract_name" => $record['contract_name'],
                    "position_name" => $record['position_name'],
                    "group_name" => $record['group_name'],
                    "job_type" => $record['job_type'],
                    "source_name" => $record['source_name'],
                    "level" => $record['level'],
                    "marital" => $record['marital'],
                    "ter_number" => $record['ter_number'],
                    "tax_id" => $record['tax_id'],
                    "account_no" => $record['account_no'],
                    "national_id" => $record['national_id'],
                    "shift_name" => $record['shift_name'],
                    //"attandance" => json_encode($arr_permit_combine),
                    "attandance_wd" => $record['attandance_wd'],
                    "working_day" => $record['working_day'],
                    "salary" => $record['salary'],
                    //"allowence" => json_encode($arr_allowance_combine),
                    //"bpjs_company" => json_encode($arr_bpjs_com_combine),
                    "bpjs_company_total" => $record['bpjs_company_total'],
                    "bpjs_company_total_2" => $record['bpjs_company_total'],
                    "correction_plus" => $record['correction_plus'],
                    "overtime_weekday" => $record['overtime_weekday'],
                    "overtime_convert_weekday" => $record['overtime_convert_weekday'],
                    "overtime_amount_weekday" => $record['overtime_amount_weekday'],
                    "overtime_holiday" => $record['overtime_holiday'],
                    "overtime_convert_holiday" => $record['overtime_convert_holiday'],
                    "overtime_amount_holiday" => $record['overtime_amount_holiday'],
                    "overtime_correction" => $record['overtime_correction'],
                    "overtime_convert_correction" => $record['overtime_convert_correction'],
                    "overtime_amount_correction" => $record['overtime_amount_correction'],
                    "total_overtime" => $record['total_overtime'],
                    "total_overtime_convert" => $record['total_overtime_convert'],
                    "total_overtime_amount" => $record['total_overtime_amount'],
                    "total_all_allowance" => $record['total_all_allowance'],
                    //"deduction_number" => json_encode($arr_permit_type_combine_b),
                    //"deduction_amount" => json_encode($arr_permit_type_combine),
                    "deduction_absence" => $record['deduction_absence'],
                    "deduction_absence_amount" => $record['deduction_absence_amount'],
                    "correction_minus" => $record['correction_minus'],
                    "total_all_deduction" => $record['total_all_deduction'],
                    "income" => $record['income'],
                    //"deduction" => json_encode($arr_deduction_combine),
                    "loan_cooperative" => $record['loan_cooperative'],
                    "loan_bank" => $record['loan_bank'],
                    "loan_other" => $record['loan_other'],
                    //"bpjs_employee" => json_encode($arr_bpjs_emp_combine),
                    "bpjs_employee_total" => $record['bpjs_employee_total'],
                    "pph" => $record['pph'],
                    "ter" => $record['ter'],
                    "net_income" => $record['net_income'],
                );
                $arr_final[] = array_merge(
                    $arr,
                    json_decode($record['attandance'], true),
                    json_decode($record['allowence'], true),
                    json_decode($record['bpjs_company'], true),
                    json_decode($record['deduction_number'], true),
                    json_decode($record['deduction_amount'], true),
                    json_decode($record['deduction'], true),
                    json_decode($record['bpjs_employee'], true)
                );
            }
            if (@$arr_final == null) {
                $result = [];
            } else {
                $result = @$arr_final;
            }
            $datas = array();
            $datas['total'] = $totalRows;
            $datas['rows'] = $result;
            echo json_encode($datas);
        }
    }
    public function generatePayroll()
    {
        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $username = $this->session->username;
            $q = "SELECT a.*, 
                    b.name as division_name,
                    c.name as departement_name,
                    d.name as departement_sub_name,
                    d.type,
                    f.id as contract_id,
                    f.name as `contract_name`,
                    g.name as position_name,
                    g.level,
                    i.id as group_id,
                    i.name as group_name,
                    n.name as shift_name,
                    l.name as shift_name_2,
                    l.days,
                    p.amount as salary,
                    p.bpjs,
                    q.number as marital,
                    q.ter_type,
                    a.tax_id
                FROM employees a
                JOIN divisions b ON a.division_id = b.id
                JOIN departements c ON a.departement_id = c.id
                JOIN departement_subs d ON a.departement_sub_id = d.id
                LEFT JOIN contracts f ON a.contract_id = f.id
                LEFT JOIN positions g ON a.position_id = g.id
                LEFT JOIN groups i ON a.group_id = i.id
                LEFT JOIN shift_employees k ON a.id = k.employee_id
                LEFT JOIN shifts n ON k.shift_id = n.id
                LEFT JOIN shift_details l ON n.id = l.shift_id
                JOIN setup_salaries p ON p.employee_id = a.id
                LEFT JOIN maritals q ON a.marital_id = q.id
                JOIN privilege_groups m ON (i.id = m.group_id or m.group_id is null or m.group_id = '') and m.username = '$username' and m.status = '1'
                WHERE a.deleted = 0 and (a.status = 0 or a.id IN (SELECT employee_id FROM resignations WHERE resign_date between '$filter_from' and '$filter_to' and status_resign = 'ON PROCEDURE')) and a.date_sign <= '$filter_to'
                AND a.division_id LIKE '%$filter_division%'
                AND a.departement_id LIKE '%$filter_departement%'
                AND a.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.contract_id LIKE '%$filter_employee_type%'
                AND a.id LIKE '%$filter_employee%'
                AND a.group_id LIKE '%$filter_group%'
                GROUP BY a.id
                ORDER BY b.name ASC, c.name ASC, d.name ASC, a.number, a.name ASC";
            // die($q);
            $query = $this->db->query($q);
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
            $record   = $this->input->post();

            //ambil data dari variabel filter
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');

            //Ubah format tanggal menjadi tahun dan bulan
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            //Convert tanggal ke time supaya hitungan tanggal nya mudah
            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            $q = "SELECT MIN(date_in) AS date FROM attandances WHERE number = '".$record['number']."'";
            $start_date = new DateTime($this->db->query($q)->row()->date);

            //Looping tanggal awal sampai tanggal akhir
            $weekend_day = [];
            $weekday_day = [];
            // $weekday = [];
            // $weekday2 = [];
            // $weekend = [];
            // $weekend2 = [];
            for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                $week = date('w', $i);
                $date = date('Y-m-d', $i);
                if ($record['days'] == "4") {
                    if (in_array((($start_date->diff(new DateTime($date))->days + 1) % 6), [5,0]))
                        { $weekend_day[] = $date; }
                    else { $weekday_day[] = $date; }
                } else {
                    $weekend = ["0"]; //minggu doang libur
                    if ($record['days'] == "5") { $weekend[] = "6"; } //tambah sabtu

                    if (in_array($week, $weekend)) { $weekend_day[] = $date; }
                    else { $weekday_day[] = $date; }
                }

                //Jika tanggal yg di looping bukan hari sabtu dan minggu
                // if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    //Hari kerja
                    // $weekday[] = date('Y-m-d', $i);
                // } else {
                    //Hari libur
                    // $weekend[] = date('Y-m-d', $i);
                // }
                // if (date('w', $i) !== '0') {
                    //Hari kerja
                    // $weekday2[] = date('Y-m-d', $i);
                // } else {
                    //Hari libur
                    // $weekend2[] = date('Y-m-d', $i);
                // }
            }
            // if ($record['days'] == "6") {
                // $weekend_day = $weekend2;
                // $weekday_day = $weekday2;
            // } else {
                // $weekend_day = $weekend;
                // $weekday_day = $weekday;
            // }
            //Setting Payroll
            $config = $this->db->get('payroll_config')->result();
            //Tanggal merah di master calendar
            $this->db->select('trans_date');
            $this->db->from('calendars');
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where_not_in('trans_date', $weekend_day);
            $calendar = $this->db->get()->result_array();
            $calendar_amount = empty($calendar) ? 0 : count($calendar);
            $w_calendars = array();
            foreach ($calendar as $cal) {
                $w_calendars[] = $cal['trans_date'];
            }
            //Permit yang non deduction atau tidak potong gaji
            $permit_date = "'" . implode("', '", $weekend_day) . "'";
            $calendar_date = "'" . implode("', '", $w_calendars) . "'";
            if(count($w_calendars) > 0){
                $calendar_where = "and a.permit_date not in ($calendar_date)";
            }else{
                $calendar_where = "";
            }
            $q_permit = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date)
                    WHERE b.payroll = 'NON DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");
            $r_permit = $q_permit->result_array();
            $arr_permit_number = "";
            $arr_permit_amount = "";
            $arr_total_permit = 0;
            foreach ($r_permit as $permit_data) {
                $arr_permit_number .= strtolower($permit_data['number']) . "a,";
                $arr_permit_amount .= $permit_data['amount'] . ",";
                $arr_total_permit += $permit_data['amount'];
            }
            $arr_permit_number_ex = explode(",", substr($arr_permit_number, 0, -1));
            $arr_permit_amount_ex = explode(",", substr($arr_permit_amount, 0, -1));
            $arr_permit_combine = array_combine($arr_permit_number_ex, $arr_permit_amount_ex);
            //Permit yang deduction atau potong gaji
            $q_permit_deduction = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) $calendar_where
                    WHERE b.payroll = 'DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");
            $r_permit_deduction = $q_permit_deduction->result_array();
            $arr_total_permit_deduction = 0;
            foreach ($r_permit_deduction as $permit_data_deduction) {
                $arr_total_permit_deduction += $permit_data_deduction['amount'];
            }
            //Permit atau absen nya YES di anggap masuk kerja
            $q_permit_absence = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) $calendar_where
                    WHERE b.absence = 'YES' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");
            $r_permit_absence = $q_permit_absence->result_array();
            // die($this->db->last_query());
            $arr_total_permit_absence = 0;
            foreach ($r_permit_absence as $permit_data_absence) {
                $arr_total_permit_absence += $permit_data_absence['amount'];
            }
            //Permit atau absen nya NO di anggap tidak kerja
            $q_permit_absence_no = $this->db->query("SELECT b.number, b.name, COUNT(a.permit_date) as amount
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to' and a.permit_date not in ($permit_date) $calendar_where
                    WHERE b.absence = 'NO' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id");
            $r_permit_absence_no = $q_permit_absence_no->result_array();
            $arr_total_permit_absence_no = 0;
            foreach ($r_permit_absence_no as $permit_data_absence_no) {
                $arr_total_permit_absence_no += $permit_data_absence_no['amount'];
            }
            //Change Days
            $this->db->select("COUNT(*) as days");
            $this->db->from('change_days');
            $this->db->where('employee_id', $record['id']);
            $this->db->where("(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')");
            if (count($weekday_day) > 0) {
                $this->db->where_in('end', $weekday_day);
            }
            $changeDays = $this->db->get()->row();
            $changeDays_amount = empty($changeDays->days) ? 0 : $changeDays->days;
            //Correction PLUS
            $this->db->select("SUM(amount) as amount");
            $this->db->from('corrections');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('correction_type', 'PLUS');
            $correction_plus = $this->db->get()->row();
            $correction_plus_amount = empty($correction_plus->amount) ? 0 : $correction_plus->amount;
            //Correction MINUS
            $this->db->select("SUM(amount) as amount");
            $this->db->from('corrections');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('correction_type', 'MINUS');
            $correction_minus = $this->db->get()->row();
            $correction_minus_amount = empty($correction_minus->amount) ? 0 : $correction_minus->amount;
            //Loan Bank
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'BANK');
            $loan_bank = $this->db->get()->row();
            $loan_bank_amount = empty($loan_bank->amount) ? 0 : $loan_bank->amount;
            //Loan Cooperative
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'COOPERATIVE');
            $loan_cooperative = $this->db->get()->row();
            $loan_cooperative_amount = empty($loan_cooperative->amount) ? 0 : $loan_cooperative->amount;
            //Loan Other
            $this->db->select("SUM(amount) as amount");
            $this->db->from('loans');
            $this->db->where('employee_id', $record['id']);
            $this->db->where('trans_date >=', $filter_from);
            $this->db->where('trans_date <=', $filter_to);
            $this->db->where('loan_type', 'OTHER');
            $loan_other = $this->db->get()->row();
            $loan_other_amount = empty($loan_other->amount) ? 0 : $loan_other->amount;
            //Attandances
            $tomorow = date('Y-m-d', strtotime("+1 day", strtotime($filter_from)));
            $this->db->select("number, date_in");
            $this->db->from('attandances');
            $this->db->where('number', $record['number']);
            $this->db->where("((date_in >= '$filter_from' and date_in <= '$filter_to') or (date_out >= '$tomorow' and date_out <= '$filter_to'))");
            if (count($weekend_day) > 0) {
                $this->db->where_not_in('date_in', $weekend_day);
            }
            if (count($w_calendars) > 0) {
                $this->db->where_not_in('date_in', $w_calendars);
            }
            $this->db->group_by('date_in');
            $attandances = $this->db->get()->result_array();
            $attandance_amount = 0;
            foreach ($attandances as $attandance) {
                $attandance_amount++;
            }
            //Hitung HKW atau jumlah hari kerja
            $hkw = (@count($weekday_day) - @$calendar_amount);
            //Hitung bereapa hari dia ga ada absen
            $absen = (@count($weekday_day) - @$calendar_amount - @$attandance_amount - @$changeDays_amount - @$arr_total_permit_absence_no - $arr_total_permit_absence);
            //Hitung Hari dia masuk kerja
            $working_days = ($attandance_amount + $arr_total_permit_absence + $changeDays_amount);
            //Jika hari kerja nya lebih besar dari HKW maka nilai ambil HKW
            // die($attandance_amount.' + '.$arr_total_permit_absence.' + '.$changeDays_amount);
            // die($working_days.' >= '.$hkw);
            if ($working_days >= $hkw) {
                $wd = $hkw;
            } else {
                $wd = $working_days;
            }
            //Total Masuk kerja
            $masuk = @$wd;
            //Allowances / Tunjangan kerja
            $q_allowance = $this->db->query("SELECT b.number, b.name, coalesce(a.amount, 0) as amount, b.calculate_days, b.type
                    FROM allowances b
                    LEFT JOIN setup_allowances a ON a.allowance_id = b.id and a.employee_id = '$record[id]'
                    GROUP BY b.id ORDER BY b.name asc");
            $r_allowance = $q_allowance->result_array();
            $arr_allowance_number = "";
            $arr_allowance_amount = "";
            $arr_allowance_amount_total = 0;
            $arr_allowance_amount_total_bpjs = 0;
            $arr_allowance_amount_number = 0;
            $count_allowance_number = 0;
            $sum_allowance_tj = 0;
            foreach ($r_allowance as $allowance_data) {
                if ($allowance_data['calculate_days'] == "1") {
                    $arr_allowance_number .= strtolower($allowance_data['number']) . ",";
                    $arr_allowance_amount .= ($allowance_data['amount'] * $masuk) . ",";
                    $arr_allowance_amount_total += ($allowance_data['amount'] * $masuk);
                } else {
                    $arr_allowance_number .= strtolower($allowance_data['number']) . ",";
                    $arr_allowance_amount .= $allowance_data['amount'] . ",";
                    $arr_allowance_amount_total += $allowance_data['amount'];
                }
                if ($allowance_data['type'] == "TEMPORARY" && $allowance_data['amount'] > 0) {
                    $arr_allowance_amount_total_bpjs += 0;
                    $count_allowance_number += 1;
                    if ($allowance_data['calculate_days'] == "1") {
                        $arr_allowance_amount_number += ($allowance_data['amount'] * $masuk);
                    } else {
                        $arr_allowance_amount_number += ($allowance_data['amount']);
                    }
                } elseif ($allowance_data['type'] == "FIX" && $allowance_data['amount'] > 0) {
                    $arr_allowance_amount_total_bpjs += ($allowance_data['amount']);
                    $count_allowance_number += 0;
                    $arr_allowance_amount_number += 0;
                } else {
                    $arr_allowance_amount_total_bpjs += 0;
                    $count_allowance_number += 0;
                    $arr_allowance_amount_number += 0;
                }
                if (strpos($allowance_data['number'], 'TJ TDK TTP') !== false || strpos($allowance_data['number'], 'TJ TETAP') !== false)
                    { $sum_allowance_tj += $allowance_data['amount']; }
            }
            $arr_allowance_number_ex = explode(",", substr($arr_allowance_number, 0, -1));
            $arr_allowance_amount_ex = explode(",", substr($arr_allowance_amount, 0, -1));
            $arr_allowance_combine = array_combine($arr_allowance_number_ex, $arr_allowance_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            //Deduction atau Potongan Gaji
            $q_deduction = $this->db->query("SELECT b.number, b.name, a.amount
                    FROM deductions b
                    LEFT JOIN setup_deductions a ON a.deduction_id = b.id and a.employee_id = '$record[id]'
                    GROUP BY b.id ORDER BY b.name asc");
            $r_deduction = $q_deduction->result_array();
            $arr_deduction_number = "";
            $arr_deduction_amount = "";
            $arr_deduction_amount_total = 0;
            foreach ($r_deduction as $deduction_data) {
                $arr_deduction_number .= strtolower($deduction_data['number']) . ",";
                $arr_deduction_amount .= $deduction_data['amount'] . ",";
                $arr_deduction_amount_total += $deduction_data['amount'];
            }
            $arr_deduction_number_ex = explode(",", substr($arr_deduction_number, 0, -1));
            $arr_deduction_amount_ex = explode(",", substr($arr_deduction_amount, 0, -1));
            $arr_deduction_combine = array_combine($arr_deduction_number_ex, $arr_deduction_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            //BPJS Employee
            //Kalo dia mempunyai BPJS potong gaji BPJS employee
            $r_bpjs_emp = $this->crud->reads('bpjs', ['status' => 0]);
            $arr_bpjs_emp_number = "";
            $arr_bpjs_emp_amount = "";
            $arr_bpjs_emp_amount_total = 0;
            foreach ($r_bpjs_emp as $bpjs_emp_data) {
                $arr_bpjs_emp_number .= strtolower($bpjs_emp_data->number) . "_employee,";
                if ($record['jamsostek'] == "" && $record['jkn'] == "") {
                    $arr_bpjs_emp_amount .= 0 . ",";
                    $arr_bpjs_emp_amount_total += 0;
                } elseif ($record['jamsostek'] == "" && $record['jkn'] != "") {
                    if ($bpjs_emp_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        }
                    } else {
                        $arr_bpjs_emp_amount .= 0 . ",";
                        $arr_bpjs_emp_amount_total += 0;
                    }
                } elseif ($record['jamsostek'] != "" && $record['jkn'] == "") {
                    if ($bpjs_emp_data->number != "BPJS") {
                        // if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100))) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)));
                        // } else {
                        //     $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100))) . ",";
                        //     $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)));
                        // }
                    } else {
                        $arr_bpjs_emp_amount .= 0 . ",";
                        $arr_bpjs_emp_amount_total += 0;
                    }
                } else {
                    if ($bpjs_emp_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        } else {
                            $arr_bpjs_emp_amount .= round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_emp_amount_total += round(($record['bpjs'] * ($bpjs_emp_data->employee / 100)) * $record['jkn_family']);
                        }
                    } else {
                        $arr_bpjs_emp_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100))) . ",";
                        $arr_bpjs_emp_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_emp_data->employee / 100)));
                    }
                }
            }
            $arr_bpjs_emp_number_ex = explode(",", substr($arr_bpjs_emp_number, 0, -1));
            $arr_bpjs_emp_amount_ex = explode(",", substr($arr_bpjs_emp_amount, 0, -1));
            $arr_bpjs_emp_combine = array_combine($arr_bpjs_emp_number_ex, $arr_bpjs_emp_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            //BPJS Company atau  potongan gaji dari perusahaan
            $r_bpjs_com = $this->crud->reads('bpjs', ['status' => 0]);
            $arr_bpjs_com_number = "";
            $arr_bpjs_com_amount = "";
            $arr_bpjs_com_amount_total = 0;
            $arr_bpjs_com_amount_total_ter = 0;
            $arr_bpjs_com_amount_salary_total = 0;
            $arr_bpjs_com_amount_jabatan_total = 0;
            foreach ($r_bpjs_com as $bpjs_com_data) {
                $arr_bpjs_com_number .= strtolower($bpjs_com_data->number) . "_company,";
                if(@$bpjs_com_data->max_salary == 0){
                    $salary = $record['salary'];
                }elseif($record['salary'] > @$bpjs_com_data->max_salary){
                    $salary = @$bpjs_com_data->max_salary;
                }else{
                    $salary = $record['salary'];
                }
                if ($record['jamsostek'] == "" && $record['jkn'] == "") {
                    $arr_bpjs_com_amount .= 0 . ",";
                    $arr_bpjs_com_amount_total += 0;
                    $arr_bpjs_com_amount_total_ter += 0;
                    $arr_bpjs_com_amount_salary_total += 0;
                    $arr_bpjs_com_amount_jabatan_total += 0;
                } elseif ($record['jamsostek'] == "" && $record['jkn'] != "") {
                    if ($bpjs_com_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_total_ter += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_salary_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } elseif ($record['jamsostek'] != "" && $record['jkn'] == "") {
                    if ($bpjs_com_data->number != "BPJS") {
                        //Perhitungan PPH Salary - Jabatan
                        if ($bpjs_com_data->number == "JKK") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JKM") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } elseif ($bpjs_com_data->number == "JHT") {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        } elseif ($bpjs_com_data->number == "JP") {
                            $arr_bpjs_com_amount .= round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += 0;
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        } else {
                            $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += 0;
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_total_ter += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                } else {
                    //Perhitungan PPH Salary - Jabatan
                    if ($bpjs_com_data->number == "JKK") {
                        $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    } elseif ($bpjs_com_data->number == "JKM") {
                        $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_total_ter += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_salary_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    } elseif ($bpjs_com_data->number == "JHT") {
                        $arr_bpjs_com_amount .= round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += round((($record['salary'] + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                    } elseif ($bpjs_com_data->number == "JP") {
                        $arr_bpjs_com_amount .= round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100))) . ",";
                        $arr_bpjs_com_amount_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)));
                    } elseif ($bpjs_com_data->number == "BPJS") {
                        if ($record['bpjs'] == "0") {
                            $arr_bpjs_com_amount .= round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']) . ",";
                            $arr_bpjs_com_amount_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_total_ter += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_salary_total += round((($salary + $arr_allowance_amount_total_bpjs) * ($bpjs_com_data->company / 100)) * $record['jkn_family']);
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        } else {
                            $arr_bpjs_com_amount .= round((($record['bpjs']) * ($bpjs_com_data->company / 100))) . ",";
                            $arr_bpjs_com_amount_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_total_ter += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_salary_total += round((($record['bpjs']) * ($bpjs_com_data->company / 100)));
                            $arr_bpjs_com_amount_jabatan_total += 0;
                        }
                    } else {
                        $arr_bpjs_com_amount .= 0 . ",";
                        $arr_bpjs_com_amount_total += 0;
                        $arr_bpjs_com_amount_total_ter += 0;
                        $arr_bpjs_com_amount_salary_total += 0;
                        $arr_bpjs_com_amount_jabatan_total += 0;
                    }
                }
            }
            $arr_bpjs_com_number_ex = explode(",", substr($arr_bpjs_com_number, 0, -1));
            $arr_bpjs_com_amount_ex = explode(",", substr($arr_bpjs_com_amount, 0, -1));
            $arr_bpjs_com_combine = array_combine($arr_bpjs_com_number_ex, $arr_bpjs_com_amount_ex);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            //Permit Deduction + Amount
            //Mengambil field dan isinya dari permit deduction + total amount 
            $q_permit_type = $this->db->query("SELECT b.number, b.name, SUM(a.duration) as amount, a.reason_id, b.cutoff
                    FROM permit_types b
                    LEFT JOIN permits a ON a.permit_type_id = b.id and a.employee_id = '$record[id]' and a.permit_date >= '$filter_from' and a.permit_date <= '$filter_to'
                    WHERE b.payroll = 'DEDUCTION' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY b.id ORDER BY b.name desc");
            $r_permit_type = $q_permit_type->result_array();
            $arr_permit_type_number = "";
            $arr_permit_type_amount = "";
            $arr_permit_type_number_b = "";
            $arr_permit_type_amount_b = "";
            $arr_permit_type_amount_b_total = 0;
            foreach ($r_permit_type as $permit_type_data) {
                if ($permit_type_data['cutoff'] == "YES") {
                    $arr_permit_type_number_b .= strtolower($permit_type_data['number']) . "b,";
                    $arr_permit_type_amount_b .= ($permit_type_data['amount'] / 2) . ",";
                    $arr_permit_type_amount_qty = ($permit_type_data['amount'] / 2);
                    $arr_permit_type_number .= strtolower($permit_type_data['number']) . "_amount,";
                } else {
                    $arr_permit_type_number_b .= strtolower($permit_type_data['number']) . "b,";
                    $arr_permit_type_amount_b .= $permit_type_data['amount'] . ",";
                    $arr_permit_type_amount_qty = $permit_type_data['amount'];
                    $arr_permit_type_number .= strtolower($permit_type_data['number']) . "_amount,";
                }
                //Untuk mengambil nilai harga deduction rumus nya adalah Gaji / 30 hari x jumlah ijinnya
                // $arr_permit_type_amount .= round(($record['salary'] / 30) * $arr_permit_type_amount_qty) . ",";
                // @$arr_permit_type_amount_b_total += round(($record['salary'] / 30) * $arr_permit_type_amount_qty) . ",";
                // if ($record['group_name'] == "MAGANG") {
                    // $arr_permit_type_amount .= round((($record['salary'] - $arr_deduction_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    // @$arr_permit_type_amount_b_total += round((($record['salary'] - $arr_deduction_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    // $arr_permit_type_amount .= round(($record['salary'] / $hkw) * $arr_permit_type_amount_qty) . ",";
                    // @$arr_permit_type_amount_b_total += round(($record['salary'] / $hkw) * $arr_permit_type_amount_qty) . ",";
                // } elseif ($count_allowance_number > 0) {
                    // $arr_permit_type_amount .= round(($arr_allowance_amount_number / $hkw) * $arr_permit_type_amount_qty) . ",";
                    // @$arr_permit_type_amount_b_total += round(($arr_allowance_amount_number / $hkw) * $arr_permit_type_amount_qty) . ",";
                // } else {
                    // $arr_permit_type_amount .= round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    // @$arr_permit_type_amount_b_total += round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $arr_permit_type_amount_qty) . ",";
                // }
                if ($record['group_name'] == "MAGANG") {
                    $arr_permit_type_amount .= round(($record['salary'] / $hkw) * $arr_permit_type_amount_qty) . ",";
                    @$arr_permit_type_amount_b_total += round(($record['salary'] / $hkw) * $arr_permit_type_amount_qty) . ",";
                } else {
                    $arr_permit_type_amount .= round((($record['salary'] + $sum_allowance_tj) / $hkw) * $arr_permit_type_amount_qty) . ",";
                    @$arr_permit_type_amount_b_total += round((($record['salary'] + $sum_allowance_tj) / $hkw) * $arr_permit_type_amount_qty) . ",";
                }
            }
            $arr_permit_type_number_ex = explode(",", substr($arr_permit_type_number, 0, -1));
            $arr_permit_type_amount_ex = explode(",", substr($arr_permit_type_amount, 0, -1));
            $arr_permit_type_combine = array_combine($arr_permit_type_number_ex, $arr_permit_type_amount_ex);
            $arr_permit_type_number_ex_b = explode(",", substr($arr_permit_type_number_b, 0, -1));
            $arr_permit_type_amount_ex_b = explode(",", substr($arr_permit_type_amount_b, 0, -1));
            $arr_permit_type_combine_b = array_combine($arr_permit_type_number_ex_b, $arr_permit_type_amount_ex_b);
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            //Menghitung Total
            //Potong gaji jika dia ga masuk kerja
            //Rumus nya Gaji / 30 hari x jumlah dia ga absen
            //$absence_qty = (@count($weekday) - @count($holiday) - $masuk - $arr_total_permit);
            $absence_qty = $absen;
            if ($absence_qty > 0) {
                $absence_qty_final = $absence_qty;
            } else {
                $absence_qty_final = 0;
            }
            //Harga Potong Gaji kalo dia ga masuk kerja
            // if ($record['group_name'] == "MAGANG") {
            //     $absence_amount = round(($record['salary'] / $hkw) * $absence_qty_final);
            // } elseif ($count_allowance_number > 0) {
            //     $absence_amount = round(($arr_allowance_amount_number / $hkw) * $absence_qty_final);
            // } else {
            //     $absence_amount = round((($record['salary'] + $arr_allowance_amount_total) / $hkw) * $absence_qty_final);
            // }
            if ($record['group_name'] == "MAGANG") {
                $absence_amount = round(($record['salary'] / $hkw) * $absence_qty_final);
            } else {
                $absence_amount = round((($record['salary'] + $sum_allowance_tj) / $hkw) * $absence_qty_final);
            }
            //Total Pendapatan Gaji (Gaji + Tunjangan + BPJS dari perusahaan + Koreksi plus + lembur hari biasa + lembur hari libur)
            $total_all_allowance = ($record['salary'] + $arr_allowance_amount_total + $arr_bpjs_com_amount_total + @$correction_plus_amount);
            $total_all_allowance_ter = ($record['salary'] + $arr_allowance_amount_total + $arr_bpjs_com_amount_total_ter + @$correction_plus_amount - $absence_amount);
            //Total Potongan Gaji (Ijin/Sakit + Koreksi Minus + alpha)
            $total_all_deduction = ($arr_permit_type_amount_b_total + @$correction_minus_amount + $absence_amount);
            $income = ($total_all_allowance - $total_all_deduction - $arr_bpjs_com_amount_total);
            $income_pajak = ($record['salary'] + $arr_allowance_amount_total);
            //Menghitung PPH Jabatan (Income + JKK + JKM + KES x 5%)
            $pph_position = ((($income_pajak + $arr_bpjs_com_amount_salary_total) * $config[0]->payroll_pph_position) / 100);
            if ($pph_position >= $config[0]->payroll_pph_position_limit) {
                $pph_position_final = $config[0]->payroll_pph_position_limit;
            } else {
                $pph_position_final = $pph_position;
            }
            //Potongan Pajak (Income + JKK + JKM + KES) - (Jabatan + JHT + JP)
            $income_pph = (($income_pajak + $arr_bpjs_com_amount_salary_total) - ($arr_bpjs_com_amount_jabatan_total + $pph_position_final));
            $netto_year = ($income_pph * 12);
            $r_marital = $this->crud->read('maritals', ["number" => $record['marital']]);
            $r_ptkp = $this->crud->read('ptkp', ["marital_id" => @$r_marital->id]);
            $tax_amount = ($netto_year - @$r_ptkp->amount);
            $pph_debt = (($tax_amount * $config[0]->payroll_pph_payable) / 100);
            $pph_pasal = round($pph_debt / 12);
            // die(json_encode(array(
            //     "income" => $income_pph . " (Total Income + BPJS Company - (Jabatan + (Total Income + BPJS Company * 5%)))",
            //     "netto_year" => $netto_year . " (income * 12)",
            //     "ptkp" => $r_ptkp[0]->amount . " (Master PTKP)",
            //     "tax_amount" => $tax_amount . " (netto_year - ptkp)",
            //     "pph_debt" => $pph_debt . " (tax_amount * 5%)",
            //     "hasil_akhir" => $pph_pasal . " (pph_debt / 12)",
            // )));
            if($record['gender'] == "FEMALE"){
                $ter_type = "A";
            }else{
                $ter_type = $record['ter_type'];
            }
            //Rumus TER
            $this->db->select("number, ter");
            $this->db->from('marital_categories');
            $this->db->where('ter_from <', $total_all_allowance_ter);
            $this->db->where('ter_to >', $total_all_allowance_ter);
            $this->db->where('type', $ter_type);
            $marital_category = $this->db->get()->row();
            if(empty($marital_category)){
                $ter_number = "-";
                $ter = 0;
            }else{
                $ter_number = $marital_category->number;
                $ter = (($total_all_allowance_ter * $marital_category->ter) / 100);
            }
            //Kalo gaji per tahun nya lebih dari master ptkp
            if ($netto_year >= @$r_ptkp->amount) {
                //jika npwp nya kosong maka kena potongan 120%
                if ($record['tax_id'] == "" or $record['tax_id'] == null) {
                    $pph_final = round(($pph_pasal * $config[0]->payroll_npwp_null) / 100);
                } else {
                    $pph_final = $pph_pasal;
                }
                //kalo kurang ga kena pajak pph
            } else {
                $pph_final = 0;
            }
            //Total keseluruhan
            $netincome = ($income - $ter - $arr_deduction_amount_total - $arr_bpjs_emp_amount_total - @$loan_cooperative->amount - @$loan_bank->amount - @$loan_other->amount);
            $roundup = 500; // digit pembulatan, dalam hal ini ratusan
            $roundupIncome = ceil($netincome / $roundup) * $roundup;
            //-------------------------------------------------------------------------------------------------------------------------------------------------------
            $arr = array(
                "employee_id" => $record['id'],
                "number" => $record['number'],
                "name" => $record['name'],
                "period_start" => $period_start,
                "period_end" => $period_end,
                "division_name" => $record['division_name'],
                "departement_name" => $record['departement_name'],
                "departement_sub_name" => $record['departement_sub_name'],
                "contract_name" => $record['contract_name'],
                "position_name" => $record['position_name'],
                "group_name" => $record['group_name'],
                "job_type" => $record['type'],
                "level" => $record['level'],
                "marital" => $record['marital'],
                "ter_number" => $ter_number,
                "tax_id" => $record['tax_id'],
                "shift_name" => $record['shift_name_2'],
                "attandance" => json_encode($arr_permit_combine),
                "attandance_wd" => ($masuk),
                "working_day" => @count($weekday_day) - @$calendar_amount,
                "salary" => $record['salary'],
                "allowence" => json_encode($arr_allowance_combine),
                "bpjs_company" => json_encode($arr_bpjs_com_combine),
                "bpjs_company_total" => $arr_bpjs_com_amount_total,
                "correction_plus" => @$correction_plus_amount,
                "overtime_weekday" => 0,
                "overtime_convert_weekday" => 0,
                "overtime_amount_weekday" => 0,
                "overtime_holiday" => 0,
                "overtime_convert_holiday" => 0,
                "overtime_amount_holiday" => 0,
                "overtime_correction" => 0,
                "overtime_convert_correction" => 0,
                "overtime_amount_correction" => 0,
                "total_overtime" => 0,
                "total_overtime_convert" => 0,
                "total_overtime_amount" => 0,
                "total_all_allowance" => $total_all_allowance,
                "deduction_number" => json_encode($arr_permit_type_combine_b),
                "deduction_amount" => json_encode($arr_permit_type_combine),
                "deduction_absence" => ($absence_qty_final),
                "deduction_absence_amount" => $absence_amount,
                "correction_minus" => @$correction_minus_amount,
                "total_all_deduction" => $total_all_deduction,
                "income" => ($income),
                "deduction" => json_encode($arr_deduction_combine),
                "loan_cooperative" => @$loan_cooperative_amount,
                "loan_bank" => @$loan_bank_amount,
                "loan_other" => @$loan_other_amount,
                "bpjs_employee" => json_encode($arr_bpjs_emp_combine),
                "bpjs_employee_total" => $arr_bpjs_emp_amount_total,
                "pph" => ($pph_final),
                "ter" => $ter,
                "net_income" => ($roundupIncome)
            );
            if (@$arr == null) {
                $result = [];
            } else {
                $result = @$arr;
            }
            $checkPayroll = $this->crud->read("payrolls", [], ["employee_id" => $record['id'], "period_start" => $period_start]);
            if (!empty($checkPayroll->employee_id)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Employee", "theme" => "error"));
            } else {
                $send = $this->crud->create('payrolls', $result);
                if ($send) {
                    echo json_encode(array("title" => "Saved", "message" => $record['name'] . " Data has been created", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $record['name'] . " Failed to created", "theme" => "error"));
                }
            }
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
        $send = $this->db->update('payrolls', ["status" => 1]);
        echo $send;
    }
    public function update_unlock()
    {
        $filter_from = $this->input->post('filter_from');
        $period_start = date("Y-m", strtotime($filter_from));
        $this->db->where('period_start', $period_start);
        $send = $this->db->update('payrolls', ["status" => 0]);
        echo $send;
    }
    //DELETE DATA
    public function delete()
    {
        $post = $this->input->post();
        $period_start = date("Y-m", strtotime($post['filter_from']));
        $period_end = date("Y-m", strtotime($post['filter_to']));
        $username = $this->session->username;
        $privileges = $this->crud->reads('privilege_groups', [], ["username" => $username, "status" => "1"]);
        foreach ($privileges as $privilege) {
            $group_id = $privilege->group_id;
            $this->db->select('a.*');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->where('a.period_start', $period_start);
            $this->db->where('a.period_end', $period_end);
            if ($post['filter_division'] != "") {
                $this->db->where('b.division_id', $post['filter_division']);
            }
            if ($post['filter_departement'] != "") {
                $this->db->where('b.departement_id', $post['filter_departement']);
            }
            if ($post['filter_departement_sub'] != "") {
                $this->db->where('b.departement_sub_id', $post['filter_departement_sub']);
            }
            if ($post['filter_employee'] != "") {
                $this->db->where('b.id', $post['filter_employee']);
            }
            if ($post['filter_employee_type'] != "") {
                $this->db->where('b.contract_id', $post['filter_employee_type']);
            }
            if ($post['filter_group'] != "") {
                $this->db->where('b.group_id', $post['filter_group']);
            } else {
                $this->db->where('b.group_id', $group_id);
            }
            $payrolls = $this->db->get()->result_object();
            foreach ($payrolls as $payroll) {
                $this->db->delete("payrolls", ['id' => $payroll->id]);
            }
        }
    }
    public function delete_id()
    {
        $data = $this->input->post();
        // Check if id exists in POST data
        if (!isset($data['id']) || empty($data['id'])) {
            $response = array(
                'status' => 'error',
                'message' => 'ID is required'
            );
            echo json_encode($response);
            return;
        }
        $delete = $this->crud->delete("payrolls", ['id' => $data['id']]);
        if ($delete) {
            $response = array(
                'status' => 'success',
                'message' => 'Data Deleted Successfully'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Failed to delete data'
            );
        }
        echo json_encode($response);
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=payrolls_$format.xls");
        }
        $permit_type = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
        $allowance = $this->crud->reads('allowances', [], [], "", "name", "asc");
        $deduction = $this->crud->reads('deductions', [], [], "", "name", "asc");
        $permit_type_d = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
        $bpjs = $this->crud->reads('bpjs', ['status' => 0]);
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_employee_type = $this->input->get('filter_employee_type');
        $filter_group = $this->input->get('filter_group');
        $filter_status = $this->input->get('filter_status');
        $username = $this->session->username;
        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));
        if($filter_status == "0"){
            $where_status = "AND e.employee_id IS NULL";
        }elseif($filter_status == "1"){
            $where_status = "AND e.employee_id IS NOT NULL";
        }else{
            $where_status = "";
        }
        $query = $this->db->query("SELECT a.*, b.national_id, b.date_sign, b.date_expired, d.name as source_name, e.employee_id as status_resign, e.resign_date, g.number as account_no 
            FROM payrolls a
            JOIN employees b on a.employee_id = b.id
            LEFT JOIN sources d on b.source_id = d.id
            JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
            LEFT JOIN resignations e ON b.id = e.employee_id
            LEFT JOIN account_coa f ON b.division_id = f.division_id and b.departement_id = f.departement_id and b.position_id = f.position_id and b.contract_id = f.contract_id
            LEFT JOIN accounts g ON f.account_id = g.id and g.category = 'payroll'
            WHERE a.period_start = '$period_start' $where_status
            AND a.period_end = '$period_end'
            AND b.division_id LIKE '%$filter_division%'
            AND b.departement_id LIKE '%$filter_departement%'
            AND b.departement_sub_id LIKE '%$filter_departement_sub%'
            AND a.employee_id LIKE '%$filter_employee%'
            AND b.contract_id LIKE '%$filter_employee_type%'
            AND b.group_id LIKE '%$filter_group%'
            GROUP BY a.employee_id
            ORDER BY a.name ASC");
        $records = $query->result_array();
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <style> .str{ mso-number-format:\@; } </style>
                <div style="page-break-after:always;"><center>
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
                </center><br><br>
                <center>
                    <h3 style="margin:0;">Generate Payroll</h3>
                    Period : ' . $filter_from . ' to ' . $filter_to . '
                    <br>
                </center>
                <br>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Employee ID</th>
                <th rowspan="2">Employee Name</th>
                <th rowspan="2">Status</th>
                <th rowspan="2">Division</th>
                <th rowspan="2">Departement</th>
                <th rowspan="2">Departement Sub</th>
                <th rowspan="2">Sign Date</th>
                <th rowspan="2">Expired Date</th>
                <th rowspan="2">Resign Date</th>
                <th rowspan="2">Job Type</th>
                <th rowspan="2">Status</th>
                <th rowspan="2">Source</th>
                <th rowspan="2">Position</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2">Level</th>
                <th rowspan="2">Marital</th>
                <th rowspan="2">TER Code</th>
                <th rowspan="2">Group</th>
                <th rowspan="2">National ID</th>
                <th rowspan="2">NPWP</th>
                <th rowspan="2">Account No</th>
                <th colspan="' . (count($permit_type) + 1) . '">Attandance</th>
                <th rowspan="2">Working Calendar</th>
                <th rowspan="2">Salary</th>
                <th colspan="' . (count($allowance)) . '">Allowence</th>
                <th colspan="' . (count($bpjs)) . '">BPJS Company</th>
                <th rowspan="2">Total BPJS Company</th>
                <th rowspan="2">Correction Plus</th>
                <th colspan="3">Overtime (Weekday)</th>
                <th colspan="3">Overtime (Weekend)</th>
                <th colspan="3">Total Overtime</th>
                <th rowspan="2">Bruto Income</th>
                <th colspan="' . (count($permit_type_d) + count($permit_type_d) + 2) . '">Deduction</th>
                <th rowspan="2">Correction Minus</th>
                <th rowspan="2">Total Deduction</th>
                <th rowspan="2">Total BPJS Company</th>
                <th rowspan="2">Income</th>
                <th colspan="' . (count($deduction)) . '">Deduction</th>
                <th colspan="3">LOAN</th>
                <th colspan="' . (count($bpjs)) . '">BPJS Employee</th>
                <th rowspan="2">Total BPJS Employee</th>
                <th rowspan="2">TER</th>
                <th rowspan="2">Net Income</th>
            </tr>
            <tr>';
        foreach ($permit_type as $permit_type_data) {
            $html .= '<th>' . $permit_type_data->number . '</th>';
        }
        $html .= '<th>WD</th>';
        foreach ($allowance as $allowance_data) {
            $html .= '<th>' . $allowance_data->number . '</th>';
        }
        foreach ($bpjs as $bpjs_data) {
            $html .= '<th>' . $bpjs_data->number . '</th>';
        }
        $html .= '  <th>Day</th>
                    <th>Hour</th>
                    <th>Amount</th>
                    <th>Day</th>
                    <th>Hour</th>
                    <th>Amount</th>
                    <th>Day</th>
                    <th>Hour</th>
                    <th>Amount</th>';
        foreach ($permit_type_d as $permit_type_d_data) {
            $html .= '<th>' . $permit_type_d_data->number . '</th>
                              <th>Amount</th>';
        }
        $html .= '<th>Absence</th>
                          <th>Amount</th>';
        foreach ($deduction as $deduction_data) {
            $html .= '<th>' . $deduction_data->number . '</th>';
        }
        $html .= '<th>Koperasi</th>
                          <th>Bank</th>
                          <th>Other</th>';
        foreach ($bpjs as $bpjs_data) {
            $html .= '<th>' . $bpjs_data->number . '</th>';
        }
        $html .= '</tr>';
        $no = 1;
        foreach ($records as $record) {
            $is_resign = !empty($record['status_resign']);
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td class="str">' . $record['number'] . '</td>
                    <td>' . $record['name'] . '</td>
                    <td style="text-align: center; color: white; background: '.(($is_resign) ? "#FFC41B" : "#53D636").';">' . (($is_resign) ? 'RESIGN' : 'ACTIVE') . '</td>
                    <td>' . $record['division_name'] . '</td>
                    <td>' . $record['departement_name'] . '</td>
                    <td>' . $record['departement_sub_name'] . '</td>
                    <td>' . $record['date_sign'] . '</td>
                    <td>' . $record['date_expired'] . '</td>
                    <td>' . $record['resign_date'] . '</td>
                    <td>' . $record['job_type'] . '</td>
                    <td>' . $record['contract_name'] . '</td>
                    <td>' . $record['source_name'] . '</td>
                    <td>' . $record['position_name'] . '</td>
                    <td>' . $record['shift_name'] . '</td>
                    <td>' . $record['level'] . '</td>
                    <td>' . $record['marital'] . '</td>
                    <td>' . $record['ter_number'] . '</td>
                    <td>' . $record['group_name'] . '</td>
                    <td class="str">' . $record['national_id'] . '</td>
                    <td>' . $record['tax_id'] . '</td>
                    <td>' . $record['account_no'] . '</td>';
            foreach (json_decode($record['attandance'], true) as $attandance => $val_attandance) {
                $html .= '<td>' . $val_attandance . '</td>';
            }
            $html .= '<td>' . $record['attandance_wd'] . '</td>';
            $html .= '<td>' . $record['working_day'] . '</td>';
            $html .= '<td>' . $record['salary'] . '</td>';
            foreach (json_decode($record['allowence'], true) as $allowence => $val_allowence) {
                $html .= '<td>' . $val_allowence . '</td>';
            }
            foreach (json_decode($record['bpjs_company'], true) as $bpjs_company => $val_bpjs_company) {
                $html .= '<td>' . $val_bpjs_company . '</td>';
            }
            $html .= '<td>' . $record['bpjs_company_total'] . '</td>';
            $html .= '<td>' . $record['correction_plus'] . '</td>';
            $html .= '<td>' . $record['overtime_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_convert_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_amount_weekday'] . '</td>';
            $html .= '<td>' . $record['overtime_holiday'] . '</td>';
            $html .= '<td>' . $record['overtime_convert_holiday'] . '</td>';
            $html .= '<td>' . $record['overtime_amount_holiday'] . '</td>';
            $html .= '<td>' . $record['total_overtime'] . '</td>';
            $html .= '<td>' . $record['total_overtime_convert'] . '</td>';
            $html .= '<td>' . $record['total_overtime_amount'] . '</td>';
            $html .= '<td>' . $record['total_all_allowance'] . '</td>';
            $deduction_amount = json_decode($record['deduction_amount'], true);
            $deduction_amount_data = array_values($deduction_amount);
            $index = 0;
            foreach (json_decode($record['deduction_number'], true) as $deduction_number => $val_deduction_number) {
                $html .= '<td>' . $val_deduction_number . '</td>';
                $html .= '<td>' . $deduction_amount_data[$index] . '</td>';
                $index++;
            }
            $html .= '<td>' . $record['deduction_absence'] . '</td>';
            $html .= '<td>' . $record['deduction_absence_amount'] . '</td>';
            $html .= '<td>' . $record['correction_minus'] . '</td>';
            $html .= '<td>' . $record['total_all_deduction'] . '</td>';
            $html .= '<td>' . $record['bpjs_company_total'] . '</td>';
            $html .= '<td>' . $record['income'] . '</td>';
            foreach (json_decode($record['deduction'], true) as $deduction => $val_deduction) {
                $html .= '<td>' . $val_deduction . '</td>';
            }
            $html .= '<td>' . $record['loan_cooperative'] . '</td>';
            $html .= '<td>' . $record['loan_bank'] . '</td>';
            $html .= '<td>' . $record['loan_other'] . '</td>';
            foreach (json_decode($record['bpjs_employee'], true) as $bpjs_employee => $val_bpjs_employee) {
                $html .= '<td>' . $val_bpjs_employee . '</td>';
            }
            $html .= '<td>' . $record['bpjs_employee_total'] . '</td>';
            $html .= '<td>' . $record['ter'] . '</td>';
            $html .= '<td>' . $record['net_income'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function excel()
    {
        if ($this->session->username != "") {
            $sheet = 0;
            $excel = $this->spreadsheet_lib->newSpreadsheet();
            $spreadsheet = $excel->getActiveSheet($sheet);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT ASKARA TEHNO INDONESIA');

            $style_row = [
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN],
                    'left' => ['borderStyle' => Border::BORDER_THIN],
                    'right' => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ];

            $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
            $style_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];

            $style_row_center = array_merge($style_row, $style_center);
            $style_row_right = array_merge($style_row, $style_right);

            $style_row_center_green = array_merge($style_row_center, [
                'font' => ['color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF53D636'],
                ]
            ]);
            $style_row_center_yellow = array_merge($style_row_center, [
                'font' => ['color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFC41B'],
                ]
            ]);

            $style_col = array_merge($style_row, [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ]);

            // $drawing = new Drawing();
            // $drawing->setName('Logo');
            // $drawing->setDescription('Company Logo');
            // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $permit_type = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
            $allowance = $this->crud->reads('allowances', [], [], "", "name", "asc");
            $deduction = $this->crud->reads('deductions', [], [], "", "name", "asc");
            $permit_type_d = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
            $bpjs = $this->crud->reads('bpjs', ['status' => 0]);
            $columns = [
                ['No','Employee ID','Employee Name','Status','Division','Departement','Departement Sub','Sign Date','Expired Date','Resign Date','Job Type','Status','Source','Position','Shift','Level','Marital','TER Code','Group','National ID','NPWP','Account No','Attandance','','Working Calendar','Salary','Allowence','BPJS Company','Total BPJS Company','Correction Plus','Bruto Income','Deduction','Correction Minus','Total Deduction','Total BPJS Company','Income','Deduction','LOAN','BPJS Employee','Total BPJS Employee','TER','Net Income'],
                ['','','','','','','','','','','','','','','','','','','','','','','','WD','','','','','','','','','','','','','','','','','','']
            ];

            $deduction_keys = [];
            $new = [[],[]];
            $position = 1;
            foreach ($columns[0] as $key => $col) {
                if (in_array($col, ['Attandance','Allowence','BPJS Company','Deduction','LOAN','BPJS Employee'])) {
                    $row1 = [];
                    $row2 = [];
                    $datas = [];

                    if ($col == 'Attandance') { $datas = $permit_type; }
                    elseif ($col == 'Allowence') { $datas = $allowance; }
                    elseif (in_array($col, ['BPJS Company','BPJS Employee'])) { $datas = $bpjs; }
                    elseif ($col == 'Deduction' && $position == 1) {
                        foreach ($permit_type_d as $index => $row) {
                            $deduction_keys[] = strtolower($row->number);
                            $datas[] = (object)['number' => $row->number];
                            $datas[] = (object)['number' => 'Amount'];
                        }
                        $datas[] = (object)['number' => 'Absence'];
                        $datas[] = (object)['number' => 'Amount'];
                        $position++;
                    } elseif ($col == 'Deduction') { $datas = $deduction; }
                    elseif ($col == 'LOAN') { $datas = [
                        (object)['number' => 'Koperasi'],
                        (object)['number' => 'Bank'],
                        (object)['number' => 'Other']
                    ]; }

                    foreach ($datas as $index => $row) {
                        array_push($row1, '');
                        array_push($row2, $row->number);
                    }
                    $row1[0] = $col;

                    $new[0][$key] = $row1;
                    $new[1][$key] = $row2;
                }
            }
            krsort($new[0]);
            krsort($new[1]);
            foreach ($new[0] as $key => $value) { array_splice($columns[0], $key, 1, $value); }
            foreach ($new[1] as $key => $value) { array_splice($columns[1], $key, 1, $value); }
            // die(json_encode($columns));

            $last_index = count($columns[0]) - 1;
            $title = 'Generate Payroll';

            $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $row_number = 6;
            foreach ($columns as $index => $row) {
                $start_alfa = '';
                $end_alfa = '';
                $alfa = 'A';

                foreach ($row as $key => $value) {
                    if ($index == 0 && $key == $last_index) {
                        $spreadsheet->setCellValue('A1', $title);
                        $spreadsheet->mergeCells('A1:'.$alfa.'1');
                        $spreadsheet->getStyle('A1')->applyFromArray($style_center);
                        $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                        $spreadsheet->setCellValue($alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                        $spreadsheet->setCellValue($alfa.'4', 'Print By : ' . $fullname);
                    }

                    $spreadsheet->setCellValue($alfa.$row_number, $value);
                    $spreadsheet->getStyle($alfa.$row_number)->applyFromArray($style_col);

                    if ($index == 0) {
                        if (!empty($value)) {
                            if (!empty($end_alfa)) { $spreadsheet->mergeCells($start_alfa.$row_number.':'.$end_alfa.$row_number); }

                            $start_alfa = $alfa;
                            $end_alfa = '';
                        } else { $end_alfa = $alfa; }
                    } elseif (empty($value)) { $spreadsheet->mergeCells($alfa.($row_number - 1).':'.$alfa.$row_number); }

                    $alfa++;
                }
                $row_number++;
            }

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $filter_status = $this->input->get('filter_status');

            $username = $this->session->username;
            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $where_status = "";
            if ($filter_status == "0") { $where_status = "AND e.employee_id IS NULL"; }
            elseif ($filter_status == "1") { $where_status = "AND e.employee_id IS NOT NULL"; }

            $query = $this->db->query("SELECT a.*, b.national_id, b.date_sign, b.date_expired, d.name as source_name, e.employee_id as status_resign, e.resign_date, g.number as account_no 
            FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                LEFT JOIN resignations e ON b.id = e.employee_id
                LEFT JOIN account_coa f ON b.division_id = f.division_id and b.departement_id = f.departement_id and b.position_id = f.position_id and b.contract_id = f.contract_id
                LEFT JOIN accounts g ON f.account_id = g.id and g.category = 'payroll'
            WHERE a.period_start = '$period_start' $where_status
                AND a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.employee_id LIKE '%$filter_employee%'
                AND b.contract_id LIKE '%$filter_employee_type%'
                AND b.group_id LIKE '%$filter_group%'
            GROUP BY a.employee_id
            ORDER BY a.name ASC");
            $datas = [];
            $right_cell_keys = [];
            $amount_groups = [];
            foreach ($query->result() as $index => $row) {
                if (!isset($amount_groups[$row->group_name])) { $amount_groups[$row->group_name] = 0; }
                $amount_groups[$row->group_name]++;

                $data = [
                    "number" => $row->number,
                    "name" => $row->name,
                    "status_resign" => (!empty($row->status_resign)) ? 'RESIGN' : 'ACTIVE',
                    "division_name" => $row->division_name,
                    "departement_name" => $row->departement_name,
                    "departement_sub_name" => $row->departement_sub_name,
                    "date_sign" => $row->date_sign,
                    "date_expired" => $row->date_expired,
                    "resign_date" => $row->resign_date,
                    "job_type" => $row->job_type,
                    "contract_name" => $row->contract_name,
                    "source_name" => $row->source_name,
                    "position_name" => $row->position_name,
                    "shift_name" => $row->shift_name,
                    "level" => $row->level,
                    "marital" => $row->marital,
                    "ter_number" => $row->ter_number,
                    "group_name" => $row->group_name,
                    "national_id" => $row->national_id,
                    "tax_id" => $row->tax_id,
                    "account_no" => $row->account_no
                ];
                foreach (json_decode($row->attandance) as $key => $value) {
                    $right_cell_keys[] = $key;
                    $data[$key] = number_format($value, 0, '', ',');
                }
                $data['attandance_wd'] = $row->attandance_wd;
                $data['working_day'] = $row->working_day;
                $data['salary'] = number_format($row->salary, 0, '', ',');
                foreach (json_decode($row->allowence) as $key => $value) {
                    $right_cell_keys[] = $key;
                    $data[$key] = number_format($value, 0, '', ',');
                }
                foreach (json_decode($row->bpjs_company) as $key => $value) {
                    $right_cell_keys[] = $key;
                    $data[$key] = number_format($value, 0, '', ',');
                }
                $data['bpjs_company_total'] = number_format($row->bpjs_company_total, 0, '', ',');
                $data['correction_plus'] = number_format($row->correction_plus, 0, '', ',');
                $data['total_all_allowance'] = number_format($row->total_all_allowance, 0, '', ',');
                $deduction_data = json_decode($row->deduction_number, true);
                $amount_data = json_decode($row->deduction_amount, true);
                foreach ($deduction_keys as $key => $value) {
                    $right_cell_keys[] = $value;
                    $right_cell_keys[] = $value.'_amount';
                    $data[$value] = (empty($deduction_data[$value.'b'])) ? 0 : number_format($deduction_data[$value.'b'] ?? '0', 0, '', ',');
                    $data[$value.'_amount'] = (empty($amount_data[$value.'_amount'])) ? 0 : number_format($amount_data[$value.'_amount'] ?? '0', 0, '', ',');
                }
                $data['deduction_absence'] = number_format($row->deduction_absence, 0, '', ',');
                $data['deduction_absence_amount'] = number_format($row->deduction_absence_amount, 0, '', ',');
                $data['correction_minus'] = number_format($row->correction_minus, 0, '', ',');
                $data['total_all_deduction'] = number_format($row->total_all_deduction, 0, '', ',');
                $data['bpjs_company_total_2'] = number_format($row->bpjs_company_total, 0, '', ',');
                $data['income'] = number_format($row->income, 0, '', ',');
                foreach (json_decode($row->deduction) as $key => $value) {
                    $right_cell_keys[] = $key;
                    $data[$key] = number_format(((empty($value)) ? '0' : $value), 0, '', ',');
                }
                $data['loan_cooperative'] = number_format($row->loan_cooperative, 0, '', ',');
                $data['loan_bank'] = number_format($row->loan_bank, 0, '', ',');
                $data['loan_other'] = number_format($row->loan_other, 0, '', ',');
                foreach (json_decode($row->bpjs_employee) as $key => $value) {
                    $right_cell_keys[] = $key;
                    $data[$key] = number_format($value, 0, '', ',');
                }
                $data['bpjs_employee_total'] = number_format($row->bpjs_employee_total, 0, '', ',');
                $data['ter'] = number_format($row->ter, 0, '', ',');
                $data['net_income'] = number_format($row->net_income, 0, '', ',');

                array_push($datas, json_decode(json_encode($data)));
            }
            // die(json_encode($datas));
            $row_no = $row_number;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if ($key == 'status_resign') { $style = ($value == 'ACTIVE') ? $style_row_center_green : $style_row_center_yellow; }
                    if (in_array($key, ['attandance_wd','working_day'])) { $style = $style_row_center; }
                    if (in_array($key, array_merge(['salary','bpjs_company_total','correction_plus','total_all_allowance','deduction_absence','deduction_absence_amount','correction_minus','total_all_deduction','bpjs_company_total_2','income','loan_cooperative','loan_bank','loan_other','bpjs_employee_total','ter','net_income'],$right_cell_keys)))
                        { $style = $style_row_right; }

                    if (in_array($key, ['number','national_id','tax_id']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $sheet++;
            $spreadsheet = $excel->createSheet();
            $excel->setActiveSheetIndex($sheet);
            $spreadsheet->setTitle('Accumulation Amount');

            $columns = ['No', 'Group', 'Total Amount'];

            $alfa = 'A';
            $row_no = 1;
            foreach ($columns as $key => $value) {
                $spreadsheet->setCellValue($alfa.$row_no, $value);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

                $alfa++;
            }

            $datas = $this->db->distinct()->select("TRIM(name) AS name, 0 AS amount")->from("groups")->order_by("name")->get()->result();

            $total = 0;
            $row_no++;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    if ($key == 'amount') {
                        if (isset($amount_groups[$row->name])) {
                            $value = $amount_groups[$row->name];
                            $total += floatval($value);
                        }
                    }

                    $style = $style_row;
                    if (in_array($key, [''])) { $style = $style_row_center; }
                    if (in_array($key, ['amount'])) { $style = $style_row_right; }

                    if (in_array($key, ['']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                }

                $row_no++;
            }

            $spreadsheet->setCellValue("A".$row_no, "Grand Total");
            $spreadsheet->setCellValue("C".$row_no, $total);

            $spreadsheet->mergeCells("A".$row_no.":B".$row_no);
            $spreadsheet->getStyle("A".$row_no)->applyFromArray($style_col);
            $spreadsheet->getStyle("B".$row_no)->applyFromArray($style_col);
            $spreadsheet->getStyle("C".$row_no)->applyFromArray(array_merge($style_row_right, ["font" => ["bold" => true]]));

            for ($i='A'; $i <= $alfa; $i++) { $spreadsheet->getColumnDimension($i)->setAutoSize(true); }

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) { ob_end_clean(); }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.date(' (Y-m-d)').'.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = IOFactory::createWriter($excel, 'Xlsx');
            $writer->save('php://output');
            exit;
        }
    }

    public function printReport($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=payroll_report_$format.xls");
        }

        $permit_type = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
        $allowance = $this->crud->reads('allowances', [], [], "", "name", "asc");
        $deduction = $this->crud->reads('deductions', [], [], "", "name", "asc");
        $permit_type_d = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
        $bpjs = $this->crud->reads('bpjs', ['status' => 0], [], "", "", "", ["number"]);

        $id_allowances = [];
        foreach ($allowance as $index => $row) { $id_allowances[strtolower(trim($row->number))] = $row->id; }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_employee_type = $this->input->get('filter_employee_type');
        $filter_group = $this->input->get('filter_group');
        $filter_status = $this->input->get('filter_status');
        $username = $this->session->username;
        $filter_group_ex = explode(",", trim($filter_group));

        $period_start = date("Y-m", strtotime($filter_from));
        $period_end = date("Y-m", strtotime($filter_to));

        if($filter_status == "0"){
            $where_status = "AND e.employee_id IS NULL";
        }elseif($filter_status == "1"){
            $where_status = "AND e.employee_id IS NOT NULL";
        }else{
            $where_status = "";
        }

        if ($username == "admin") {
            $privileges = "";
        } else {
            $privileges = "AND c.status = '1'";
        }

        if($filter_group != ""){
            $filter_group_ex = array_map(function($v) {
                return $this->db->escape($v);
            }, $filter_group_ex);

            $where_group = "AND b.group_id IN (" . implode(',', $filter_group_ex) . ")";
        } else {
            $where_group = "";
        }

        if($filter_employee != ""){
            $employee = " AND a.employee_id LIKE '%$filter_employee%'";
        } else {
            $employee = " AND ( r.resign_date IS NULL OR r.resign_date >= '$filter_from' ) ";
        }

        $q = "SELECT a.*, 0 AS tax_allowance, 0 AS overtime_saturday, 0 AS overtime_amount_saturday, 0 AS overtime_sunday, 0 AS overtime_amount_sunday, b.national_id, d.name as source_name, e.employee_id as status_resign, g.number as account_no,
        coalesce(p.bpjs, 0) as bpjs_rate, b.group_id, 0 as bpjs_kes_rate, r.resign_date
        FROM payrolls a
            JOIN employees b on a.employee_id = b.id
            LEFT JOIN sources d on b.source_id = d.id
            JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' $privileges
            LEFT JOIN resignations e ON b.id = e.employee_id
            LEFT JOIN account_coa f ON b.division_id = f.division_id and b.departement_id = f.departement_id and b.position_id = f.position_id and b.contract_id = f.contract_id
            LEFT JOIN accounts g ON f.account_id = g.id and g.category = 'payroll'
            LEFT JOIN setup_salaries p ON p.employee_id = b.id
            LEFT JOIN resignations r ON r.employee_id = b.id
            WHERE a.period_start = '$period_start' $where_status
            AND a.period_end = '$period_end'
            AND b.division_id LIKE '%$filter_division%'
            AND b.departement_id LIKE '%$filter_departement%'
            AND b.departement_sub_id LIKE '%$filter_departement_sub%'
            $employee
            AND b.contract_id LIKE '%$filter_employee_type%'
            $where_group
            GROUP BY a.employee_id
            ORDER BY a.name ASC";
        // die($q);
        $query = $this->db->query($q);
        $records = $query->result_array();

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $group_id = null;
        $hlGroups = ['20221213000002', '20221213000003'];

        $filter_group_ex = array_map(function($v) {
            return trim($v, "'");
        }, $filter_group_ex);
        $is_hl = 0;
        if ($filter_employee) {
            $femployee = $this->crud->read('employees', [], ['id' => $filter_employee]);
            $group_id  = $femployee->group_id ?? null;
            $is_hl = in_array($group_id, $hlGroups, true);
        } else {
            $is_hl = array_intersect($filter_group_ex, $hlGroups);
        }

        $filterGroupAll = 1;
        if ($filter_group) {
            $filterGroupAll = 0;
        }

        $dynamic_allowances = [];
        $excluded_allowance_numbers = [
            '20230217000001', // Tunjangan Tetap
            '20230222000001', // Tunjangan Jabatan
            '20230117000003', // Tunjangan Kinerja
            '20221213000006', // Uang Makan 1
            '20250916000001', // Uang Makan 2
            '20221213000007'  // Uang Makan 3
        ];
        $excluded_allowance_names = [
            'tunjangan tetap',
            'tunjangan jabatan',
            'tunjangan kinerja',
            'tunjangan transport'
        ];
        $id_tunjangan_tetap = null;
        $id_tunjangan_jabatan = null;
        $id_tunjangan_kinerja = null;
        foreach ($allowance as $row) {
            $num = strtolower(trim($row->number));
            $name = strtolower(trim($row->name));
            if ($num == '20230217000001' || $name == 'tunjangan tetap') { $id_tunjangan_tetap = $row->id; }
            if ($num == '20230222000001' || $name == 'tunjangan jabatan') { $id_tunjangan_jabatan = $row->id; }
            if ($num == '20230117000003' || $name == 'tunjangan kinerja') { $id_tunjangan_kinerja = $row->id; }
            if (in_array($num, $excluded_allowance_numbers) || in_array($name, $excluded_allowance_names)) {
                continue;
            }
            $dynamic_allowances[] = $row;
        }

        $th = "";
        if ($is_hl || $filterGroupAll) {
            $th .= " <th>Gaji Harian</th>
                    <th>Uang Makan</th>";
        }
        $th .= "
            <th>Basic Salary</th>";
        foreach ($dynamic_allowances as $row) {
            $th .= "
            <th>" . $row->name . "</th>";
        }
        $th .= "
            <th>Tunjangan Tetap</th>
            <th>Tunjangan Jabatan</th>
            <th>Tunjangan Kinerja</th>
            <th>Tunjangan Transport</th>
        ";

        if ($filter_division) {
            $division = $this->crud->read('divisions', [], ['id' => $filter_division]);
            $division_name = $division ? $division->name : '';
            $division_logo = $division ? base_url('assets/image/division/' . $division->logo) : '';
        } else {
            $division_name = $config->name;
            $division_logo = $config->favicon;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}#ttd {border-collapse: collapse;width: 100%;font-size: 10px;}#ttd td, #ttd th {padding: 2px;}</style><body>
                <style> .str{ mso-number-format:\@; } </style>
                <div style="page-break-after:always;"><center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . $division_logo . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $division_name . '</b><br>
                                    <small>' . $config->description . '</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                </center><br><br>
                <center>
                    <h3 style="margin:0;">Generate Payroll</h3>
                    Period : ' . $filter_from . ' to ' . $filter_to . '
                    <br>
                </center>
                <br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Status</th>
                <th>No. KTP</th>
                <th>Status PTKP</th>
                <th>Job Position</th>
                <th>Organization</th>
                '.$th.'
                <th>Correction Plus</th>
                <th>Total Overtime</th>
                <th>Tax Allowance</th>
                <th>Total Allowance</th>
                <th>Potongan Absen</th>
                <th>Potongan Lain-Lain</th>
                <th>Jaminan Pensiun Employee</th>
                <th>BPJSK Employee</th>
                <th>JHT Employee</th>
                <th>PPH 21</th>
                <th>Total Deduction</th>
                <th>PPH 21 Payment</th>
                <th>Take Home Pay</th>
                <th>BPJS TK Rate</th>
                <th>BPJS Kes Rate</th>
            </tr>';

        $no = 1;
        $sum_total_correction_plus = 0;
        $sum_total_overtime       = 0;
        $sum_tax_allowance        = 0;
        $sum_total_all_allowance  = 0;
        $sum_deduction_absence    = 0;
        $sum_potongan_lain        = 0;
        $sum_jp_emp               = 0;
        $sum_bpjs_emp             = 0;
        $sum_jht_emp              = 0;
        $sum_ter                  = 0;
        $sum_total_all_deduction  = 0;
        $sum_net_income           = 0;
        $sum_bpjs_rate            = 0;
        $sum_bpjs_kes_rate        = 0;
        // total untuk th2
        $sum_salary              = 0;
        $sum_allowances          = array_fill(0, count($dynamic_allowances), 0);
        $sum_tunjangan_tetap     = 0;
        $sum_tunjangan_jabatan   = 0;
        $sum_tunjangan_kinerja   = 0;
        $sum_tunjangan_transport = 0;

        $sum_gaji_harian = 0;
        $sum_uang_makan  = 0;


        foreach ($records as $record) {
            $status = "ACTIVE";
            if ($record['resign_date']) {
                // cek resign dalam periode gaji
                $rstart = new DateTime($filter_from);
                $rend   = (new DateTime($filter_to));
                $rdate = new DateTime($record['resign_date']);

                if ($rdate >= $rstart && $rdate <= $rend) {
                    $status = 'RESIGN';
                }
            }
            $is_hl_emp = in_array($record['group_id'], $hlGroups, true);
            $all_deduction = [];
            foreach (json_decode($record['deduction']) as $key => $value) { $all_deduction[] = $value; }
            $potongan_lain = $record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'] + array_sum($all_deduction);
            $allowances = json_decode($record['allowence'], true);
            $th2 = "";
            $arr_allowance = [];
            foreach ($allowances as $key => $value) {
                $arr_allowance[(isset($id_allowances[$key])) ? $id_allowances[$key] : $key] = floatval($value);
            }
            // die(json_encode($arr_allowance));
            if ($is_hl || $filterGroupAll) {
                $gaji_harian = 0;
                // ambil nilai uang makan
                $uang_makan = 0;
                if (isset($arr_allowance['20221213000006'])) { $uang_makan = $arr_allowance['20221213000006']; }
                if (isset($arr_allowance['20250916000001']) && empty($uang_makan)) { $uang_makan = $arr_allowance['20250916000001']; }
                if (isset($arr_allowance['20221213000007']) && empty($uang_makan)) { $uang_makan = $arr_allowance['20221213000007']; }

                if (floatval($record['salary']) == 0) {
                    // hitung total selain uang makan
                    foreach ($arr_allowance as $key => $value) {
                        if (in_array($key, ['20221213000006','20250916000001','20221213000007'])) continue; // skip uang makan
                        $gaji_harian += (float) $value;
                    }
                }
                // if (!$is_hl_emp) {
                //     $gaji_harian = 0;
                //     $uang_makan = 0;
                // }
                if ($option === 'excel') {
                    $th2 .= '
                        <td style="mso-number-format:&quot;#,##0&quot;">'. round($gaji_harian, 0) .'</td>
                        <td style="mso-number-format:&quot;#,##0&quot;">'. round($uang_makan, 0) .'</td>
                    ';
                } else {
                    $th2 .= '
                        <td>'. number_format($gaji_harian, 0, ".", ",") .'</td>
                        <td>'. number_format($uang_makan, 0, ".", ",") .'</td>
                    ';
                }

                $sum_gaji_harian += $gaji_harian;
                $sum_uang_makan  += $uang_makan;
            }
                $tunjangan_arr = [];
                foreach ($dynamic_allowances as $row) {
                    $val = 0;
                    if (isset($arr_allowance[$row->id])) {
                        $val = $arr_allowance[$row->id];
                    } elseif (isset($arr_allowance[strtolower(trim($row->number))])) {
                        $val = $arr_allowance[strtolower(trim($row->number))];
                    } elseif (isset($arr_allowance[$row->number])) {
                        $val = $arr_allowance[$row->number];
                    }
                    $tunjangan_arr[] = $val;
                }

                $tunjangan_tetap = 0;
                if ($id_tunjangan_tetap && isset($arr_allowance[$id_tunjangan_tetap])) {
                    $tunjangan_tetap = $arr_allowance[$id_tunjangan_tetap];
                } elseif (isset($arr_allowance['20230217000001'])) {
                    $tunjangan_tetap = $arr_allowance['20230217000001'];
                }

                $tunjangan_jabatan = 0;
                if ($id_tunjangan_jabatan && isset($arr_allowance[$id_tunjangan_jabatan])) {
                    $tunjangan_jabatan = $arr_allowance[$id_tunjangan_jabatan];
                } elseif (isset($arr_allowance['20230222000001'])) {
                    $tunjangan_jabatan = $arr_allowance['20230222000001'];
                }

                $tunjangan_kinerja = 0;
                if ($id_tunjangan_kinerja && isset($arr_allowance[$id_tunjangan_kinerja])) {
                    $tunjangan_kinerja = $arr_allowance[$id_tunjangan_kinerja];
                } elseif (isset($arr_allowance['20230117000003'])) {
                    $tunjangan_kinerja = $arr_allowance['20230117000003'];
                }

                $tunjangan_transport = 0;

                $value_cols = [];
                if ($option === 'excel') {
                    $value_cols[] = round($record['salary'], 0);
                    foreach ($tunjangan_arr as $val) {
                        $value_cols[] = round($val, 0);
                    }
                    $value_cols[] = round($tunjangan_tetap, 0);
                    $value_cols[] = round($tunjangan_jabatan, 0);
                    $value_cols[] = round($tunjangan_kinerja, 0);
                    $value_cols[] = round($tunjangan_transport, 0);
                } else {
                    $value_cols[] = number_format($record['salary'], 0, '.', ',');
                    foreach ($tunjangan_arr as $val) {
                        $value_cols[] = number_format($val, 0, '.', ',');
                    }
                    $value_cols[] = number_format($tunjangan_tetap, 0, '.', ',');
                    $value_cols[] = number_format($tunjangan_jabatan, 0, '.', ',');
                    $value_cols[] = number_format($tunjangan_kinerja, 0, '.', ',');
                    $value_cols[] = number_format($tunjangan_transport, 0, '.', ',');
                }

                foreach ($value_cols as $key => $value) {
                    $style = "";
                    if ($option === 'excel') { $style = 'style="mso-number-format:&quot;#,##0&quot;"'; }
                    $th2 .= '<td '.$style.'>'. $value .'</td>';
                }

                $sum_salary              += $record['salary'];
                $sum_tunjangan_tetap     += $tunjangan_tetap;
                $sum_tunjangan_jabatan   += $tunjangan_jabatan;
                $sum_tunjangan_kinerja   += $tunjangan_kinerja;
                $sum_tunjangan_transport += $tunjangan_transport;
                foreach ($tunjangan_arr as $key => $value) { $sum_allowances[$key] += $value; }

            // if ($record['ter'] < 0 || $record['tax_allowance'] == 0) {
                $tax_allowance = 0;
            // } else {
            //     $tax_allowance = $record['ter'];
            // }
            $total_overtime = $record['overtime_amount_weekday'] + $record['overtime_amount_saturday'] + $record['overtime_amount_sunday'] + $record['overtime_amount_holiday'];

            $corr_plus = $record['correction_plus'];

            // if ($is_hl_emp) {
            //     $total_all_allowance = $tax_allowance + $total_overtime + $gaji_harian + $corr_plus;
            // } else {
                $total_all_allowance = $tax_allowance + array_sum($tunjangan_arr) + $tunjangan_tetap + $tunjangan_jabatan + $tunjangan_kinerja + $total_overtime + $corr_plus;
            // }

            $bpjs_employee = json_decode($record['bpjs_employee'], true);
            $jp_emp = $bpjs_employee['jp_employee'] ?? 0;
            $bpjs_emp = $bpjs_employee['bpjs_employee'] ?? 0;
            $jht_emp = $bpjs_employee['jht_employee'] ?? 0;

            $total_all_deduction = $record['total_all_deduction'] + $potongan_lain + $jp_emp + $bpjs_emp + $jht_emp + $record['ter'];

            if ($option === 'excel') {
                // ===== EXCEL (numeric, bisa dihitung) =====
                $td_number = '
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($corr_plus, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($total_overtime, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($tax_allowance, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($total_all_allowance, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["total_all_deduction"], 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($potongan_lain, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($jp_emp, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($bpjs_emp, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($jht_emp, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["ter"], 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($total_all_deduction, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["ter"], 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["net_income"], 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["bpjs_rate"], 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($record["bpjs_kes_rate"], 0) .'</td>
                ';
            } else {
                // ===== PDF (tampilan pakai koma) =====
                $td_number = '
                    <td>'. number_format($corr_plus, 0, ".", ",") .'</td>
                    <td>'. number_format($total_overtime, 0, ".", ",") .'</td>
                    <td>'. number_format($tax_allowance, 0, ".", ",") .'</td>
                    <td>'. number_format($total_all_allowance, 0, ".", ",") .'</td>
                    <td>'. number_format($record["total_all_deduction"], 0, ".", ",") .'</td>
                    <td>'. number_format($potongan_lain, 0, ".", ",") .'</td>
                    <td>'. number_format($jp_emp, 0, ".", ",") .'</td>
                    <td>'. number_format($bpjs_emp, 0, ".", ",") .'</td>
                    <td>'. number_format($jht_emp, 0, ".", ",") .'</td>
                    <td>'. number_format($record["ter"], 0, ".", ",") .'</td>
                    <td>'. number_format($total_all_deduction, 0, ".", ",") .'</td>
                    <td>'. number_format($record["ter"], 0, ".", ",") .'</td>
                    <td>'. number_format($record["net_income"], 0, ".", ",") .'</td>
                    <td>'. number_format($record["bpjs_rate"], 0, ".", ",") .'</td>
                    <td>'. number_format($record["bpjs_kes_rate"], 0, ".", ",") .'</td>
                ';
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td class="str">' . $record['number'] . '</td>
                    <td>' . $record['name'] . '</td>
                    <td>' . $status . '</td>
                    <td class="str">' . $record['national_id'] . '</td>
                    <td>' . $record['marital'] . '</td>
                    <td>' . $record['position_name'] . '</td>
                    <td>' . $record['departement_sub_name'] . '</td>
                    '.$th2.'
                    '.$td_number.'
                    ';
            $no++;
            $sum_total_correction_plus += $corr_plus;
            $sum_total_overtime      += $total_overtime;
            $sum_tax_allowance       += $tax_allowance;
            $sum_total_all_allowance += $total_all_allowance;
            $sum_deduction_absence   += $record['total_all_deduction'];
            $sum_potongan_lain       += $potongan_lain;
            $sum_jp_emp              += $jp_emp;
            $sum_bpjs_emp            += $bpjs_emp;
            $sum_jht_emp             += $jht_emp;
$sum_ter                 += $record['ter'];
            $sum_total_all_deduction += $total_all_deduction;
            $sum_net_income          += $record['net_income'];
            $sum_bpjs_rate           += $record['bpjs_rate'];
            $sum_bpjs_kes_rate       += $record['bpjs_kes_rate'];
        }
        $th2_total = "";
        if ($is_hl || $filterGroupAll) {
            if ($option === 'excel') {
                $th2_total .= '
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_gaji_harian, 0) .'</td>
                    <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_uang_makan, 0) .'</td>
                ';
            } else {
                $th2_total .= '
                    <td>'. number_format($sum_gaji_harian, 0, ".", ",") .'</td>
                    <td>'. number_format($sum_uang_makan, 0, ".", ",") .'</td>
                ';
            }
        }

        $sum_value_cols = [];
        if ($option === 'excel') {
            $sum_value_cols[] = round($sum_salary, 0);
            foreach ($sum_allowances as $val) {
                $sum_value_cols[] = round($val, 0);
            }
            $sum_value_cols[] = round($sum_tunjangan_tetap, 0);
            $sum_value_cols[] = round($sum_tunjangan_jabatan, 0);
            $sum_value_cols[] = round($sum_tunjangan_kinerja, 0);
            $sum_value_cols[] = round($sum_tunjangan_transport, 0);
        } else {
            $sum_value_cols[] = number_format($sum_salary, 0, ".", ",");
            foreach ($sum_allowances as $val) {
                $sum_value_cols[] = number_format($val, 0, ".", ",");
            }
            $sum_value_cols[] = number_format($sum_tunjangan_tetap, 0, ".", ",");
            $sum_value_cols[] = number_format($sum_tunjangan_jabatan, 0, ".", ",");
            $sum_value_cols[] = number_format($sum_tunjangan_kinerja, 0, ".", ",");
            $sum_value_cols[] = number_format($sum_tunjangan_transport, 0, ".", ",");
        }
        foreach ($sum_value_cols as $key => $value) {
            $style = "";
            if ($option === 'excel') { $style = 'style="mso-number-format:&quot;#,##0&quot;"'; }
            $th2_total .= '<td '.$style.'>'. $value .'</td>';
        }

        if ($option === 'excel') {
            $html .= '
            <tr style="font-weight:bold; background:#f0f0f0;">
                <td colspan="8" align="center">TOTAL</td>
                '.$th2_total.'
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_total_correction_plus,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_total_overtime,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_tax_allowance,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_total_all_allowance,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_deduction_absence,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_potongan_lain,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_jp_emp,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_bpjs_emp,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_jht_emp,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_ter,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_total_all_deduction,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_ter,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_net_income,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_bpjs_rate,0) .'</td>
                <td style="mso-number-format:&quot;#,##0&quot;">'. round($sum_bpjs_kes_rate,0) .'</td>
            </tr>';
        } else {
            $html .= '
            <tr style="font-weight:bold; background:#f0f0f0;">
                <td colspan="8" align="center">TOTAL</td>
                '.$th2_total.'
                <td>'. number_format($sum_total_correction_plus,0,'.',',') .'</td>
                <td>'. number_format($sum_total_overtime,0,'.',',') .'</td>
                <td>'. number_format($sum_tax_allowance,0,'.',',') .'</td>
                <td>'. number_format($sum_total_all_allowance,0,'.',',') .'</td>
                <td>'. number_format($sum_deduction_absence,0,'.',',') .'</td>
                <td>'. number_format($sum_potongan_lain,0,'.',',') .'</td>
                <td>'. number_format($sum_jp_emp,0,'.',',') .'</td>
                <td>'. number_format($sum_bpjs_emp,0,'.',',') .'</td>
                <td>'. number_format($sum_jht_emp,0,'.',',') .'</td>
                <td>'. number_format($sum_ter,0,'.',',') .'</td>
                <td>'. number_format($sum_total_all_deduction,0,'.',',') .'</td>
                <td>'. number_format($sum_ter,0,'.',',') .'</td>
                <td>'. number_format($sum_net_income,0,'.',',') .'</td>
                <td>'. number_format($sum_bpjs_rate,0,'.',',') .'</td>
                <td>'. number_format($sum_bpjs_kes_rate,0,'.',',') .'</td>
            </tr>';
        }

        $html .= '</table><br></br>';

        $colspan_ttd = 8 + ($is_hl || $filterGroupAll ? 2 : 0) + 5 + count($dynamic_allowances) + 15 - 2;
        $html .= '
            <table id="ttd">
                <tr>
                    <th style="width: 62%;" colspan="' . $colspan_ttd . '"></th>
                    <th colspan="2" style="width: 10%; border: 1px solid #ddd;">Approval By</th>
                </tr>
                <tr>
                    <th style="width: 62%; height: 65px;" colspan="' . $colspan_ttd . '"></th>
                    <th style="height: 65px; width: 13%; border: 1px solid #ddd;"></th>
                    <th style="height: 65px; width: 13%; border: 1px solid #ddd;"></th>
                </tr>
                <tr>
                    <th style="width: 62%;" colspan="' . $colspan_ttd . '"></th>
                    <th style="width: 10%; border: 1px solid #ddd;">Rendhika Harsono</th>
                    <th style="width: 10%; border: 1px solid #ddd;">Askara Harsono</th>
                </tr>
            </table>
            ';
        $html .= '</body></html>';
        
        echo $html;
    }

    public function excelReport()
    {
        if ($this->session->username != "") {
            $sheet = 0;
            $excel = $this->spreadsheet_lib->newSpreadsheet();
            $spreadsheet = $excel->getActiveSheet($sheet);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT ASKARA TEHNO INDONESIA');

            $style_row = [
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
            $style_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];
            $style_row_center = array_merge($style_row, $style_center);
            $style_row_right = array_merge($style_row, $style_right);

            $style_row_ttd = array_merge($style_row, $style_center);
            $style_row_ttd['borders']['allBorders']['color'] = ['argb' => 'FFDDDDDD'];

            $style_col = array_merge($style_row, [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ]);

            // $drawing = new Drawing();
            // $drawing->setName('Logo');
            // $drawing->setDescription('Company Logo');
            // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $permit_type = $this->crud->reads('permit_types', [], ['payroll' => 'NON DEDUCTION']);
            $allowance = $this->crud->reads('allowances', [], [], "", "name", "asc");
            $deduction = $this->crud->reads('deductions', [], [], "", "name", "asc");
            $permit_type_d = $this->crud->reads('permit_types', [], ['payroll' => 'DEDUCTION']);
            $bpjs = $this->crud->reads('bpjs', ['status' => 0], [], "", "", "", ["number"]);

            $id_allowances = [];
            foreach ($allowance as $index => $row) { $id_allowances[strtolower(trim($row->number))] = $row->id; }

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $filter_status = $this->input->get('filter_status');
            $username = $this->session->username;
            $filter_group_ex = explode(",", trim($filter_group));

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            if($filter_status == "0"){
                $where_status = "AND e.employee_id IS NULL";
            }elseif($filter_status == "1"){
                $where_status = "AND e.employee_id IS NOT NULL";
            }else{
                $where_status = "";
            }

            if ($username == "admin") {
                $privileges = "";
            } else {
                $privileges = "AND c.status = '1'";
            }

            if($filter_group != ""){
                $filter_group_ex = array_map(function($v) {
                    return $this->db->escape($v);
                }, $filter_group_ex);

                $where_group = "AND b.group_id IN (" . implode(',', $filter_group_ex) . ")";
            } else {
                $where_group = "";
            }

            if($filter_employee != ""){
                $employee = " AND a.employee_id LIKE '%$filter_employee%'";
            } else {
                $employee = " AND ( r.resign_date IS NULL OR r.resign_date >= '$filter_from' ) ";
            }

            $q = "SELECT a.*, 0 AS tax_allowance, 0 AS overtime_saturday, 0 AS overtime_amount_saturday, 0 AS overtime_sunday, 0 AS overtime_amount_sunday, b.national_id, d.name as source_name, e.employee_id as status_resign, g.number as account_no,
            coalesce(p.bpjs, 0) as bpjs_rate, b.group_id, 0 as bpjs_kes_rate, r.resign_date
            FROM payrolls a
                JOIN employees b on a.employee_id = b.id
                LEFT JOIN sources d on b.source_id = d.id
                JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' $privileges
                LEFT JOIN resignations e ON b.id = e.employee_id
                LEFT JOIN account_coa f ON b.division_id = f.division_id and b.departement_id = f.departement_id and b.position_id = f.position_id and b.contract_id = f.contract_id
                LEFT JOIN accounts g ON f.account_id = g.id and g.category = 'payroll'
                LEFT JOIN setup_salaries p ON p.employee_id = b.id
                LEFT JOIN resignations r ON r.employee_id = b.id
                WHERE a.period_start = '$period_start' $where_status
                AND a.period_end = '$period_end'
                AND b.division_id LIKE '%$filter_division%'
                AND b.departement_id LIKE '%$filter_departement%'
                AND b.departement_sub_id LIKE '%$filter_departement_sub%'
                $employee
                AND b.contract_id LIKE '%$filter_employee_type%'
                $where_group
                GROUP BY a.employee_id
                ORDER BY a.name ASC";
            // die($q);
            $datas = [];
            $no = 1;
            $sum_total_correction_plus = 0;
            $sum_total_overtime       = 0;
            $sum_tax_allowance        = 0;
            $sum_total_all_allowance  = 0;
            $sum_deduction_absence    = 0;
            $sum_potongan_lain        = 0;
            $sum_jp_emp               = 0;
            $sum_bpjs_emp             = 0;
            $sum_jht_emp              = 0;
            $sum_ter                  = 0;
            $sum_total_all_deduction  = 0;
            $sum_net_income           = 0;
            $sum_bpjs_rate            = 0;
            $sum_bpjs_kes_rate        = 0;
            // total untuk th2
            $dynamic_allowances = [];
            $excluded_allowance_numbers = [
                '20230217000001', // Tunjangan Tetap
                '20230222000001', // Tunjangan Jabatan
                '20230117000003', // Tunjangan Kinerja
                '20221213000006', // Uang Makan 1
                '20250916000001', // Uang Makan 2
                '20221213000007'  // Uang Makan 3
            ];
            $excluded_allowance_names = [
                'tunjangan tetap',
                'tunjangan jabatan',
                'tunjangan kinerja',
                'tunjangan transport'
            ];
            $id_tunjangan_tetap = null;
            $id_tunjangan_jabatan = null;
            $id_tunjangan_kinerja = null;
            foreach ($allowance as $row) {
                $num = strtolower(trim($row->number));
                $name = strtolower(trim($row->name));
                if ($num == '20230217000001' || $name == 'tunjangan tetap') { $id_tunjangan_tetap = $row->id; }
                if ($num == '20230222000001' || $name == 'tunjangan jabatan') { $id_tunjangan_jabatan = $row->id; }
                if ($num == '20230117000003' || $name == 'tunjangan kinerja') { $id_tunjangan_kinerja = $row->id; }
                if (in_array($num, $excluded_allowance_numbers) || in_array($name, $excluded_allowance_names)) {
                    continue;
                }
                $dynamic_allowances[] = $row;
            }

            $sum_salary              = 0;
            $sum_allowances          = array_fill(0, count($dynamic_allowances), 0);
            $sum_tunjangan_tetap     = 0;
            $sum_tunjangan_jabatan   = 0;
            $sum_tunjangan_kinerja   = 0;
            $sum_tunjangan_transport = 0;

            $sum_gaji_harian = 0;
            $sum_uang_makan  = 0;

            $group_id = null;
            $hlGroups = ['20221213000002', '20221213000003'];

            $filter_group_ex = array_map(function($v) {
                return trim($v, "'");
            }, $filter_group_ex);
            $is_hl = 0;
            if ($filter_employee) {
                $femployee = $this->crud->read('employees', [], ['id' => $filter_employee]);
                $group_id  = $femployee->group_id ?? null;
                $is_hl = in_array($group_id, $hlGroups, true);
            } else {
                $is_hl = array_intersect($filter_group_ex, $hlGroups);
            }

            $filterGroupAll = 1;
            if ($filter_group) {
                $filterGroupAll = 0;
            }

            $amount_groups = [];
            foreach ($this->db->query($q)->result_array() as $index => $record) {
                if (!isset($amount_groups[$record['group_name']])) { $amount_groups[$record['group_name']] = 0; }
                $amount_groups[$record['group_name']]++;

                $status = "ACTIVE";
                if ($record['resign_date']) {
                    // cek resign dalam periode gaji
                    $rstart = new DateTime($filter_from);
                    $rend   = (new DateTime($filter_to));
                    $rdate = new DateTime($record['resign_date']);

                    if ($rdate >= $rstart && $rdate <= $rend) {
                        $status = 'RESIGN';
                    }
                }
                $data = [
                    "number" => $record["number"],
                    "name" => $record["name"],
                    "status" => $status,
                    "national_id" => $record["national_id"],
                    "marital" => $record["marital"],
                    "position_name" => $record["position_name"],
                    "departement_sub_name" => $record["departement_sub_name"],
                ];
                $is_hl_emp = in_array($record['group_id'], $hlGroups, true);
                $all_deduction = [];
                foreach (json_decode($record['deduction']) as $key => $value) { $all_deduction[] = $value; }
                $potongan_lain = $record['loan_cooperative'] + $record['loan_bank'] + $record['loan_other'] + array_sum($all_deduction);
                $allowances = json_decode($record['allowence'], true);
                $arr_allowance = [];
                foreach ($allowances as $key => $value) {
                    $arr_allowance[(isset($id_allowances[$key])) ? $id_allowances[$key] : $key] = floatval($value);
                }
                // die(json_encode($arr_allowance));
                if ($is_hl || $filterGroupAll) {
                    $gaji_harian = 0;
                    // ambil nilai uang makan (only pti)
                    $uang_makan = 0;
                    if (isset($arr_allowance['20221213000006'])) { $uang_makan = $arr_allowance['20221213000006']; }
                    if (isset($arr_allowance['20250916000001']) && empty($uang_makan)) { $uang_makan = $arr_allowance['20250916000001']; }
                    if (isset($arr_allowance['20221213000007']) && empty($uang_makan)) { $uang_makan = $arr_allowance['20221213000007']; }

                    if (floatval($record['salary']) == 0) {
                        // hitung total selain uang makan
                        foreach ($arr_allowance as $key => $value) {
                            if (in_array($key, ['20221213000006','20250916000001','20221213000007'])) continue; // skip uang makan
                            $gaji_harian += (float) $value;
                        }
                    }
                    // if (!$is_hl_emp) {
                    //     $gaji_harian = 0;
                    //     $uang_makan = 0;
                    // }
                    $data["gaji_harian"] = $gaji_harian;
                    $data["uang_makan"] = $uang_makan;

                    $sum_gaji_harian += $gaji_harian;
                    $sum_uang_makan  += $uang_makan;
                }
                // BRI, KI, PTI, DUMMY
                $tunjangan_tetap = 0;
                if ($id_tunjangan_tetap && isset($arr_allowance[$id_tunjangan_tetap])) {
                    $tunjangan_tetap = $arr_allowance[$id_tunjangan_tetap];
                } elseif (isset($arr_allowance['20230217000001'])) {
                    $tunjangan_tetap = $arr_allowance['20230217000001'];
                }

                $tunjangan_jabatan = 0;
                if ($id_tunjangan_jabatan && isset($arr_allowance[$id_tunjangan_jabatan])) {
                    $tunjangan_jabatan = $arr_allowance[$id_tunjangan_jabatan];
                } elseif (isset($arr_allowance['20230222000001'])) {
                    $tunjangan_jabatan = $arr_allowance['20230222000001'];
                }

                $tunjangan_kinerja = 0;
                if ($id_tunjangan_kinerja && isset($arr_allowance[$id_tunjangan_kinerja])) {
                    $tunjangan_kinerja = $arr_allowance[$id_tunjangan_kinerja];
                } elseif (isset($arr_allowance['20230117000003'])) {
                    $tunjangan_kinerja = $arr_allowance['20230117000003'];
                }

                $tunjangan_transport = 0;

                $data["salary"] = $record['salary'];

                // Dynamic allowances
                $tunjangan_arr = [];
                foreach ($dynamic_allowances as $row) {
                    $val = 0;
                    if (isset($arr_allowance[$row->id])) {
                        $val = $arr_allowance[$row->id];
                    } elseif (isset($arr_allowance[strtolower(trim($row->number))])) {
                        $val = $arr_allowance[strtolower(trim($row->number))];
                    } elseif (isset($arr_allowance[$row->number])) {
                        $val = $arr_allowance[$row->number];
                    }
                    $data["allowance_" . $row->id] = $val;
                    $tunjangan_arr[] = $val;
                }

                $data["tunjangan_tetap"] = $tunjangan_tetap;
                $data["tunjangan_jabatan"] = $tunjangan_jabatan;
                $data["tunjangan_kinerja"] = $tunjangan_kinerja;
                $data["tunjangan_transport"] = $tunjangan_transport;

                $sum_salary              += $record['salary'];
                foreach ($tunjangan_arr as $key => $val) {
                    $sum_allowances[$key] += $val;
                }
                $sum_tunjangan_tetap     += $tunjangan_tetap;
                $sum_tunjangan_jabatan   += $tunjangan_jabatan;
                $sum_tunjangan_kinerja   += $tunjangan_kinerja;
                $sum_tunjangan_transport += $tunjangan_transport;

                
                // if ($record['ter'] < 0) {
                    $tax_allowance = 0;
                // } else {
                //     $tax_allowance = $record['ter'];
                // }
                $total_overtime = $record['overtime_amount_weekday'] + $record['overtime_amount_saturday'] + $record['overtime_amount_sunday'] + $record['overtime_amount_holiday'];

                $corr_plus = $record['correction_plus'];

                // if ($is_hl_emp) {
                //     $total_all_allowance = $tax_allowance + $total_overtime + $gaji_harian + $corr_plus;
                // } else {
                    $total_all_allowance = $tax_allowance + array_sum($tunjangan_arr) + $tunjangan_tetap + $tunjangan_jabatan + $tunjangan_kinerja + $total_overtime + $corr_plus;
                // }

                $bpjs_employee = json_decode($record['bpjs_employee'], true);
                $jp_emp = $bpjs_employee['jp_employee'] ?? 0;
                $bpjs_emp = $bpjs_employee['bpjs_employee'] ?? 0;
                $jht_emp = $bpjs_employee['jht_employee'] ?? 0;

                $total_all_deduction = $record['total_all_deduction'] + $potongan_lain + $jp_emp + $bpjs_emp + $jht_emp + $record['ter'];

                $data["corr_plus"] = $corr_plus;
                $data["total_overtime"] = $total_overtime;
                $data["tax_allowance"] = $tax_allowance;
                $data["total_all_allowance"] = $total_all_allowance;
                $data["total_all_deduction"] = $record["total_all_deduction"];
                $data["potongan_lain"] = $potongan_lain;
                $data["jp_emp"] = $jp_emp;
                $data["bpjs_emp"] = $bpjs_emp;
                $data["jht_emp"] = $jht_emp;
                $data["ter"] = $record["ter"];
                $data["total_all_deduction2"] = $total_all_deduction;
                $data["ter2"] = $record["ter"];
                $data["net_income"] = $record["net_income"];
                $data["bpjs_rate"] = $record["bpjs_rate"];
                $data["bpjs_kes_rate"] = $record["bpjs_rate"];

                $no++;
                $sum_total_correction_plus += $corr_plus;
                $sum_total_overtime      += $total_overtime;
                $sum_tax_allowance       += $tax_allowance;
                $sum_total_all_allowance += $total_all_allowance;
                $sum_deduction_absence   += $record['total_all_deduction'];
                $sum_potongan_lain       += $potongan_lain;
                $sum_jp_emp              += $jp_emp;
                $sum_bpjs_emp            += $bpjs_emp;
                $sum_jht_emp             += $jht_emp;
                $sum_ter                 += $record['ter'];
                $sum_total_all_deduction += $total_all_deduction;
                $sum_net_income          += $record['net_income'];
                $sum_bpjs_rate           += $record['bpjs_rate'];
                $sum_bpjs_kes_rate       += $record['bpjs_rate'];
                // die(json_encode($data));
                array_push($datas, json_decode(json_encode($data)));
            }
            $data = ["total_1" => "", "total_2" => "", "total_3" => "", "total_4" => "", "total_5" => "", "total_6" => "", "total_7" => ""];
            if ($is_hl || $filterGroupAll) {
                $data["sum_gaji_harian"] = $sum_gaji_harian;
                $data["sum_uang_makan"] = $sum_uang_makan;
            }
            $data["sum_salary"] = $sum_salary;
            foreach ($sum_allowances as $key => $val) {
                $data["sum_allowances_" . $key] = $val;
            }
            $data["sum_tunjangan_tetap"] = $sum_tunjangan_tetap;
            $data["sum_tunjangan_jabatan"] = $sum_tunjangan_jabatan;
            $data["sum_tunjangan_kinerja"] = $sum_tunjangan_kinerja;
            $data["sum_tunjangan_transport"] = $sum_tunjangan_transport;
            $data["sum_total_correction_plus"] = $sum_total_correction_plus;
            $data["sum_total_overtime"] = $sum_total_overtime;
            $data["sum_tax_allowance"] = $sum_tax_allowance;
            $data["sum_total_all_allowance"] = $sum_total_all_allowance;
            $data["sum_deduction_absence"] = $sum_deduction_absence;
            $data["sum_potongan_lain"] = $sum_potongan_lain;
            $data["sum_jp_emp"] = $sum_jp_emp;
            $data["sum_bpjs_emp"] = $sum_bpjs_emp;
            $data["sum_jht_emp"] = $sum_jht_emp;
            $data["sum_ter"] = $sum_ter;
            $data["sum_total_all_deduction"] = $sum_total_all_deduction;
            $data["sum_ter2"] = $sum_ter;
            $data["sum_net_income"] = $sum_net_income;
            $data["sum_bpjs_rate"] = $sum_bpjs_rate;
            $data["sum_bpjs_kes_rate"] = $sum_bpjs_kes_rate;
            array_push($datas, json_decode(json_encode($data)));

            $columns = ['No', 'Employee ID', 'Full Name', 'Status', 'No. KTP', 'Status PTKP', 'Job Position', 'Organization', 'Basic Salary'];
            foreach ($dynamic_allowances as $row) {
                $columns[] = $row->name;
            }
            $columns[] = 'Tunjangan Tetap';
            $columns[] = 'Tunjangan Jabatan';
            $columns[] = 'Tunjangan Kinerja';
            $columns[] = 'Tunjangan Transport';
            $columns = array_merge($columns, ['Correction Plus', 'Total Overtime', 'Tax Allowance', 'Total Allowance', 'Potongan Absen', 'Potongan Lain-Lain', 'Jaminan Pensiun Employee', 'BPJSK Employee', 'JHT Employee', 'PPH 21', 'Total Deduction', 'PPH 21 Payment', 'Take Home Pay', 'BPJS TK Rate', 'BPJS Kes Rate']);
            if ($is_hl || $filterGroupAll) { array_splice($columns, 8, 0, ['Gaji Harian', 'Uang Makan']); }
            $last_index = count($columns) - 1;
            $title = 'Generate Payroll Report';

            $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            $ttd_alfa = '';
            $ttd_alfa2 = '';
            foreach ($columns as $key => $value) {
                if ($key == $last_index) {
                    $spreadsheet->setCellValue('A1', $title);
                    $spreadsheet->mergeCells('A1:'.$alfa.'1');
                    $spreadsheet->getStyle('A1')->applyFromArray($style_center);
                    $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                    $spreadsheet->setCellValue($alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                    $spreadsheet->setCellValue($alfa.'4', 'Print By : ' . $fullname);
                }

                $spreadsheet->setCellValue($alfa.'6', $value);
                $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);

                if (empty($ttd_alfa) && $value == 'BPJSK Employee') { $ttd_alfa = $alfa; }
                if (empty($ttd_alfa2) && $value == 'JHT Employee') { $ttd_alfa2 = $alfa; }

                $alfa++;
            }

            $id = base64_decode($this->input->post('id'));

            $row_no = 7;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';
                $is_total = isset($row->total_1);

                $style = $style_row_center;
                if ($is_total) {
                    $style = array_merge($style, [
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF0F0F0']
                        ]
                    ]);
                }
                $spreadsheet->setCellValue($alfa.$row_no, ($is_total) ? 'TOTAL' : $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);

                $set_right = false;
                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if (in_array($key, ['status'])) { $style = $style_row_center; }
                    if ($set_right) { $style = $style_row_right; }
                    if ($is_total) {
                        $style = array_merge($style, [
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0']
                            ]
                        ]);
                    }

                    if (in_array($key, ['number','national_id']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }

                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);

                    if (!$set_right && in_array($key, ['departement_sub_name', 'total_7'])) { $set_right = true; }
                }

                if ($is_total) { $spreadsheet->mergeCells('A'.$row_no.':H'.$row_no); }

                $row_no++;
            }
            $row_no++;
            $spreadsheet->setCellValue($ttd_alfa.$row_no, 'Approval By');
            $spreadsheet->getStyle($ttd_alfa.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->getStyle($ttd_alfa2.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->mergeCells($ttd_alfa.$row_no.':'.$ttd_alfa2.$row_no);
            $row_no++;
            $spreadsheet->getStyle($ttd_alfa.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->getStyle($ttd_alfa2.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->getRowDimension($row_no)->setRowHeight(48.75);
            $row_no++;
            $spreadsheet->setCellValue($ttd_alfa.$row_no, 'Rendhika Harsono');
            $spreadsheet->setCellValue($ttd_alfa2.$row_no, 'Askara Harsono');
            $spreadsheet->getStyle($ttd_alfa.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->getStyle($ttd_alfa2.$row_no)->applyFromArray($style_row_ttd);
            $spreadsheet->getColumnDimension($ttd_alfa)->setAutoSize(true);
            $spreadsheet->getColumnDimension($ttd_alfa2)->setAutoSize(true);

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $sheet++;
            $spreadsheet = $excel->createSheet();
            $excel->setActiveSheetIndex($sheet);
            $spreadsheet->setTitle('Accumulation Amount');

            $columns = ['No', 'Group', 'Total Amount'];

            $alfa = 'A';
            $row_no = 1;
            foreach ($columns as $key => $value) {
                $spreadsheet->setCellValue($alfa.$row_no, $value);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

                $alfa++;
            }

            $datas = $this->db->distinct()->select("TRIM(name) AS name, 0 AS amount")->from("groups")->order_by("name")->get()->result();

            $total = 0;
            $row_no++;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    if ($key == 'amount') {
                        if (isset($amount_groups[$row->name])) {
                            $value = $amount_groups[$row->name];
                            $total += floatval($value);
                        }
                    }

                    $style = $style_row;
                    if (in_array($key, [''])) { $style = $style_row_center; }
                    if (in_array($key, ['amount'])) { $style = $style_row_right; }

                    if (in_array($key, ['']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                }

                $row_no++;
            }

            $spreadsheet->setCellValue("A".$row_no, "Grand Total");
            $spreadsheet->setCellValue("C".$row_no, $total);

            $spreadsheet->mergeCells("A".$row_no.":B".$row_no);
            $spreadsheet->getStyle("A".$row_no)->applyFromArray($style_col);
            $spreadsheet->getStyle("B".$row_no)->applyFromArray($style_col);
            $spreadsheet->getStyle("C".$row_no)->applyFromArray(array_merge($style_row_right, ["font" => ["bold" => true]]));

            for ($i='A'; $i <= $alfa; $i++) { $spreadsheet->getColumnDimension($i)->setAutoSize(true); }

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) { ob_end_clean(); }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.date(' (Y-m-d)').'.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = IOFactory::createWriter($excel, 'Xlsx');
            $writer->save('php://output');
            exit;
        }
    }

    public function printcsv(){
        if ($this->input->get()) {

            header("Content-type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=payroll_" . time() . ".csv");
            $output = fopen("php://output", "w");

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_employee_type = $this->input->get('filter_employee_type');
            $filter_group = $this->input->get('filter_group');
            $filter_status = $this->input->get('filter_status');
            $payroll_bank_id = $this->input->get('payroll_bank_id');
            $username = $this->session->username;
            $filter_group_ex = explode(",", trim($filter_group));

            $payroll_bank = $this->crud->read('payroll_account', [], ["id" => $payroll_bank_id]);
            $rekening = $payroll_bank->account_no;
            // $filter_division = $payroll_bank->division_id;

            // $master_bank = $this->crud->read('banks', [], ["id" => $payroll_bank->bank_id]);
            $bank_name = $this->banks->banks('find')[$payroll_bank->bank_id];
            // die($bank_name);

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $today = date("Ymd");
            
            $bulan_payroll = strtoupper(date('My', strtotime($filter_to)));
            $bulan_payroll = 'PAYROLL '.$bulan_payroll;

            if ($username == "admin") {
                $privilege = "";
            } else {
                $privilege = " and c.status = '1'";
            }
            $this->db->select('a.*, b.bank_no, b.bank_name, b.name as employee_name, b.email');
            $this->db->from('payrolls a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('privilege_groups c', "(b.group_id = c.group_id or b.group_id is null or b.group_id = '') and c.username = '$username' $privilege");
            $this->db->like('b.bank_name', $bank_name);
            if (isset($this->restrict_user)) {
                $this->accessmodule->applyEmployeeRestriction($this->restrict_user, 'b');
            }
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.period_start =', $period_start);
                $this->db->where('a.period_end =', $period_end);
            }
            if($filter_status == "0"){
                $this->db->where('e.employee_id', null);
            }elseif($filter_status == "1"){
                $this->db->where('e.employee_id !=', null);
            }

            if($filter_division != ""){
               $this->db->like('b.division_id', $filter_division);
            }

            if($filter_departement != ""){
                $this->db->like('b.departement_id', $filter_departement);
            }

            if($filter_departement_sub != ""){
                $this->db->like('b.departement_sub_id', $filter_departement_sub);
            }

            if($filter_employee != ""){
                $this->db->like('b.id', $filter_employee);
            }
           
            if($filter_employee_type != ""){
                $this->db->like('b.contract_id', $filter_employee_type);
            }
            if($filter_group != ""){
                $this->db->where_in('c.group_id', $filter_group_ex);
            }
            
            $this->db->order_by('a.name', 'ASC');
            $this->db->group_by('a.employee_id');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);

            $records = $this->db->get()->result_array();

            $arr_final = [];
            $total_income = 0;
            foreach ($records as $record) {
                $arr_final[] = array(
                    $record['bank_no'],
                    $this->csvQuote($record['employee_name']),
                    '',
                    '',
                    '',
                    'IDR',
                    $record['net_income'],
                    $this->csvQuote($bulan_payroll),
                    '',
                    'IBU',
                    '',
                    $this->csvQuote($record['bank_name']),
                    'Jakarta',
                    '',
                    '',
                    '',
                    'Y',
                    $record['email'],
                    '',
                    '',
                    '',
                    'Y',
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                    'OUR',
                    '1'
                );
                $total_income += $record['net_income'];
            }
            fputcsv($output, array(
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null
            ));
            fputcsv($output, array(
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null,
                null,null,null,null,null,null,null,null,null,null
            ));
            fputcsv($output, array(
                "P",$today, $rekening, $totalRows, $total_income
            ));
            foreach ($arr_final as $row) {
                fwrite($output, implode(',', $row) . "\n");
            }
        }
    }

    function csvQuote($value) {
        $value = (string) $value;
        // escape double quote sesuai standar CSV
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }
}