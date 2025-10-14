<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') OR exit('No direct script access allowed');

class RequestTrainingModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function get_all_data() {
        $query = $this->db->get('lnd_request_training');
        return $query->result_array();
    }

    public function get_detail_data($id) {
        $query = $this->db->get_where('lnd_request_training', ['id' => $id]);

        if($query->num_rows() > 0) {
            return $query->row_array();
        }

        return null;
    }

    public function insert_data($data) {
        $data['createdBy'] = $this->session->username;
        $data['createdTime'] = date('Y-m-d H:i:s');
        $this->db->insert('lnd_request_training', $data);

        $query = $this->db->order_by('createdTime')->limit(1)->get('lnd_request_training');
    
        $record = $query->row();
    
        return $data; 
    }

    public function update_data($id, $data) {
        $this->db->where('id', $id);
        $data['updatedBy'] = $this->session->username;
        $data['updatedTime'] = date('Y-m-d H:i:s');

        $this->db->update('lnd_request_training', $data);
        
        $query = $this->db->order_by('updatedTime', 'activityName')->limit(1)->get('lnd_request_training');
    
        $record = $query->row();
    
        return $record; 
    }

    public function delete_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('lnd_request_training');
    }

    public function delete_data_history($id) {
        $this->db->where('trainingRequestId', $id);
        $this->db->delete('lnd_request_training_approvals_history');
    }

    public function insert_data_trainee($data) {
        $data['createdBy'] = $this->session->username;
        $data['createdTime'] = date('Y-m-d H:i:s');
        $data['id'] = $this->uuid();
        $this->db->insert('lnd_request_training_trainee', $data);

        $query = $this->db->order_by('createdTime', 'fullName')->limit(1)->get('lnd_request_training_trainee');
    
        $record = $query->row();
    
        return $record; 
    }

    public function update_data_trainee($id, $data) {
        $this->db->where('id', $id);
        // $data['updatedBy'] = $this->session->username;
        // $data['updatedTime'] = date('Y-m-d H:i:s');

        $this->db->update('lnd_request_training_trainee', $data);
        
        $query = $this->db->limit(1)->get('lnd_request_training_trainee');
    
        $record = $query->row();
    
        return $record; 
    }

    public function delete_data_trainee($idRequestTraining) {
        $this->db->where('trainingRequestId', $idRequestTraining);
        $this->db->delete('lnd_request_training_trainee');
    }

    private function uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

	public function get_detail_data_history_approval($id) {
		$this->db->order_by('approved_date', 'DESC');
		$query = $this->db->get_where('lnd_request_training_approvals_history', ['trainingRequestId' => $id]);

		if($query->num_rows() > 0) {
			return $query->row_array();
		}

		return null;
	}

	public function update_data_request_training_history($id, $data) {
		$this->db->where('id', $id);
		$data['approved_date'] = date('Y-m-d H:i:s');

		$this->db->update('lnd_request_training_approvals_history', $data);

		$query = $this->db->limit(1)->get('lnd_request_training_approvals_history');

		$record = $query->row();

		return $record;
	}
}