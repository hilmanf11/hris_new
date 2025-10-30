<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trainee_Training_History extends CI_Controller {
	public function __construct() {
		parent::__construct();
		// Load any models or libraries needed
		$this->load->model('TraineeTrainingHistoryModel');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->model('crud');

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: Content-Type, Authorization');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('session');
	}

	public function index() {
		if (empty($this->session->username)) {
			redirect('error_session');
		} elseif ($this->checkuserAccess($this->id_menu()) > 0) {
			$data['button'] = $this->getbutton($this->id_menu());

			$this->load->view('template/header', $data);
			$this->load->view('lnd/trainee-training-history');
		} else {
			redirect('error_session');
		}
	}

	public function readTrainingName() {
		$this->db->start_cache();
		$this->db->select("a.*,  c.trainingActivity as name, c.induction");
		$this->db->from('lnd_master_form_test a');
        $this->db->join('lnd_schedule_training b', 'b.id = a.training_name', 'left');
        $this->db->join('lnd_training_activity c', 'c.id = b.trainingName', 'left');		
		$this->db->stop_cache();
		$res = $this->db->get()->result_array();
		$this->db->flush_cache(); // Hapus cache query
		echo json_encode($res);

	}

	public function readsEmployeesLeaderUp()
	{
		$post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();
		$this->db->start_cache();
		$this->db->select('a.id, a.name, b.name as positionName');
		$this->db->from('employees a');
		$this->db->join('positions b', 'b.id = a.position_id', 'left');
		// Perbaikan: pastikan perbandingan level sebagai integer agar '01', '02', dst bisa dibandingkan dengan benar
		$this->db->where("(CAST(b.level AS UNSIGNED) <= 5 OR a.departement_sub_id = '20221213000007') AND a.status = 0", null, false);
		if ($get) {
            $this->db->like($get);
        }
		if(!empty($post)) {
			// Jika $post ada isinya, tambahkan kondisi where untuk filter berdasarkan nomor atau nama
			$this->db->group_start();
			$this->db->like('a.number', $post);
			$this->db->or_like('a.name', $post);
			$this->db->group_end();
		}
		$this->db->stop_cache();
		$res = $this->db->get()->result_array();
		$this->db->flush_cache(); // Hapus cache query
		echo json_encode($res);
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

			$this->db->select("a.name, a.number, a.status, a.id as employee_id,
								(CASE
									WHEN CAST(a.date_expired AS CHAR) = '0000-00-00' THEN '-'
									ELSE CAST(a.date_expired AS CHAR)
								END) as date_expired,
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
								l.name as religion_name");
			$this->db->from('lnd_training_history lth');
			$this->db->join('lnd_test_form_detail ltd', "lth.id = ltd.test_id", "left");
			$this->db->join('employees a', 'lth.employee_id = a.id', 'right');
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
			$this->db->where('a.deleted', 0);
			$this->db->where("DATE(ltd.test_date) BETWEEN '".$form['filter_from']."' AND '".$form['filter_to']."'");
			if (!empty($form['filter_training_name'])) {
				$this->db->where('lth.test_id', $form['filter_training_name']);
			}
			$this->db->like("a.departement_id", $form['filter_departement']);
			$this->db->like("a.id", $form['filter_trainee_name']);
			$this->db->group_by('a.name');
			$this->db->group_by('a.number');
			$this->db->group_by('a.status');
			$this->db->group_by('a.id');
			$this->db->group_by('b.users_id_from');
			$this->db->group_by('b.users_id_to');
			$this->db->order_by('a.name', 'ASC');
			$records = $this->db->get()->result_array();

			if(empty($records)) {
				die('<h3 style="color:red;">DATA NOT FOUND</h3>');
			}

			// --- HEADER TABLE: SCORE DIBUAT 1 LINE DENGAN 2 KOLOM ---
			$header = "<thead><tr><th width='20' rowSpan='2'>No</th>";
			$header .= "<th width='180' rowSpan='2'>Training Name</th>";
			$header .= "<th width='180' rowSpan='2'>Trainer Name</th>";
			$header .= "<th width='180' rowSpan='2'>Training Date</th>";
			$header .= "<th width='180' rowSpan='2'>Location</th>";
			$header .= "<th width='160' colSpan='2' style='text-align: center'>Score</th>"; // Colspan 2 untuk 2 sub-kolom
			$header .= "</tr>";
			$header .= "<tr>";
			$header .=  "       <th width='80' style='text-align: center'>Pre-Test</th> ";
			$header .=  "       <th width='80' style='text-align: center'>Post-Test</th>";
			$header .=  "       </tr></thead>";
			// --------------------------------------------------------

			//Config
			$this->db->select('*');
			$this->db->from('config');
			$config = $this->db->get()->row();
			$html = '';
			$logo = base_url('assets/image/logo_bei_2.jpg');
			
			// --- START HTML & CSS (DI LUAR LOOP TRAINEE) ---
			$html .= '<html><head><title>Print Data</title></head><style>
				body {font-family: Arial, Helvetica, sans-serif;}
				#customers {border-collapse: collapse;width: 100%;font-size: 10px;}
				#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}
				#customers tr:nth-child(even){background-color: #f2f2f2;}
				#customers tr:hover {background-color: #ddd;}
				#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}
				#approver {border-collapse: collapse;width: 50%;font-size: 10px; page-break-inside: avoid;}
				#approver td, #approver th {border: 1px solid #ddd;padding: 2px;}
				#approver tr:nth-child(even){background-color: #f2f2f2;}
				#approver tr:hover {background-color: #ddd;}
				#approver th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
				.page-header {position: relative; width: 100%; margin-bottom: 20px;}
				.form-label {position: absolute; top: 0; right: 0; font-size: 10px; font-weight: bold; color: #333;}
				.header-content {display: flex; justify-content: space-between; align-items: flex-start; margin-top: 30px;}
				.company-info {flex: 1; text-align: left;}
				.print-info {flex: 1; text-align: right; margin-top: 10px;}
				
				/* KUNCI PAGE BREAK */
				@media print {
					.page-break {page-break-before: always;}
					.trainee-info-wrapper {
						page-break-inside: avoid !important; /* Pastikan Header dan Info Trainer tidak terpotong */
					}
					tr {page-break-inside: avoid;} /* Usahakan baris tabel tidak terpotong */
					thead {display: table-header-group;}
				}
				.str{ mso-number-format:\@; }
			</style>
			<body>';
			// -----------------------------------------------
			
			if($records) {
				$employeeCount = 0;
				foreach ($records as $dataEmployee) {
					$employeeCount++;
					
					// Add page break for every employee except the first one
					if ($employeeCount > 1) {
						$html .= '<div class="page-break"></div>';
					}
					
					// --- WRAPPER UNTUK HEADER & INFO TRAINEE (PAGE BREAK AVOID) ---
					$html .= '<div class="trainee-info-wrapper">'; 

					// Header Report
					$html .= '<div class="page-header">
								<div class="form-label">FRM-L&D-005 Rev.01</div>
							</div>
							<div class="header-content">
								<div class="company-info">
									<table style="width: 100%;">
										<tr>
											<td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
												<img src="' . $logo . '" width="30">
											</td>
											<td style="font-size: 14px; text-align: left; margin:2px;">
												<b>PT BANSHU ELECTIRC INDONESIA</b><br>
												<small>' . $config->description . '</small>
											</td>
										</tr>
									</table>
								</div>
								<div class="print-info">
									<div style="font-size: 12px; text-align: right;">
										Print Date ' . date("d M Y H:i:s") . ' <br>
										Print By ' . $this->session->username . '  
									</div>
								</div>
							</div>
							<br><br>
							<center>
								<h3 style="margin:0;">Trainee Training History</h3>
								<p style="margin: 0">Period '. $form['filter_from'] .' to '. $form['filter_to'] .'</p>
							</center>
							<br>
							
							<div style="width: 100%; margin-left: 25px;">
								<table>
									<tr><td>Trainee Name</td><td>:</td><td>'.$dataEmployee['name'] .'</td></tr>
									<tr><td>Employee ID</td><td>:</td><td>'. $dataEmployee['number'].'</td></tr>
									<tr><td>Position</td><td>:</td><td>'. $dataEmployee['position_name'].'</td></tr>
									<tr><td>Departement</td><td>:</td><td>'. $dataEmployee['departement_name'].'</td></tr>
									<tr><td>Section</td><td>:</td><td>'. $dataEmployee['departement_sub_name'].'</td></tr>
								</table>                
							</div>';
					
					$html .= '</div>'; // --- TUTUP trainee-info-wrapper ---
					
					// --- TABEL CUSTOMER (LANGSUNG SETELAH WRAPPER INFO) ---
					$html .= '<br>
							<table id="customers" border="1">';
					$html .= $header;
					$html .= '<tbody>';

					$no = 1;
					$content = "";

					// --- QUERY UNTUK DATA TRAINING TRAINEE (GROUP BY ID TES/PELATIHAN) ---
					$this->db->select("
						lta.trainingActivity AS training_name,
						MAX(lth.trainer) AS trainer,
						MAX(ltd.score_pre_test) AS score_pre_test,
						MAX(ltd.score_post_test) AS score_post_test,
						DATE_FORMAT(MAX(ltd.test_date), '%Y-%m-%d') AS test_date,
						'PT. PIRANTI TEKNIK INDONESIA' as location 
					", false); 

					$this->db->from('lnd_training_history lth');
					$this->db->join('lnd_test_form_detail ltd', "lth.id = ltd.test_id", "left");
					$this->db->join('lnd_master_form_test lmft', "lth.test_id = lmft.id", "left");
					$this->db->join('lnd_schedule_training lst', "lmft.training_name = lst.id", "left");
					$this->db->join('lnd_training_activity lta', "lst.trainingName = lta.id", "left");
					$this->db->where('lth.employee_id', $dataEmployee['employee_id']);
					$this->db->where("DATE(ltd.test_date) BETWEEN '".$form['filter_from']."' AND '".$form['filter_to']."'");

					if (!empty($form['filter_trainer_name'])) {
						$this->db->where('lth.trainer', $form['filter_trainer_name']);
					}

					if (!empty($form['filter_training_name'])) {
						$this->db->where('lth.test_id', $form['filter_training_name']);
					}

					// KLAUSA GROUP BY DIPERBAIKI: Hanya berdasarkan ID Tes/Pelatihan
					$this->db->group_by('lth.test_id'); 
					$this->db->order_by('test_date DESC');

					$query = $this->db->get();
					$history_training = $query->result_array();

					if (count($history_training) > 0) {
						foreach ($history_training as $traine) {
							// DATA TRAINING DITAMPILKAN DALAM SATU BARIS
							$html .= "<tr>
									<td>" . $no . "</td>
									<td style='mso-number-format:\@;width:100px'>" . htmlspecialchars($traine['training_name']) . "</td>
									<td style='mso-number-format:\@;width:100px'>" . htmlspecialchars($traine['trainer']) . "</td>
									<td style='mso-number-format:\@;width:100px'>" . htmlspecialchars($traine['test_date']) . "</td>
									<td style='mso-number-format:\@;width:100px'>PT. PIRANTI TEKNIK INDONESIA</td>
									<td style='mso-number-format:\@;width:80px; text-align: center'>" . (isset($traine['score_pre_test']) ? $traine['score_pre_test'] : '0.00') . "</td>
									<td style='mso-number-format:\@;width:80px; text-align: center'>" . (isset($traine['score_post_test']) ? $traine['score_post_test'] : '0.00') . "</td>
								</tr>";
							$no++;
						}
					} else {
						$html .= "<tr><td colspan='7' style='text-align:center'>Data tidak ditemukan</td></tr>";
					}
					
					$html .= '</tbody></table> <br> <br>';
				}
			}
			
			// --- BAGIAN APPROVER (DI LUAR LOOP TRAINEE) ---
			$html .= '<table id="approver" width="20%" style="float: right; font-size: 12px; text-align: center;">';
			$html .= "<tr>
							<th>Disetujui</th>
							<th>Diperiksa</th>
							<th>Dibuat</th>
						</tr>
						<tr>
							<td style='height: 80px'></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Achmad Goesly</td>
							<td>Fajar Budi P.</td>
							<td>Rizal Alip P.</td>
						</tr>
						<tr>
							<td>Manager HRD & GA</td>
							<td>Asst. Manager HRD & GA</td>
							<td>Coordinator LnD</td>
						</tr>
						";
			$html .= '</table>';
			
			// KODE BARU: Membersihkan float dan menghilangkan halaman kosong.
			$html .= '<div style="clear: both;"></div>';

			$html .= '</body></html>';
			echo $html;
		}
	}
}
