<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Thr_hl extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('payroll/thr_hl');
        } else {
            redirect('error_access');
        }
    }

    public function read()
    {
        $filter_year = $this->input->get('filter_year');
        $readPayroll = $this->crud->reads('thr', ["period" => $filter_year]);

        if (count($readPayroll) > 0) {
            echo json_encode(array("generate" => "EXIST", "status" => @$readPayroll[0]->status));
        } else {
            echo json_encode(array("generate" => "OK", "status" => @$readPayroll[0]->status));
        }
    }

    public function readYears()
    {
        $yearBefore = (date("Y") - 5);
        $yearAfter = (date("Y") + 3);

        for ($i = $yearBefore; $i < $yearAfter; $i++) {
            $arr[] = array("year" => $i);
        }

        die(json_encode($arr));
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
            return array("periode" => "$y $m $d", "day" => $diff->d, "month" => $diff->m, "year" => $diff->y);
        }
    }

    public function readEmployee()
    {
        $cutoff = base64_decode($this->input->post("cutoff"));
        $result = [];

        $periode = date("Y-m", strtotime("-1 year", strtotime(date("Y-m-01", strtotime($cutoff)))));

        if (!empty($cutoff)) {
            $this->db->distinct()->select("TRIM(a.number) AS number, TRIM(a.name) AS name")
            ->from("employees a")->join("resignations b", "a.id = b.employee_id", "LEFT")
            ->where([
                "a.deleted" => 0,
                "a.status" => 1,
                "b.resign_date >=" => date("Y-m-d", strtotime("-1 year", strtotime($cutoff))),
                "a.group_id" => "20221119000002"
            ])->order_by("name");
            // die($this->db->get_compiled_select());
            $result = $this->db->get()->result();
        }

        echo json_encode(["total" => count($result), "rows" => $result]);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {
            $filter_year = $this->input->get('filter_year');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_service = $this->input->get('filter_service');
            $username = $this->session->username;

            if ($filter_service == "0") {
                $whereService1 = $this->db->where("datediff(current_date(), b.date_sign) <", 365);
                $whereService2 = "";
            } elseif ($filter_service == "1") {
                $whereService1 = $this->db->where("datediff(current_date(), b.date_sign) >", 365);
                $whereService2 = $this->db->where("datediff(current_date(), b.date_sign) <", 730);
            }else{
                $whereService1 = "";
                $whereService2 = "";
            }

            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query

            $this->db->select('a.*, c.name as source_name');
            $this->db->from('thr_hl a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('sources c', 'b.source_id = c.id', 'left');
            $this->db->join("privilege_groups d", "b.group_id = d.group_id and d.username = '$username' and d.status = '1'");
            $this->db->where_in("b.group_id", ['20221119000002']);
            if ($filter_year != "") {
                $this->db->where('a.period =', $filter_year);
            }
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('b.id', $filter_employee);
            $whereService1;
            $whereService2;
            $this->db->order_by('a.departement_name', 'ASC');
            $this->db->order_by('a.departement_sub_name', 'ASC');
            $this->db->order_by('a.employee_name', 'ASC');
            // die($this->db->get_compiled_select());

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $datas = array();
            $datas['total'] = $totalRows;
            $datas['rows'] = $records;
            echo json_encode($datas);
        }
    }

    public function generatePayroll()
    {
        if ($this->input->get()) {
            $filter_year = $this->input->get('filter_year');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_cutoff = strtotime(date('Y-m-01', strtotime(base64_decode($this->input->get('filter_cutoff')))));

            $period = date('Y-m', strtotime('-1 year', $filter_cutoff));

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
                    0 as salary,
                    p.bpjs,
                    q.number as marital,
                    q.ter_type,
                    a.tax_id,
                    o.thr_fee,
                    e.ter,
                    e.avg
                FROM employees a
                JOIN divisions b ON a.division_id = b.id
                JOIN departements c ON a.departement_id = c.id
                JOIN departement_subs d ON a.departement_sub_id = d.id
                JOIN (
                    SELECT employee_id, ter, GROUP_CONCAT(net_income SEPARATOR ',') AS avg
                    FROM payroll_harian_lepas WHERE period_end >= '$period' GROUP BY employee_id, ter
                ) e ON a.id = e.employee_id
                LEFT JOIN contracts f ON a.contract_id = f.id
                LEFT JOIN positions g ON a.position_id = g.id
                LEFT JOIN groups i ON a.group_id = i.id
                LEFT JOIN shift_employees k ON a.id = k.employee_id
                LEFT JOIN shifts n ON k.shift_id = n.id
                LEFT JOIN shift_details l ON n.id = l.shift_id
                LEFT JOIN sources o ON o.id = a.source_id
                LEFT JOIN setup_salaries p ON p.employee_id = a.id
                LEFT JOIN maritals q ON a.marital_id = q.id
                JOIN privilege_groups m ON i.id = m.group_id and m.username = '$username' and m.status = '1'
                WHERE a.deleted = 0 AND a.status = 0 AND a.group_id IN ('20221119000002')
                AND a.division_id LIKE '%$filter_division%'
                AND a.departement_id LIKE '%$filter_departement%'
                AND a.departement_sub_id LIKE '%$filter_departement_sub%'
                AND a.id LIKE '%$filter_employee%'
                GROUP BY a.id
                ORDER BY b.name ASC, c.name ASC, d.name ASC, a.number, a.name ASC";
            // die($q);
            $query = $this->db->query($q);
            $records = [];
            foreach ($query->result_array() as $index => $row) {
                if (!empty($row["avg"])) {
                    $arr = json_decode("[".$row["avg"]."]");
                    $row["salary"] = array_sum($arr) / count($arr);
                }

                array_push($records, $row);
            }

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
            $cutoff = base64_decode($this->input->get('filter_cutoff'));
            $salary = empty($record['salary']) ? 0 : $record['salary'];

            // $this->db->select('*');
            // $this->db->from('cutoff');
            // $this->db->order_by('start', 'desc');
            // $cutoffperiod = $this->db->get()->row();
            // $days = date("d", strtotime($cutoffperiod->start));
            
            $filter_year = $this->input->get('filter_year');
            $employee_id = $record['id'];
            $service = $this->readService($record['date_sign'], $cutoff);

            // $date = date("Y-m-d");
            // $timeStart = strtotime($record['date_sign']);
            // $timeEnd = strtotime($date);
            // // Menambah bulan ini + semua bulan pada tahun sebelumnya
            // $numBulan = 1 + (date("Y", $timeEnd) - date("Y", $timeStart)) * 12;
            // // menghitung selisih bulan
            // $numBulan += date("m", $timeEnd) - date("m", $timeStart);
            // // $month = $service['month'];

            $numTahun = ($service['year'] * 12);
            $numBulan = ($numTahun + $service['month']);

            if($service['day'] >= 20){
                $numBulan += 1;
            }

            //Menghitung jika fit for service lebih dari 12 bulan
            $thr = $salary;
            if ($numBulan < 12) { $thr = round(($salary / 12) * $numBulan); }

            //Menghitung PPH 21
            // $config = $this->db->get('payroll_config')->result();
            // $income_pph = $thr;
            // $netto_year = ($income_pph * 12);
            // $r_marital = $this->crud->read('maritals', ["number" => $record['marital']]);
            // $r_ptkp = $this->crud->read('ptkp', ["marital_id" => @$r_marital->id]);
            // $tax_amount = ($netto_year - @$r_ptkp->amount);
            // $pph_debt = (($tax_amount * $config[0]->payroll_pph_payable) / 100);
            // $pph_pasal = round($pph_debt / 12);

            // //Kalo gaji per tahun nya lebih dari master ptkp
            // if ($netto_year >= @$r_ptkp->amount) {
            //     //jika npwp nya kosong maka kena potongan 120%
            //     if ($record['tax_id'] == "" or $record['tax_id'] == null or $record['tax_id'] == "-") {
            //         $pph_final = round(($pph_pasal * $config[0]->payroll_npwp_null) / 100);
            //     } else {
            //         $pph_final = $pph_pasal;
            //     }
            //     //kalo kurang ga kena pajak pph
            // } else {
            //     $pph_final = 0;
            // }

            $income_thr = $salary + $thr;

            //Rumus TER
            $this->db->select("number, ter");
            $this->db->from('marital_categories');
            $this->db->where('ter_from <', $income_thr);
            $this->db->where('ter_to >', $income_thr);
            $this->db->where('type', $record['ter_type']);
            $marital_category = $this->db->get()->row();
            
            if(empty($marital_category)){
                $ter = 0;
            }else{
                $ter = ((($income_thr * $marital_category->ter) / 100) - $record['ter']);
            }

            if($numBulan >= 1){
                $arr = array(
                    "employee_id" => $record['id'],
                    "employee_number" => $record['number'],
                    "employee_name" => $record['name'],
                    "period" => $filter_year,
                    "cutoff" => $cutoff,
                    "type" => 'NORMAL',
                    "date_sign" => $record['date_sign'],
                    "services" => $service['periode'],
                    "departement_name" => $record['departement_name'],
                    "departement_sub_name" => $record['departement_sub_name'],
                    "position_name" => $record['position_name'],
                    "marital" => $record['marital'],
                    "tax_id" => $record['tax_id'],
                    "salary" => $salary,
                    "thr" => $thr,
                    "income" => $salary,
                    "income_thr" => $income_thr,
                    "pph" => ($ter),
                    "total_thr" => ($thr - $ter),
                );
            }

            if (@$arr == null) {
                $result = [];
            } else {
                $result = @$arr;
            }

            $checkPayroll = $this->crud->read("thr_hl", [], ["employee_id" => $employee_id, "period" => $filter_year]);
            if (!empty($checkPayroll->employee_id)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Employee", "theme" => "error"));
            } else {
                $send = $this->crud->create('thr_hl', $result);
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

    //DELETE DATA
    public function delete()
    {
        $post = $this->input->post();
        $username = $this->session->username;

        if (isset($post['id'])) {
            foreach ($post['id'] as $key => $value) {
                $this->db->delete("thr_hl", ['id' => base64_decode($value)]);
            }
        } else {
            $privileges = $this->crud->reads('privilege_groups', [], ["username" => $username, "status" => "1"]);

            foreach ($privileges as $privilege) {
                $group_id = $privilege->group_id;

                $this->db->select('a.*');
                $this->db->from('thr_hl a');
                $this->db->join('employees b', 'a.employee_id = b.id');
                $this->db->where_in("b.group_id", ['20221119000002']);
                $this->db->where('a.period', $post['filter_year']);
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
                $payrolls = $this->db->get()->result_object();

                foreach ($payrolls as $payroll) {
                    $this->db->delete("thr_hl", ['id' => $payroll->id]);
                }
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=thr_hl_$format.xls");
        }

        $filter_year = $this->input->get('filter_year');
        $filter_division = $this->input->get('filter_division');
        $filter_departement = $this->input->get('filter_departement');
        $filter_departement_sub = $this->input->get('filter_departement_sub');
        $filter_employee = $this->input->get('filter_employee');
        $filter_service = $this->input->get('filter_service');
        $username = $this->session->username;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select('a.*,  c.name as source_name');
        $this->db->from('thr_hl a');
        $this->db->join('employees b', 'a.employee_id = b.id');
        $this->db->join('sources c', 'b.source_id = c.id', 'left');
        $this->db->join("privilege_groups d", "b.group_id = d.group_id and d.username = '$username' and d.status = '1'");
        $this->db->where_in("b.group_id", ['20221119000002']);
        if ($filter_year != "") {
            $this->db->where('a.period =', $filter_year);
        }
        if ($filter_service == "0") {
            $this->db->where("datediff(current_date(), a.date_sign) <", 365);
        } elseif ($filter_service == "1") {
            $this->db->where("datediff(current_date(), a.date_sign) <", 730);
        }
        $this->db->like('b.division_id', $filter_division);
        $this->db->like('b.departement_id', $filter_departement);
        $this->db->like('b.departement_sub_id', $filter_departement_sub);
        $this->db->like('b.id', $filter_employee);
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
                            <small>DATA PAYROLL THR</small>
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
                <th>Departement</th>
                <th>Departement Sub</th>
                <th>Join Date</th>
                <th>Cutoff</th>
                <th>Fit of Services</th>
                <th>Position</th>
                <th>Marital</th>
                <th>Tax Id</th>
                <th>Salary</th>
                <th>THR</th>
                <th>Payroll</th>
                <th>Total Income</th>
                <th>TER</th>
                <th>Total THR</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['employee_number'] . '</td>
                            <td>' . $data['employee_name'] . '</td>
                            <td>' . $data['departement_name'] . '</td>
                            <td>' . $data['departement_sub_name'] . '</td>
                            <td>' . date("d F Y", strtotime($data['date_sign'])) . '</td>
                            <td>' . date("d F Y", strtotime($data['cutoff'])) . '</td>
                            <td>' . $data['services'] . '</td>
                            <td>' . $data['position_name'] . '</td>
                            <td>' . $data['marital'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['tax_id'] . '</td>
                            <td>' . $data['salary'] . '</td>
                            <td>' . $data['thr'] . '</td>
                            <td>' . $data['income'] . '</td>
                            <td>' . $data['income_thr'] . '</td>
                            <td>' . $data['pph'] . '</td>
                            <td>' . $data['total_thr'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function additionalCalculation()
    {
        $cutoff = base64_decode($this->input->post("cutoff"));
        $mp = base64_decode($this->input->post("mp"));
        $filter_cutoff = strtotime(date('Y-m-01', strtotime($cutoff)));

        $period = date('Y-m', strtotime('-1 year', $filter_cutoff));

        $this->db->distinct()->select(
            "TRIM(a.id) AS employee_id,".

            "TRIM(a.number) AS employee_number,".
            "TRIM(a.name) AS employee_name,".
            "COALESCE(TRIM(b.name), '') AS departement_name,".
            "COALESCE(TRIM(c.name), '') AS departement_sub_name,".
            "COALESCE(TRIM(d.name), '') AS source_name,".
            "a.date_sign AS date_sign,".
            "'$cutoff' AS cutoff,".
            "'ADDITIONAL' AS type,".
            "'' AS services,".
            "COALESCE(TRIM(e.name), '') AS position_name,".
            "COALESCE(TRIM(f.number), '') AS marital,".
            "TRIM(a.tax_id) AS tax_id,".
            "0 AS salary,".
            "0 AS thr,".
            "0 AS income,".
            "0 AS income_thr,".
            "0 AS pph,".
            "0 AS total_thr,".

            "a.date_expired,".
            "a.source_id,".
            "a.group_id,".
            "COALESCE(TRIM(i.name), '') AS group_name,".
            "COALESCE(j.avg, '') AS avg,".
            "COALESCE(j.ter, 0) AS ter,".
            "COALESCE(TRIM(f.ter_type), '') AS ter_type"
        )->from("employees a")
            ->join("departements b", "a.departement_id = b.id")
            ->join("departement_subs c", "a.departement_sub_id = c.id")
            ->join("sources d", "a.source_id = d.id", "LEFT")
            ->join("positions e", "a.position_id = e.id", "LEFT")
            ->join("maritals f", "a.marital_id = f.id", "LEFT")
            ->join("setup_salaries g", "a.id = g.employee_id", "LEFT")
            ->join("groups i", "a.group_id = i.id", "LEFT")
            ->join("(".
                "SELECT employee_id, ter, GROUP_CONCAT(net_income SEPARATOR ',') AS avg ".
                "FROM payroll_harian_lepas WHERE period_start >= '$period' GROUP BY employee_id, ter"
            .") j", "a.id = j.employee_id", "LEFT")
        ->where(["a.number" => $mp]);
        // die($this->db->get_compiled_select());
        $result = $this->db->get()->row();

        if (!empty($result)) {
            $service = $this->readService($result->date_expired, date("Y-m-d", strtotime("-1 year", strtotime($cutoff))));
            $result->services = $service["periode"];

            $numBulan = (floatval($service['year']) * 12) + floatval($service['month']);
            if (floatval($service['day']) >= 20) { $numBulan += 1; }

            if (!empty($result->avg)) {
                $arr = json_decode("[$result->avg]");
                $result->salary = array_sum($arr) / count($arr);
            }

            $thr = floatval($result->salary);
            if ($numBulan < 12) { $thr = round(($thr / 12) * $numBulan); }
            $result->thr = $thr ?? 0;

            $result->income = floatval($result->salary);
            $result->income_thr = $result->income + $result->thr;

            $this->db->select("ter")->from("marital_categories")->where([
                "ter_from <" => $result->income_thr,
                "ter_to >" => $result->income_thr,
                "type" => $result->ter_type
            ]);
            $marital_category = $this->db->get()->row();

            if (!empty($marital_category)) { $result->pph = (($result->income_thr * floatval($marital_category->ter)) / 100) - $result->ter; }

            $result->total_thr = $result->thr - $result->pph;
        }

        echo json_encode($result);
    }

    public function additionalSave()
    {

        $post = $this->input->post();
        $periode = base64_decode($post["periode"]);
        $data = json_decode(base64_decode($post["data"]));
        $result = ['stat' => ''];

        $this->db->trans_start();

        foreach ($data as $index => $row) {
            $this->db->delete("thr_hl", [
                "period" => $periode,
                "employee_id" => $row->employee_id
            ]);
        }

        foreach ($data as $index => $row) {
            if (empty($result['stat'])) {
                $send_data = [
                    "employee_id" => $row->employee_id,
                    "employee_number" => $row->employee_number,
                    "employee_name" => $row->employee_name,
                    "period" => $periode,
                    "cutoff" => $row->cutoff,
                    "type" => 'ADDITIONAL',
                    "date_sign" => $row->date_sign,
                    "services" => $row->services,
                    "departement_name" => $row->departement_name,
                    "departement_sub_name" => $row->departement_sub_name,
                    "position_name" => $row->position_name,
                    "marital" => $row->marital,
                    "tax_id" => $row->tax_id,
                    "salary" => $row->salary,
                    "thr" => $row->thr,
                    "income" => $row->income,
                    "income_thr" => $row->income_thr,
                    "pph" => $row->pph,
                    "total_thr" => $row->total_thr
                ];

                $this->crud->create("thr_hl", $send_data);
                if ($this->db->where($send_data)->get("thr_hl")->num_rows() == 0) {
                    $result['stat'] = $this->db->error()['message'];
                }
            }
        }

        if (!empty($result['stat'])) { $this->db->trans_rollback(); }
        else { $this->db->trans_commit(); }

        echo json_encode($result);
    }
}
