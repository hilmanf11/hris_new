<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') OR exit('No direct script access allowed');

class ScheduleTrainingModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function get_all_data() {
        $query = $this->db->get('lnd_schedule_training');
        return $query->result_array();
    }

    public function get_detail_data($id) {
        $query = $this->db->get_where('lnd_schedule_training', ['id' => $id]);

        if($query->num_rows() > 0) {
            return $query->row_array();
        }

        return null;
    }

    public function get_detail_form_test($id) {
		$this->db->select('lmft.*, ta.trainingActivity');
		$this->db->from('lnd_master_form_test lmft');
		$this->db->join('lnd_schedule_training st', 'st.id = lmft.training_name');
		$this->db->join('lnd_training_activity ta', 'ta.id = st.trainingName');
		$this->db->where('lmft.training_name', $id);
		$query = $this->db->get()->row_array();
        if($query != null) {
            return $query;
        }
        return null;
    }

    // public function insert_data($data) {
    //     $data['createdBy'] = $this->session->username;
    //     $data['createdTime'] = date('Y-m-d H:i:s');
    //     $this->db->insert('lnd_schedule_training', $data);

    //     $query = $this->db->order_by('createdTime', 'activityName')->limit(1)->get('lnd_schedule_training');
    
    //     $record = $query->row();
    
    //     return $record; 
    // }

    public function insert_data($data, $trainingDates = [], $combineTrainer = []) {
        $data['createdBy'] = $this->session->username ?? 'system';
        $data['createdTime'] = date('Y-m-d H:i:s');

        // Use UUID if needed manually
        $data['id'] = $this->uuid->v4(); // Assuming you have a UUID helper or generate one
        $training_id = $data['id'];

        $this->db->trans_start(); // Begin transaction

        // Hilangkan field trainer sebelum insert ke master table
        if (isset($data['trainer'])) {
            unset($data['trainer']);
        }
        if (!empty($data['trainingTrainerId'])) {
            unset($data['trainingTrainerId']);
        }
        // Insert into master table
        $data['updatedBy'] = null;
        $data['updatedTime'] = null;
        $this->db->insert('lnd_schedule_training', $data);

        // Insert trainingDates (detail rows)
        foreach ($trainingDates as $item) {
            if (empty($item['training_date'])) continue;

            $detail = [
                'id' => $this->uuid->v4(),
                'training_id' => $training_id,
                'training_date' => $item['training_date'],
                'batch_count' => $item['batch_count'],
                'week_label' => $item['week'],
                'createdTime' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('lnd_schedule_training_dates', $detail);
        }

		foreach ($combineTrainer as $item) {

			$detail = [
				'id' => $this->uuid->v4(),
				'training_id' => $training_id,
				'trainer_name' => $item['name'],
				'trainer_id' => isset($item['id']) && $item['id'] ? $item['id'] : null,
			];
			$this->db->insert('lnd_schedule_trainers', $detail);
		}

        $this->db->trans_complete(); // Commit or rollback transaction

        // Return the inserted master record
        $query = $this->db->get_where('lnd_schedule_training', ['id' => $training_id]);
        return $query->row();
    }


	public function update_data($id, $data, $trainingDates = [], $combineTrainer = []) {
		$this->db->trans_start(); // Begin transaction

		// Update metadata
		$data['updatedBy'] = $this->session->username ?? 'system';
		$data['updatedTime'] = date('Y-m-d H:i:s');
		
		if (isset($data['trainer'])) {
            unset($data['trainer']);
        }

		// Update master table
		$this->db->where('id', $id);
		$this->db->update('lnd_schedule_training', $data);

		// Delete existing training dates
		$this->db->where('training_id', $id);
		$this->db->delete('lnd_schedule_training_dates');

		// Insert new training dates
		foreach ($trainingDates as $item) {
			if (empty($item['training_date'])) continue;

			$detail = [
				'id' => $this->uuid->v4(),
				'training_id' => $id,
				'training_date' => $item['training_date'],
				'batch_count' => $item['batch_count'],
				'week_label' => $item['week'],
				'createdTime' => date('Y-m-d H:i:s')
			];
			$this->db->insert('lnd_schedule_training_dates', $detail);
		}

		// Delete existing trainers
		$this->db->where('training_id', $id);
		$this->db->delete('lnd_schedule_trainers');

		// Insert new trainers
		foreach ($combineTrainer as $item) {
			$detail = [
				'id' => $this->uuid->v4(),
				'training_id' => $id,
				'trainer_name' => $item['name'],
				'trainer_id' => $item['id'],
			];
			$this->db->insert('lnd_schedule_trainers', $detail);
		}

		$this->db->trans_complete(); // Commit or rollback transaction

		// Return the updated record
		$query = $this->db->get_where('lnd_schedule_training', ['id' => $id]);
		return $query->row();
	}

    public function delete_data($id) {
        $this->db->delete('lnd_schedule_training', ['id' => $id]);
		$this->db->delete('lnd_schedule_training_dates', ['training_id' => $id]);
		$this->db->delete('lnd_schedule_trainers', ['training_id' => $id]);
    }
}
