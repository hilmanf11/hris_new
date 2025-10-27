<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
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
            $username = $this->session->username;

            $this->db->select('b.*');
            $this->db->from('logins a');
            $this->db->join('users b', 'a.username = b.username');
            $this->db->where('b.deleted', 0);
            $this->db->where('b.actived', 0);
            $this->db->where('b.status', 0);
            $this->db->where_not_in('b.username', $username);
            $this->db->like('a.created_date', date('Y-m-d'));
            $this->db->order_by('b.name', 'ASC');
            $logins = $this->db->get()->result_object();

            $config = $this->crud->read('config');
            $profiles = $this->crud->read("users", [], ["username" => $username]);

            if ($profiles->avatar != "") {
                $profile_img = $profiles->avatar;
            } else {
                $profile_img = base_url('assets/image/users/default.png');
            }

            $data['users'] = $logins;
            $data['profile'] = $profiles;
            $data['profile_img'] = $profile_img;
            $data['config'] = $config;

            $this->load->view('template/header');
            $this->load->view('home', $data);
        } else {
            redirect('login');
        }
    }

    public function menus()
    {
        if ($this->session->username != "") {
            $username = $this->session->username;
            $searchName = $this->input->get('name');

            $this->db->select('a.*');
            $this->db->from('menus a');
            $this->db->join('setting_users b', 'a.id = b.menus_id');
            $this->db->join('users c', 'b.users_id = c.username');
            $this->db->where('a.deleted', 0);
            $this->db->where('c.username', $username);
            $this->db->where('b.v_view', 1);
            if($searchName != ""){
                $this->db->like('a.name', $searchName);
                $this->db->where('a.state', '');
            }
            $this->db->order_by('a.sort', 'ASC');
            $menus = $this->db->get()->result_array();

            die(json_encode($menus));
        } else {
            show_error("Session Expired");
        }
    }

    public function updateProfile()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $avatar = $this->crud->upload('avatar', ["jpg", "png", "jpeg"], 'assets/image/users/', ["username" => $post['username']], "users", "avatar");

            if ($post['password'] == "") {
                $postFinal = array(
                    "name" => $post['name'],
                    "username" => $post['username'],
                    "email" => $post['email'],
                    "phone" => $post['phone'],
                    "position" => $post['position'],
                    "theme" => $post['theme'],
                    "avatar" => $avatar,
                );
            } else {
                $postFinal = array(
                    "name" => $post['name'],
                    "username" => $post['username'],
                    "email" => $post['email'],
                    "phone" => $post['phone'],
                    "position" => $post['position'],
                    "theme" => $post['theme'],
                    "password" => $post['password'],
                    "avatar" => $avatar,
                );
            }

            $send = $this->crud->update('users', ["username" => $post['username']], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function chats()
    {
        if ($this->input->post()) {
            $to_users_id = $this->input->post('to_users_id');
            $from_users_id = $this->session->id;

            $this->crud->update("chats", ["from_users_id" => $to_users_id, "to_users_id" => $from_users_id], ["status" => 1]);

            $chats = $this->crud->query("SELECT * FROM chats WHERE (to_users_id='$to_users_id' and from_users_id = '$from_users_id') or (to_users_id='$from_users_id' and from_users_id = '$to_users_id')");
            foreach ($chats as $chat) {
                if ($chat->to_users_id == $to_users_id) {
                    echo '  <div class="alert alert-warning p-2" role="alert">
                                ' . $chat->messages . '
                            </div>';
                } else {
                    echo '  <div class="alert alert-info p-2" style="text-align: right;" role="alert">
                                ' . $chat->messages . '
                            </div>';
                }
            }
        }
    }

    public function createChats()
    {
        if ($this->input->post()) {
            $from_users_id = $this->session->id;
            $to_users_id = $this->input->post('to_users_id');
            $messages = $this->input->post('messages');

            $chats = $this->crud->create("chats", ["from_users_id" => $from_users_id, "to_users_id" => $to_users_id, "messages" => $messages]);
            echo json_encode($chats);
        }
    }

    public function notificationList()
    {
        $get_access = $this->db->select("v_view, v_edit")->from('setting_users')
            ->where('menus_id', 'a391d47a7bce4f02aff4e45a2648b7')
            ->where('users_id', $this->session->username)
        ->get()->row();

        $get_data = $this->db->select("COUNT(number) AS count")->from("agreements")
        ->where("DATE(NOW()) BETWEEN DATE_SUB(date_expired, INTERVAL 30 DAY) AND date_expired")->get()->row();

        if ($get_access->v_view == '1' && $get_access->v_edit == '1' && intval($get_data->count) > 0) {
            echo <<<TEXT
            <li class="list-isi"><a onclick="formAgreement()">
                <table style="width: 100%;">
                    <tr><td style="font-weight: bold;">Agreements</td></tr>
                    <tr><td>There are several contract to expired in <span style="font-weight: bold;">Agreements</span></td></tr>
                </table>
            </a></li>
            TEXT;
        }
    }

    public function notificationCount()
    {
        $count = 0;

        $get_access = $this->db->select("v_view, v_edit")->from('setting_users')
            ->where('menus_id', 'a391d47a7bce4f02aff4e45a2648b7')
            ->where('users_id', $this->session->username)
        ->get()->row();

        $get_data = $this->db->select("COUNT(number) AS count")->from("agreements")
        ->where("DATE(NOW()) BETWEEN DATE_SUB(date_expired, INTERVAL 30 DAY) AND date_expired")->get()->row();

        if ($get_access->v_view == '1' && $get_access->v_edit == '1' && intval($get_data->count) > 0) { $count++; }

        echo '<span class="badge">' . $count . '</span>';
    }

    public function datatablesagreement()
    {
        $result = ['rows' => [], 'total' => 0];

        $get = $this->db->distinct()->select(
             "TRIM(a.id) AS id,"
            ."TRIM(a.number) AS emp,"
            ."TRIM(b.name) AS name,"
            ."TRIM(c.name) AS division,"
            ."TRIM(d.name) AS dept,"
            ."TRIM(e.name) AS dept_sub,"
            ."TRIM(f.name) AS position,"
            ."TRIM(g.name) AS type,"
            ."DATE(a.date_sign) AS date_join,"
            ."DATE(a.date_expired) AS expired,"
            ."COALESCE(TRIM(a.note),'') AS note,"
            ."COALESCE(TRIM(b.contract_id), '') IN ('20221119000001', '20221119000002', '20230112000001', '20230124000001', '20240104000001') AS is_contract"
        )->from("agreements a")
            ->join("employees b", "a.number = b.number")
            ->join("divisions c", "b.division_id = c.id")
            ->join("departements d", "b.departement_id = d.id")
            ->join("departement_subs e", "b.departement_sub_id = e.id")
            ->join("positions f", "a.position_id = f.id")
            ->join("contracts g", "a.contract_id = g.id")
        ->where("a.status", '0')
        ->where("b.contract_id !=", '20221119000005')
        ->group_start()
            ->where("DATE(NOW()) BETWEEN DATE_SUB(a.date_expired, INTERVAL 30 DAY) AND a.date_expired")
            ->or_where("a.attachment IS NULL")
        ->group_end()
        // ->where_in("b.id", ["20221119000007", "18050009210574"])
        ->order_by("expired")->get()->result_object();
        foreach ($get as $key => $value) { array_push($result['rows'], $value); }
        $result['total'] = count($result['rows']);

        echo json_encode($result);
    }

    public function agreementExcel()
    {
        $title = 'Expired Contract  Agreements';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=$title ".date("(Y-m-d)").".xls");

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $fullname = $this->db->select("TRIM(name) AS name")->from("users")->where("username", $this->session->username)
        ->get()->row()->name;

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>'.$title.'</title>
            <style>
                body { font-family: Arial, Helvetica, sans-serif; }
                #tbl { border-collapse: collapse;width: 100%; font-size: 12px; }
                #tbl td, #tbl th { border: 1px solid #ddd; padding: 2px; }
                #tbl tr:nth-child(even){ background-color: #f2f2f2; }
                #tbl tr:hover { background-color: #ddd; }
                #tbl th { padding-top: 2px; padding-bottom: 2px; text-align: left; color: black; }
            </style>
        </head>
        <body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="'.$config->favicon.'" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>'.$config->name.'</b><br>
                                <small>AGREEMENT EMPLOYEE</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date '.date("d M Y H:m:s").' <br>
                    Print By '.$fullname.'  
                </div>
            </center>
            <br><br><br>
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Division</th>
                    <th>Departement</th>
                    <th>Departement Sub</th>
                    <th>Position</th>
                    <th>Employee Type</th>
                    <th>Join Date</th>
                    <th>Contract Expired</th>
                    <th>Note</th>
                </tr>';

        $get = $this->db->distinct()->select(
            "TRIM(a.number) AS emp,"
            ."TRIM(b.name) AS name,"
            ."TRIM(c.name) AS division,"
            ."TRIM(d.name) AS dept,"
            ."TRIM(e.name) AS dept_sub,"
            ."TRIM(f.name) AS position,"
            ."TRIM(g.name) AS type,"
            ."DATE(a.date_sign) AS date_join,"
            ."DATE(a.date_expired) AS expired,"
            ."COALESCE(TRIM(a.note),'') AS note,"
        )->from("agreements a")
            ->join("employees b", "a.number = b.number")
            ->join("divisions c", "b.division_id = c.id")
            ->join("departements d", "b.departement_id = d.id")
            ->join("departement_subs e", "b.departement_sub_id = e.id")
            ->join("positions f", "a.position_id = f.id")
            ->join("contracts g", "a.contract_id = g.id")
        ->where("a.status", '0')
        ->where("b.contract_id !=", '20221119000005')
        ->group_start()
            ->where("DATE(NOW()) BETWEEN DATE_SUB(a.date_expired, INTERVAL 30 DAY) AND a.date_expired")
            ->or_where("a.attachment IS NULL")
        ->group_end()
        ->order_by("expired")->get()->result_object();
        foreach ($get as $key => $value) {
            $html .= '<tr>
                <td style="vertical-align: middle;" align="center">'.($key + 1).'</td>
                <td style="vertical-align: middle;">'.$value->emp.'</td>
                <td style="vertical-align: middle;">'.$value->name.'</td>
                <td style="vertical-align: middle;">'.$value->division.'</td>
                <td style="vertical-align: middle;">'.$value->dept.'</td>
                <td style="vertical-align: middle;">'.$value->dept_sub.'</td>
                <td style="vertical-align: middle;">'.$value->position.'</td>
                <td style="vertical-align: middle;">'.$value->type.'</td>
                <td style="vertical-align: middle;" align="center">'.$value->date_join.'</td>
                <td style="vertical-align: middle;" align="center">'.$value->expired.'</td>
                <td style="vertical-align: middle;">'.$value->note.'</td>
            </tr>';
        }

        $html .= '</table>
        </body>
        </html>';

        echo $html;
    }

    public function formAgreement()
    {
        $result = [
            "departement_sub" => [],
            "employee_type" => [],
            "departement" => [],
            "agreement" => [],
            "allowance" => [],
            "component" => [],
            "division" => [],
            "employee" => [],
            "position" => [],
            "salary" => [],
            "setup" => [],
            "group" => [],
        ];

        $this->db->distinct()->select(
             "TRIM(number) AS number,"
            ."TRIM(name) AS name,"
            ."TRIM(national_id) AS national,"
            ."TRIM(place_birth) AS place,"
            ."birthday AS date,"
            ."TRIM(gender) AS gender,"
            ."TRIM(address) AS address"
        )->from("employees");
        $result["employee"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(a.number) AS employee,"
            ."DATE(a.date_sign) AS sign,"
            ."DATE(a.date_expired) AS expired,"
            ."COALESCE(TRIM(b.division_id), '') AS division,"
            ."COALESCE(TRIM(b.departement_id), '') AS dept,"
            ."COALESCE(TRIM(b.departement_sub_id), '') AS sub_dept,"
            ."TRIM(a.process) AS process,"
            ."COALESCE(TRIM(b.contract_id), '') AS type,"
            ."TRIM(a.position_id) AS position,"
            ."TRIM(a.group_id) AS group_id,"
            ."TRIM(a.note) AS note"
        )->from("agreements a")
            ->join("employees b", "a.number = b.number")
            ->join("departement_subs c", "b.departement_sub_id = c.id", "left")
        ->where("a.status", '0')
        ->where("b.contract_id !=", '20221119000005')
        ->group_start()
            ->where("DATE(NOW()) BETWEEN DATE_SUB(a.date_expired, INTERVAL 30 DAY) AND a.date_expired")
            ->or_where("a.attachment IS NULL")
        ->group_end();
        // die($this->db->get_compiled_select());
        $result["agreement"] = $this->db->get()->result_object();

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("divisions")->order_by("name");
        $result["division"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(division_id) AS division,"
            ."TRIM(id) AS id,"
            ."TRIM(name) AS name"
        )->from("departements")->order_by("name");
        $result["departement"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(departement_id) AS dept,"
            ."TRIM(id) AS id,"
            ."TRIM(name) AS name"
        )->from("departement_subs")->order_by("name");
        $result["departement_sub"] = $this->db->get()->result_object();

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("contracts")->order_by("name");
        $result["employee_type"] = $this->db->get()->result_object();

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("positions")->order_by("name");
        $result["position"] = $this->db->get()->result_object();

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("groups")->order_by("name");
        $result["group"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(id) AS id,"
            ."TRIM(name) AS name,"
            ."amount"
        )->from("allowances")->order_by("name");
        $result["allowance"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(b.number) AS employee,"
            ."TRIM(a.allowance_id) AS id,"
            ."TRIM(c.name) AS name,"
            ."a.amount"
        )->from("setup_allowances a")
            ->join("employees b", "a.employee_id = b.id")
            ->join("allowances c", "a.allowance_id = c.id")
        ->order_by("name");
        $result["setup"] = $this->db->get()->result_object();

        $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")
        ->from("salary_components")->order_by("name");
        $result["component"] = $this->db->get()->result_object();

        $this->db->distinct()->select(
             "TRIM(b.number) AS employee,"
            ."TRIM(a.salary_component_id) AS component,"
            ."a.amount,"
            ."a.bpjs"
        )->from("setup_salaries a")->join("employees b", "a.employee_id = b.id")->order_by("employee");
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());
        foreach ($get as $index => $row) {
            array_push($result["salary"], [
                "component" => $row->component,
                "employee" => $row->employee,
                "amount" => $row->amount,
                "bpjs" => $row->bpjs
            ]);
        }

        echo json_encode($result);
    }

    public function saveAgreement($act = '')
    {
        $result = ['stat' => 'ok'];

        if ($act == 'attachment') {
            $result['attachment'] = $this->crud->upload(
                'attachment',
                ['pdf', 'xls', 'xlsx'],
                'assets/document/agreement/'
            );
        } else {
            $post = [];
            foreach ($this->input->post() as $key => $value) { $post[$key] = base64_decode($value); }

            $this->db->trans_start();
            $this->crud->update('agreements', ["id" => $post['agreement_id']], ["status" => 1]);
            $stat = $this->db->where('id', $post['agreement_id'])->get('agreements')->row()->status == '1';

            if ($stat) {
                $map = ['M' => 1000,'CM' => 900,'D' => 500,'CD' => 400,'C' => 100,'XC' => 90,'L' => 50,'XL' => 40,'X' => 10,'IX' => 9,'V' => 5,'IV' => 4,'I' => 1];

                $returnValue = '';
                $num = intval(date('m'));
                while ($num > 0) {
                    foreach ($map as $roman => $int) {
                        if ($num >= $int) {
                            $num -= $int;
                            $returnValue .= $roman;
                            break;
                        }
                    }
                }
                $format = "/PKWT/KI/$returnValue/".date('Y');

                $data = [
                    'number' => $post['emp_id'],
                    'position_id' => $post['position_agreement'],
                    'contract_id' => $post['emp_type'],
                    'group_id' => $post['group'],
                    'date_sign' => $post['date_sign'],
                    'date_expired' => $post['date_expired'],
                    'attachment' => (!isset($post['attachment'])) ? NULL : $post['attachment'],
                    'note' => $post['notes'],
                    'process' => $post['process_agreement'],
                    'duration' => str_replace(['year', 'month', 'day'], ['tahun', 'bulan', 'hari'], $post['duration']),
                    'pwkt_no' => $this->db->query("SELECT LPAD(COALESCE(CONVERT(MAX(LEFT(pwkt_no,3)), UNSIGNED), 0) + 1, 3, '0') AS no FROM agreements WHERE pwkt_no LIKE '%$format'")->row()->no.$format,
                ];
                $this->crud->create('agreements', $data);
                $get = $this->db->where($data)->get('agreements')->row();
                $stat = (isset($get->id));
                if ($stat) { $result['id'] = base64_encode($get->id); }
            }

            if ($stat) {
                $data = [
                    "position_id" => $post['position_agreement'],
                    "group_id" => $post['group'],
                    "contract_id" => $post['emp_type'],
                    "date_sign" => $post['date_sign'],
                    "date_expired" => $post['date_expired']
                ];
                $this->crud->update('employees', ["number" => $post['emp_id']], $data);
                $get = $this->db->where("number", $post['emp_id'])->get('employees')->row_array();
                foreach ($data as $key => $value) { if ($get[$key] != $value) { $stat = false; break; } }
                if ($stat) { $emp_id = $get['id']; }
            }

            if ($stat) {
                if ($this->db->where('employee_id', $get['id'])->get('setup_allowances')->num_rows() > 0) {
                    $this->crud->delete('setup_allowances', ['employee_id' => $get['id']]);
                    $stat = $this->db->where('employee_id', $get['id'])->get('setup_allowances')->num_rows() == 0;
                }
            }

            if ($stat) {
                foreach (json_decode($post['allowances']) as $key => $value) {
                    $data = [
                        "employee_id" => $get['id'],
                        "allowance_id" => $value->allow,
                        "amount" => $value->amount
                    ];
                    $this->crud->create('setup_allowances', $data);
                    $stat = $this->db->where($data)->get('setup_allowances')->num_rows() > 0;
                    if (!$stat) { break; }
                }
            }

            if ($stat) {
                $data = [
                    "salary_component_id" => $post['component'],
                    "amount" => $post['amount'],
                    "bpjs" => $post['bpjs']
                ];

                $get_id = $this->db->distinct()->select('TRIM(id) AS id')->from('setup_salaries')
                ->where('employee_id', $emp_id)->get()->row();

                if (isset($get_id->id)) {
                    $this->crud->update('setup_salaries', ["id" => $get_id->id], $data);
                    $this->db->where("id", $get_id->id);
                } else {
                    $this->crud->create('setup_salaries', $data);
                    $this->db->where($data);
                }

                $stat = $this->db->get('setup_salaries')->num_rows() > 0;
            }

            if (!$stat) {
                // $result['stat'] = $this->db->error()['message'];
                $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }
}
