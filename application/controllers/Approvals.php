<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Approvals extends CI_Controller

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



    public function approveall()

    {

        $approved_to = $this->input->post('approved_to');

        $approved_by = $this->input->post('approved_by');

        $table_name = $this->input->post('table_name');



        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "approved_by" => $approved_by]);

        

        foreach ($datas as $data) {

            $id = $data->id;

            $user = $this->crud->read('users', [], ["username" => $data->approved_by]);

            $approval = $this->crud->read('approvals', [], ["table_name" => $table_name, "departement_id" => @$user->departement_id]);

            

            if ($data->approved == 1) {

                $users_id = @$approval->user_approval_2;

                $approved = 2;

            } elseif ($data->approved == 2) {

                $users_id = @$approval->user_approval_3;

                $approved = 3;

            } elseif ($data->approved == 3) {

                $users_id = @$approval->user_approval_4;

                $approved = 4;

            } elseif ($data->approved == 4) {

                $users_id = @$approval->user_approval_5;

                $approved = 5;

            } else {

                $users_id = "";

                $approved = 0;

            }



            //Approval Mutation

            if ($table_name == "mutations" && $users_id == "") {

                $mutations = $this->crud->read('mutations', [], ["id" => $id]);

                $postEmployee = array(

                    "division_id" => $mutations->division_id,

                    "departement_id" => $mutations->departement_id,

                    "departement_sub_id" => $mutations->departement_sub_id

                );



                if($mutations->type == "PERMANENT"){

                    $send = $this->db->update('employees', $postEmployee, ["id" => $mutations->employee_id]);

                }

            }



            //Approval Agreement

            if ($table_name == "agreements" && $users_id == "") {

                $agreements = $this->crud->read('agreements', [], ["id" => $id]);

                $postEmployee = array(

                    "position_id" => $agreements->position_id,

                    "group_id" => $agreements->group_id,

                    "contract_id" => $agreements->contract_id,

                    "date_sign" => $agreements->date_sign,

                    "date_expired" => $agreements->date_expired,

                );



                $send = $this->db->update('employees', $postEmployee, ["number" => $agreements->number]);

            }



            $values = array(

                "approved_by" => $this->session->username,

                "approved_date" => date('Y-m-d H:i:s'),

                "approved_to" => $users_id,

                "approved" => $approved,

            );



            $send = $this->db->update($table_name, $values, ["id" => $id]);

        }



        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

    }



    public function approvePayrollHarianLepas()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');

        $period_start = base64_decode($this->input->post('period_start'));

        $period_end = base64_decode($this->input->post('period_end'));



        $this->db->select('a.id');

        $this->db->from('payroll_harian_lepas a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('b.group_id', $id);

        $this->db->where('a.period_start', $period_start);

        $this->db->where('a.period_end', $period_end);

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->where('b.deleted', 0);

        $this->db->where('a.status', 0);

        $this->db->group_by('a.employee_id');

        $records = $this->db->get()->result_array();



        foreach ($records as $record) {

            $payroll_id = $record['id'];



            $data = $this->crud->read($tablename, [], ["id" => $payroll_id]);

            $user = $this->crud->read('users', [], ["username" => $data->approved_by]);

            $approval = $this->crud->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);



            if ($data->approved == 1) {

                $users_id = @$approval->user_approval_2;

                $approved = 2;

            } elseif ($data->approved == 2) {

                $users_id = @$approval->user_approval_3;

                $approved = 3;

            } elseif ($data->approved == 3) {

                $users_id = @$approval->user_approval_4;

                $approved = 4;

            } elseif ($data->approved == 4) {

                $users_id = @$approval->user_approval_5;

                $approved = 5;

            } else {

                $users_id = "";

                $approved = 0;

            }



            $values = array(

                "approved_by" => $this->session->username,

                "approved_date" => date('Y-m-d H:i:s'),

                "approved_to" => $users_id,

                "approved" => $approved,

            );



            $send = $this->db->update($tablename, $values, ["id" => $payroll_id]);

        }



        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

    }



    public function approve()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');

        $data = $this->crud->read($tablename, [], ["id" => $id]);

        $user = $this->crud->read('users', [], ["username" => $data->approved_by]);

        $approval = $this->crud->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);



        if ($data->approved == 1) {

            $users_id = @$approval->user_approval_2;

            $approved = 2;

        } elseif ($data->approved == 2) {

            $users_id = @$approval->user_approval_3;

            $approved = 3;

        } elseif ($data->approved == 3) {

            $users_id = @$approval->user_approval_4;

            $approved = 4;

        } elseif ($data->approved == 4) {

            $users_id = @$approval->user_approval_5;

            $approved = 5;

        } else {

            $users_id = "";

            $approved = 0;

        }



        //Approval Mutation

        if ($tablename == "mutations" && $users_id == "") {

            $mutations = $this->crud->read('mutations', [], ["id" => $id]);

            $postEmployee = array(

                "division_id" => $mutations->division_id,

                "departement_id" => $mutations->departement_id,

                "departement_sub_id" => $mutations->departement_sub_id

            );



            if($mutations->type == "PERMANENT"){

                $send = $this->db->update('employees', $postEmployee, ["id" => $mutations->employee_id]);

                if($send){

                    echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

                } else {

                    echo log_message('error', 'There is an error in your system or data');

                }

            }

        }



        //Approval Agreement

        if ($tablename == "agreements" && $users_id == "") {

            $agreements = $this->crud->read('agreements', [], ["id" => $id]);

            $postEmployee = array(

                "position_id" => $agreements->position_id,

                "group_id" => $agreements->group_id,

                "contract_id" => $agreements->contract_id,

                "date_sign" => $agreements->date_sign,

                "date_expired" => $agreements->date_expired,

            );



            $send = $this->db->update('employees', $postEmployee, ["number" => $agreements->number]);

            if($send){

                echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

            } else {

                echo log_message('error', 'There is an error in your system or data');

            }

        }



        $values = array(

            "approved_by" => $this->session->username,

            "approved_date" => date('Y-m-d H:i:s'),

            "approved_to" => $users_id,

            "approved" => $approved,

        );



        $send = $this->db->update($tablename, $values, ["id" => $id]);

        if($send){

            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

        } else {

            echo log_message('error', 'There is an error in your system or data');

        }

    }



    public function approvePayrolls()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');

        $period_start = base64_decode($this->input->post('period_start'));

        $period_end = base64_decode($this->input->post('period_end'));



        $this->db->select('a.id');

        $this->db->from('payrolls a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('b.group_id', $id);

        $this->db->where('a.period_start', $period_start);

        $this->db->where('a.period_end', $period_end);

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->where('b.deleted', 0);

        $this->db->where('a.status', 0);

        $this->db->group_by('a.employee_id');

        $records = $this->db->get()->result_array();



        foreach ($records as $record) {

            $payroll_id = $record['id'];



            $data = $this->crud->read($tablename, [], ["id" => $payroll_id]);

            $user = $this->crud->read('users', [], ["username" => $data->approved_by]);

            $approval = $this->crud->read('approvals', [], ["table_name" => $tablename, "departement_id" => @$user->departement_id]);



            if ($data->approved == 1) {

                $users_id = @$approval->user_approval_2;

                $approved = 2;

            } elseif ($data->approved == 2) {

                $users_id = @$approval->user_approval_3;

                $approved = 3;

            } elseif ($data->approved == 3) {

                $users_id = @$approval->user_approval_4;

                $approved = 4;

            } elseif ($data->approved == 4) {

                $users_id = @$approval->user_approval_5;

                $approved = 5;

            } else {

                $users_id = "";

                $approved = 0;

            }



            $values = array(

                "approved_by" => $this->session->username,

                "approved_date" => date('Y-m-d H:i:s'),

                "approved_to" => $users_id,

                "approved" => $approved,

            );

            

            $send = $this->db->update($tablename, $values, ["id" => $payroll_id]);

        }



        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));

    }



    public function disapproveall()

    {

        $approved_by = $this->input->post('approved_by');

        $approved_to = $this->input->post('approved_to');

        $table_name = $this->input->post('table_name');

        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "approved_by" => $approved_by]);



        /* Default */

        foreach ($datas as $data) {

            $send = $this->crud->delete($table_name, ["id" => $data->id]);

        }



        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));

    }



    public function disapprove()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');



        /* Default */

        $send = $this->crud->delete($tablename, ["id" => $id]);

        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));

    }



    public function disapprovePayrolls()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');

        $period_start = base64_decode($this->input->post('period_start'));

        $period_end = base64_decode($this->input->post('period_end'));



        $this->db->select('a.id');

        $this->db->from('payrolls a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('b.group_id', $id);

        $this->db->where('a.period_start', $period_start);

        $this->db->where('a.period_end', $period_end);

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.status', 0);

        $this->db->group_by('a.employee_id');

        $records = $this->db->get()->result_array();



        foreach ($records as $record) {

            $payroll_id = $record['id'];

            $send = $this->crud->delete($tablename, ["id" => $payroll_id]);

        }



        echo $send;

    }



    public function disapprovePayrollHarianLepas()

    {

        $id = $this->input->post('id');

        $tablename = $this->input->post('tablename');

        $period_start = base64_decode($this->input->post('period_start'));

        $period_end = base64_decode($this->input->post('period_end'));



        $this->db->select('a.id');

        $this->db->from('payroll_harian_lepas a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('b.group_id', $id);

        $this->db->where('a.period_start', $period_start);

        $this->db->where('a.period_end', $period_end);

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.status', 0);

        $this->db->group_by('a.employee_id');

        $records = $this->db->get()->result_array();



        foreach ($records as $record) {

            $payroll_id = $record['id'];

            $send = $this->crud->delete($tablename, ["id" => $payroll_id]);

        }



        echo $send;

    }



    public function approvalCount()

    {

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('cash_carries a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $cash_carries = $this->db->get()->result_object();



        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('payrolls a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $payrolls = $this->db->get()->result_object();



        // $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        // $this->db->from('payroll_harian_lepas a');

        // $this->db->join('users b', 'a.approved_by = b.username');

        // $this->db->join('users c', 'a.approved_to = c.username');

        // $this->db->where('a.approved_to', $this->session->username);

        // $this->db->group_by('a.approved_by');

        // $payroll_harian_lepas = $this->db->get()->result_object();

        

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('setup_salaries a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $setup_salaries = $this->db->get()->result_object();



        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('permits a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $permits = $this->db->get()->result_object();



        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('change_days a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $change_days = $this->db->get()->result_object();

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');
		$this->db->from('lnd_request_training_approvals_history a');
		$this->db->join('users b', 'a.approved_by = b.username');
		$this->db->join('users c', 'a.approved_to = c.username');
        $this->db->join('lnd_request_training lrt', 'a.trainingRequestId = lrt.requestTrainingId');
		$this->db->where('a.approved_to', $this->session->username);
        $this->db->where('a.status', 0);
		$this->db->group_by('a.approved_by');
		$lnd_request_training_approvals_history = $this->db->get()->result_object();



        $totalRows = (count($cash_carries) + count($payrolls) + count($setup_salaries) + count($permits) + count($change_days) + count($lnd_request_training_approvals_history));

        if ($totalRows > 0) {

            echo '<span class="badge">' . $totalRows . '</span>';

        } else {

            echo '';

        }

    }



    public function approvalList()

    {

        //Cash Carries

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar, e.name AS group_name');

        $this->db->from('cash_carries a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->join('employees d', 'a.employee_id = d.id');

        $this->db->join('groups e', 'd.group_id = e.id');

        $this->db->where('a.approved_to', $this->session->username);

        // $this->db->group_by('a.approved_by');
        $this->db->group_by(['a.approved_by', 'a.approved_to', 'b.name', 'b.avatar', 'e.name']);

        $cash_carries = $this->db->get()->result_object();
        // die($this->db->last_query());



        //Payrolls

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('payrolls a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $payrolls = $this->db->get()->result_object();



        //Payroll Harian Lepas

        // $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        // $this->db->from('payroll_harian_lepas a');

        // $this->db->join('users b', 'a.approved_by = b.username');

        // $this->db->join('users c', 'a.approved_to = c.username');

        // $this->db->where('a.approved_to', $this->session->username);

        // $this->db->group_by('a.approved_by');

        // $payroll_harian_lepas = $this->db->get()->result_object();



        //Setup Salary

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('setup_salaries a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $setup_salaries = $this->db->get()->result_object();



        //Permits

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('permits a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $permits = $this->db->get()->result_object();



        //Change Days

        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');

        $this->db->from('change_days a');

        $this->db->join('users b', 'a.approved_by = b.username');

        $this->db->join('users c', 'a.approved_to = c.username');

        $this->db->where('a.approved_to', $this->session->username);

        $this->db->group_by('a.approved_by');

        $change_days = $this->db->get()->result_object();


        $this->db->select('b.name as fullname, a.approved_to, a.approved_by, b.avatar');
		$this->db->from('lnd_request_training_approvals_history a');
		$this->db->join('users b', 'a.approved_by = b.username');
		$this->db->join('users c', 'a.approved_to = c.username');
		$this->db->join('lnd_request_training lrt', 'a.trainingRequestId = lrt.requestTrainingId');
		$this->db->where('a.approved_to', $this->session->username);
        $this->db->where('a.status', 0);
		$this->db->group_by('a.approved_by');
		$lnd_request_training_approvals_history = $this->db->get()->result_object();



        if (count($cash_carries) > 0) {

            foreach ($cash_carries as $cash_carry) {
                $name = 'cash_carries'.(($cash_carry->group_name == 'HARIAN LEPAS') ? '_harian_lepas' : '');

                $this->approvalMessage($cash_carry->avatar, $cash_carry->fullname, $cash_carry->approved_to, $cash_carry->approved_by, $name);
            }

        }



        if(count($payrolls) > 0) {

            foreach ($payrolls as $payroll) {

                $this->approvalMessage($payroll->avatar, $payroll->fullname, $payroll->approved_to, $payroll->approved_by, "payrolls");

            }

        }



        // if(count($payroll_harian_lepas) > 0) {

        //     foreach ($payroll_harian_lepas as $payroll_hl) {

        //         $this->approvalMessage($payroll_hl->avatar, $payroll_hl->fullname, $payroll_hl->approved_to, $payroll_hl->approved_by, "payroll_harian_lepas");

        //     }

        // }



        if(count($setup_salaries) > 0) {

            foreach ($setup_salaries as $setup_salarie) {

                $this->approvalMessage($setup_salarie->avatar, $setup_salarie->fullname, $setup_salarie->approved_to, $setup_salarie->approved_by, "setup_salaries");

            }

        }



        if(count($permits) > 0) {

            foreach ($permits as $permit) {

                $this->approvalMessage($permit->avatar, $permit->fullname, $permit->approved_to, $permit->approved_by, "permits");

            }

        }



        if(count($change_days) > 0) {

            foreach ($change_days as $change_day) {

                $this->approvalMessage($change_day->avatar, $change_day->fullname, $change_day->approved_to, $change_day->approved_by, "change_days");

            }

        }

        if(count($lnd_request_training_approvals_history) > 0) {
			foreach ($lnd_request_training_approvals_history as $val) {
				$this->approvalMessage($val->avatar, $val->fullname, $val->approved_to, $val->approved_by, "lnd_request_training_approvals_history");
			}
		}

    }



    public function approvalMessage($foto, $fullname, $approved_to, $approved_by, $table){

        if ($foto == "") {

            $avatar = base_url('assets/image/users/default.png');

        } else {

            $avatar = $foto;

        }



        $link = "approvalDetail('$table', '$approved_to', '$approved_by')";

        echo '  <li class="list-isi">

                    <a onclick="' . $link . '">

                        <table style="width: 100%;">

                            <tr>

                                <td>

                                    <div class="icon-container">

                                        <img src="' . $avatar . '" class="user-online" />

                                        <div class="status-circle"></div>

                                    </div>

                                </td>

                                <td style="padding-left: 10px;">

                                    <b>' . $fullname . '</b><br>

                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>

                                </td>

                            </tr>

                        </table>

                    </a>

                </li>';

    }



    public function approvalUsers($approved_to, $approved_by)

    {

        $this->db->select('*');

        $this->db->from('users a');

        $this->db->where('approved_to', $approved_to);

        $this->db->where('approved_by', $approved_by);

        $this->db->order_by('created_date', 'DESC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalAgreements($approved_to, $approved_by)

    {

        $this->db->select('a.*, 

                b.name as employee_name, 

                c.name as division_name, 

                d.name as departement_name, 

                e.name as departement_sub_name,

                e.type, 

                f.name as position_name,

                g.name as contract_name');

        $this->db->from('agreements a');

        $this->db->join('employees b', 'a.number = b.number');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('positions f', 'f.id = a.position_id');

        $this->db->join('contracts g', 'g.id = a.contract_id');

        $this->db->join('groups h', 'h.id = a.group_id');

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalMutations($approved_to, $approved_by)

    {

        $this->db->select('a.*, 

                b.number as employee_number, 

                b.name as employee_name, 

                c.name as division_name, 

                d.name as departement_name,

                e.name as departement_sub_name');

        $this->db->from('mutations a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'a.division_id = c.id');

        $this->db->join('departements d', 'a.departement_id = d.id');

        $this->db->join('departement_subs e', 'a.departement_sub_id = e.id');

        $this->db->where('a.deleted', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalPermits($approved_to, $approved_by)

    {

        $this->db->select('a.*,

                c.number as employee_number,

                c.name as employee_name,

                d.name as division_name,

                e.name as departement_name,

                f.name as departement_sub_name,

                g.name as permit_type_name,

                h.name as reason_name,

                i.name as request_name

            ');

        $this->db->from('permits a');

        $this->db->join('employees c', 'a.employee_id = c.id');

        $this->db->join('divisions d', 'c.division_id = d.id');

        $this->db->join('departements e', 'c.departement_id = e.id');

        $this->db->join('departement_subs f', 'c.departement_sub_id = f.id');

        $this->db->join('permit_types g', 'a.permit_type_id = g.id');

        $this->db->join('reasons h', 'a.reason_id = h.id');

        $this->db->join('users i', 'a.approved_by = i.username');

        $this->db->where('c.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->order_by('a.permit_date', 'DESC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalOvertimes($approved_to, $approved_by)

    {

        $this->db->select('a.*,

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname

            ');

        $this->db->from('overtimes a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.deleted', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.trans_date');

        $this->db->group_by('a.employee_id');

        $this->db->group_by('a.type');

        $this->db->order_by('a.trans_date', 'DESC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalCashCarries($approved_to, $approved_by)

    {

        $this->db->select('a.*, 

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname

            ');

        $this->db->from('cash_carries a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.deleted', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.trans_date');

        $this->db->group_by('a.employee_id');

        $this->db->group_by('a.type');

        $this->db->order_by('a.trans_date', 'DESC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalChangeDays($approved_to, $approved_by)

    {

        $this->db->select('a.*,

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname

            ');

        $this->db->from('change_days a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.deleted', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.start');

        $this->db->group_by('a.employee_id');

        $this->db->order_by('a.created_date', 'DESC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalPayrolls($approved_to, $approved_by)

    {

        $this->db->select('a.period_start, a.period_end, b.group_id, d.name as group_name, c.name, a.created_date, COUNT(a.employee_id) as employee, SUM(a.net_income) as amount');

        $this->db->from('payrolls a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('users c', 'a.approved_by = c.username');

        $this->db->join('groups d', 'b.group_id = d.id');

        // $this->db->join('privilege_groups e', "e.group_id = d.id and e.status = '1' and a.approved_to = e.username");

        $this->db->where('b.deleted', 0);

        // $this->db->where('b.status', 0);

        $this->db->where('a.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('b.group_id');

        $records = $this->db->get()->result_array();



        $total_employee = 0;

        $total_amount = 0;

        foreach ($records as $record) {

            $total_employee += $record['employee'];

            $total_amount += $record['amount'];

        }



        $data['total'] = count($records);

        $data['rows'] = $records;

        $data['footer'] = array(['group_name' => 'GRAND TOTAL', 'employee' => $total_employee, 'amount' => $total_amount, 'action' => "-"]);



        die(json_encode($data));

    }



    public function approvalPayrollHarianLepas($approved_to, $approved_by)

    {

        $this->db->select('a.period_start, a.period_end, b.group_id, d.name as group_name, c.name, a.created_date, COUNT(a.employee_id) as employee, SUM(a.net_income) as amount');

        $this->db->from('payroll_harian_lepas a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('users c', 'a.approved_by = c.username');

        $this->db->join('groups d', 'b.group_id = d.id');

        $this->db->where('b.deleted', 0);

        $this->db->where('a.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('b.group_id');

        $records = $this->db->get()->result_array();



        $total_employee = 0;

        $total_amount = 0;

        foreach ($records as $record) {

            $total_employee += $record['employee'];

            $total_amount += $record['amount'];

        }



        $data['total'] = count($records);

        $data['rows'] = $records;

        $data['footer'] = array(['group_name' => 'GRAND TOTAL', 'employee' => $total_employee, 'amount' => $total_amount, 'action' => "-"]);



        die(json_encode($data));

    }



    public function approvalSetupSalary($approved_to, $approved_by)

    {

        $this->db->select('a.*,

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname,

                h.name as salary_component_name

            ');

        $this->db->from('setup_salaries a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->join('salary_components h', 'a.salary_component_id = h.id', 'left');

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.employee_id');

        $this->db->order_by('b.name', 'ASC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalWarningLetters($approved_to, $approved_by)

    {

        $this->db->select('a.*,

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname,

                h.name as violation_name

            ');

        $this->db->from('warning_letters a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->join('violations h', 'a.violation_id = h.id', 'left');

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.employee_id');

        $this->db->order_by('b.name', 'ASC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function approvalResignations($approved_to, $approved_by)

    {

        $this->db->select('a.*, 

                g.users_id_from as status_check,

                g.users_id_to as status_notification, 

                g.updated_date as status_date,

                c.name as division_name,

                d.name as departement_name,

                e.name as departement_sub_name,

                b.number as employee_number,

                b.name as employee_name,

                f.name as fullname,

                h.name as reason_name

            ');

        $this->db->from('resignations a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('divisions c', 'b.division_id = c.id');

        $this->db->join('departements d', 'b.departement_id = d.id');

        $this->db->join('departement_subs e', 'b.departement_sub_id = e.id');

        $this->db->join('users f', "a.approved_by = f.username");

        $this->db->join('notifications g', "a.id = g.table_id and g.table_name = 'resignations'", 'left');

        $this->db->join('reason_resignations h', 'a.reason_resignation_id = h.id', 'left');

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.approved_to', $approved_to);

        $this->db->where('a.approved_by', $approved_by);

        $this->db->group_by('a.employee_id');

        $this->db->order_by('b.name', 'ASC');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }

    public function approveLnd()
	{
		$id = $this->input->post('id');
		$tablename = $this->input->post('tablename');
        $createdBy = $this->input->post('createdBy');
		$data = $this->crud->read($tablename, [], ["id" => $id, "status" => 0]);
        $user = $this->crud->read('users', [], ["username" => $createdBy]);
		$this->db->where('table_name', $tablename);
        $this->db->group_start();
        $this->db->where('departement_id', @$user->departement_id);
        $this->db->or_where('departement_sub_id', @$user->departement_id);
        $this->db->group_end();
        $approval = $this->db->get('approvals')->row();

		// Perbaikan logika approval LND
		$users_id = "";
		$approved = 0;
		$approval_data = null;

		if (isset($data->approved)) {
			switch ($data->approved) {
				case 1:
					$users_id = isset($approval->user_approval_2) ? $approval->user_approval_2 : "";
					$approved = 2;
					$approval_data = empty($approval->user_approval_2) ? 'COMPLETED' : null;
					break;
				case 2:
					$users_id = isset($approval->user_approval_3) ? $approval->user_approval_3 : "";
					$approved = 3;
					$approval_data = empty($approval->user_approval_3) ? 'COMPLETED' : null;
					break;
				case 3:
					$users_id = isset($approval->user_approval_4) ? $approval->user_approval_4 : "";
					$approved = 4;
					$approval_data = empty($approval->user_approval_4) ? 'COMPLETED' : null;
					break;
				case 4:
					$users_id = isset($approval->user_approval_5) ? $approval->user_approval_5 : "";
					$approved = 5;
					$approval_data = empty($approval->user_approval_5) ? 'COMPLETED' : null;
					break;
				default:
					$users_id = "";
					$approved = 0;
					$approval_data = null;
					break;
			}
		}

		$valuesUpdate = array(
			"status" => 1,
			"id" => $id,
		);

		$resUpdated = $this->db->update($tablename, $valuesUpdate, ["id" => $id]);

		$values = array(
            "id" => $this->uuid(),
			"trainingRequestId" => $data->trainingRequestId,
			"approved_by" => $this->session->username,
			"approved_date" => date('Y-m-d H:i:s'),
			"approved_to" => $users_id,
			"approved" => $approved,
			"status" => 0,
            "approval_data" => $approval_data
		);

		$send = $this->db->insert($tablename, $values, ["id" => $id]);
		if($send && $resUpdated){
			echo json_encode(array("title" => "Approved", "message" => "Data Approved Successful", "theme" => "success"));
		} else {
			echo log_message('error', 'There is an error in your system or data');
		}
	}

    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

	public function disapproveLnd()
	{
		$id = $this->input->post('id');
		$tablename = $this->input->post('tablename');
		$remark = $this->input->post('remark');

		// Ambil data approval saat ini
		$data = $this->crud->read($tablename, [], ["id" => $id]);
		if (!$data) {
			echo json_encode(array("title" => "Error", "message" => "Data not found", "theme" => "error"));
			return;
		}

		// Jika approval == 1, lakukan disapprove seperti biasa
		if (isset($data->approved) && intval($data->approved) == 1) {
			$values = array(
				"status" => -1,
				"id" => $id,
				"approval_data" => $remark
			);
			$send = $this->db->update($tablename, $values, ["id" => $id]);
		} else {
			// Jika approval > 1, hapus semua data approval history kecuali approved == 1, dan reset status ke 0
			$trainingRequestId = isset($data->trainingRequestId) ? $data->trainingRequestId : null;
			if ($trainingRequestId) {
				// Hapus semua data approval history dengan approved > 1
				$this->db->where('trainingRequestId', $trainingRequestId);
				$this->db->where('approved >', 1);
				$this->db->delete($tablename);

				// Update status pada approval history dengan approved == 1 menjadi 0 dan set approval_data remark
				$this->db->where('trainingRequestId', $trainingRequestId);
				$this->db->where('approved', 1);
				$this->db->update($tablename, [
					"status" => 0,
					"approval_data" => $remark
				]);
			}
			// Update status utama ke 0 (proses revisi)
			$this->db->where('id', $id);
			$this->db->update('lnd_request_training', [
				"status" => 0
			]);
		}

		echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
	}

    public function approvalRequestTraining($approved_to, $approved_by)
	{
		$this->db->select('a.*, 
            DATE_FORMAT(a.suggestDateTraining, "%Y-%m-%d") as suggestDate,
			a.status as statusTraining, 
			a.status as statusApproval, 
			rth.approved_by, 
			rth.approved_date, 
			rth.id as approvalHistoryId,
			COALESCE(e.name, a.trainer_name) as trainerName'
		);
		$this->db->from('lnd_request_training a');
		$this->db->join('lnd_request_training_approvals_history rth', 'a.requestTrainingId = rth.trainingRequestId', 'left');
		$this->db->join('employees e', 'a.trainer_name = e.id', 'left');
		$this->db->where('rth.status', 0);
		$this->db->where('rth.approved_to', $approved_to);
		$this->db->where('rth.approved_by', $approved_by);
		$records = $this->db->get()->result_array();

		die(json_encode($records));
	}

}

