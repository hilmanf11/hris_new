<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Sourcing_fee extends CI_Controller
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
            $this->load->view('report/sourcing_fee');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sourcing_fee_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_group = $this->input->get('filter_group');
            $filter_source = $this->input->get('filter_source');

            $username = $this->session->username;

            $period_start = date("Y-m", strtotime($filter_from));
            $period_end = date("Y-m", strtotime($filter_to));

            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $z);

                $this->db->select('description');
                $this->db->from('calendars');
                $this->db->where('trans_date', $working_date);
                $holiday = $this->db->get()->row();

                if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                    if (@$holiday->description != null or @$holiday->description != "") {
                        $hkw += 0;
                    } else {
                        $hkw += 1;
                    }
                } else {
                    $hkw += 0;
                }
            }

            $permits = [];
            $this->db->select("employee_id, COUNT(permit_date) AS count")->from("permits")->where([
                "permit_type_id" => "20221124000005",
                "permit_date >=" => $filter_from,
                "permit_date <=" => $filter_to
            ])->group_by("employee_id");
            // die($this->db->get_compiled_select());
            foreach ($this->db->get()->result() as $index => $row) { $permits[$row->employee_id] = $row->count; }

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <style> .str{ mso-number-format:\@; } </style>
            <body>';
            $this->db->select('a.id as group_id, a.name as group_name, b.sourcing_fee, b.id as source_id, b.name as source_name');
            $this->db->from('groups a');
            $this->db->join('sources b', 'a.id = b.group_id');
            $this->db->like('a.id', $filter_group);
            $this->db->like('b.id', $filter_source);
            $this->db->group_by('b.id');
            $this->db->order_by('a.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            foreach ($records as $record) {
                $no = 1;
                $query = $this->db->query("SELECT a.*, b.bank_branch, b.bank_no, b.national_id, b.date_sign FROM payrolls a
                            JOIN employees b ON a.employee_id = b.id
                            LEFT JOIN privilege_groups c ON b.group_id = c.group_id and c.username = '$username' and c.status = '1'
                            LEFT JOIN setup_allowances d ON a.employee_id = d.employee_id
                            LEFT JOIN allowances e ON d.allowance_id = e.id
                            WHERE a.period_start = '$period_start' and a.period_end = '$period_end' and (e.type != 'TEMPORARY' or e.type is null)
                            AND b.status = 0
                            AND b.group_id = '$record[group_id]'
                            AND b.source_id = '$record[source_id]'
                            ORDER BY a.`name` ASC");
                $employees = $query->result_array();

                if(count($employees) > 0){
                    $html .= '  <center>
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
                                <h3 style="margin:0;">SOURCE FEE REPORT ' . $record['group_name'] . ' ' . $record['source_name'] . '</h3>
                                <p style="margin:0;">Period <b>' . date("d F Y", strtotime($filter_from)) . '</b> to <b>' . date("d F Y", strtotime($filter_to)) . '</b></p>
                                <br>
                            </center>
                            <table id="customers" border="1">
                                <tr>
                                    <th width="20">No</th>
                                    <th style="text-align:center;">Employee ID</th>
                                    <th style="text-align:center;">Employee Name</th>
                                    <th style="text-align:center;">Fit Of Service</th>
                                    <th style="text-align:center;">WD</th>
                                    <th style="text-align:center;">Amount</th>
                                </tr>';

                    $total = 0;
                    foreach ($employees as $employee) {
                        $start  = date_create($employee['date_sign']);
                        $end = date_create($filter_to);
                        $diff  = date_diff($start, $end);
                        $selisih = date_diff(date_create($employee['date_sign']), date_create($filter_to));
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

                        $services = $y . $m . $d;

                        if ($selisih->format('%a') > 30) {
                            $wd = $hkw;
                        } else {
                            $wd = $employee['attandance_wd'] + ($permits[$employee["employee_id"]] ?? 0);
                        }

                        $fee = (($record['sourcing_fee'] / $hkw) * $wd);

                        $html .= '  <tr>
                                        <td>' . $no . '</td>
                                        <td style="mso-number-format:\@;">' . $employee['number'] . '</td>
                                        <td>' . $employee['name'] . '</td>
                                        <td>' . $services . '</td>
                                        <td>' . $wd . '</td>
                                        <td style="text-align:right;">' . round($fee) . '</td>
                                    </tr>';
                        $total += round($fee);
                        $no++;
                    }

                    $html .= '  <tr>
                                    <th colspan="5" style="text-align:right;">Grand Total</th>
                                    <th style="text-align:right;">' . round($total) . '</th>
                                </tr>';

                    $html .= '</table>
                                <div style="page-break-after:always;"></div>';
                }
            }

            $html .= '</body></html>';
            echo $html;
        }
    }
}
