<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trainer_Training_History extends CI_Controller {
	public function __construct() {
		parent::__construct();
		// Load any models or libraries needed
		$this->load->model('TrainerTrainingHistoryModel');
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
			$this->load->view('lnd/trainer-training-history');
		} else {
			redirect('error_session');
		}
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

			// if (@$form['filter_employee']== "") {
			// 	die('<h3 style="color:red;">PLEASE CHOOSE TRAINER NAME</h3>');
			// } else {
				$this->db->select("a.name, a.number, a.status, a.id as employee_id, (CASE
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
				$this->db->join('employees a', 'lth.trainer = a.name COLLATE utf8mb4_unicode_ci', 'right', FALSE);
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
				$this->db->where('CAST(g.level AS UNSIGNED) <= 5');
				$this->db->where("DATE(ltd.test_date) BETWEEN '".$form['filter_from']."' AND '".$form['filter_to']."'");
				if (!empty($form['filter_training_name'])) {
					$this->db->where('lth.test_id', $form['filter_training_name']);
				}
				// $this->db->like("a.division_id", $form['filter_division']);
				$this->db->like("a.departement_id", $form['filter_departement']);
				// $this->db->like("a.departement_sub_id", $form['filter_departement_sub']);
				$this->db->like("a.id", $form['filter_employee']);
				// $this->db->like("a.status", $form['filter_status']);
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

				log_message('debug', 'Records data history: ' . json_encode($records));
				$header = "<tr><th width='20' rowSpan='2'>No</th>";
//				for ($i = 0; $i < count($form['filter_column']); $i++) {
//					$header .= "<th>" . strtoupper(strtr($form['filter_column'][$i], "_", " ")) . "</th>";
//				}
				$header .= "<th width='180' rowSpan='2'>Training Name</th>";
				$header .= "<th width='180' rowSpan='2'>Training Date</th>";
				$header .= "<th width='80' colSpan='2' style='text-align: center'>Feedback Training</th>";
				$header .= "</tr>";
				$header .= "<tr>";
				$header .= 	"		<th width='80' style='text-align: center'>Aspect</th> ";
				$header .= 	"		<th width='80' style='text-align: center'>Score</th>";
				$header .= 	"</tr>";
				//Config
				$this->db->select('*');
				$this->db->from('config');
				$config = $this->db->get()->row();

				$html = '';

				$logo = base_url('assets/image/logo_bei_2.jpg');
				foreach ($records as $index => $dataEmployee) {
					// Add page break for every employee except the first one
					if ($index > 0) {
						$html .= '<div style="page-break-before: always;"></div>';
					}
					
					$html .= '<html><head><title>Print Data</title></head><style>
					@media print {
						body {font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 20px;}
						.page-break {page-break-before: always;}
						.page-break-after {page-break-after: always;}
						.no-break {page-break-inside: avoid;}
						table {page-break-inside: auto;}
						tr {page-break-inside: avoid; page-break-after: auto;}
						thead {display: table-header-group;}
						tfoot {display: table-footer-group;}
					}
					body {font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 20px;}
					#customers {border-collapse: collapse;width: 100%;font-size: 10px;page-break-inside: auto;}
					#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}
					#customers tr:nth-child(even){background-color: #f2f2f2;}
					#customers tr:hover {background-color: #ddd;}
					#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}
					#approver {border-collapse: collapse;width: 50%;font-size: 10px;page-break-inside: avoid;}
					#approver td, #approver th {border: 1px solid #ddd;padding: 2px;}
					#approver tr:nth-child(even){background-color: #f2f2f2;}
					#approver tr:hover {background-color: #ddd;}
					#approver th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
					.str{ mso-number-format:\@; }
					.header-section {page-break-inside: avoid;}
					.employee-info {page-break-inside: avoid;}
					.table-section {page-break-inside: auto;}
					.approver-section {page-break-inside: avoid; margin-top: 20px;}
					.employee-page {page-break-inside: avoid;}
					</style>
					<body>
					<div class="employee-page">
					<div class="header-section">
						<center>
							<div style="float: left; font-size: 12px; text-align: left;">
								<table style="width: 100%;">
									<tr>
										<td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
											<img src="' . $logo . '" width="30">
										</td>
										<td style="font-size: 14px; text-align: left; margin:2px;">
											<b>PT BANSHU ELECTRIC INDONESIA</b><br>
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
							<h3 style="margin:0;">Trainer Training History</h3>
							<p style="margin: 0">Period '. $form['filter_from'] .' to '. $form['filter_to'] .'</p>
						</center>
					</div>
					<br>
					<div class="employee-info" style="width: 100%; margin-left: 25px;">
						<table>
							<tr>
								<td>Trainer Name</td>
								<td>:</td>
								<td>'. $dataEmployee['name'].'</td>
							</tr>
							<tr>
								<td>Employee ID</td>
								<td>:</td>
								<td>'. $dataEmployee['number'].'</td>
							</tr>
							<tr>
								<td>Position</td>
								<td>:</td>
								<td>'. $dataEmployee['position_name'].'</td>
							</tr>
							<tr>
								<td>Departement</td>
								<td>:</td>
								<td>'. $dataEmployee['departement_name'].'</td>
							</tr>
							<tr>
								<td>Section</td>
								<td>:</td>
								<td>'. $dataEmployee['departement_sub_name'].'</td>
							</tr>
						</table>              	
					</div>
					<br>
					<table id="customers" border="1">';
					$html .= $header;

					$no = 1;
					$content = "";
					// foreach ($records as $data) {
						if ($dataEmployee['status'] == 0) {
							$status = "ACTIVE";
							$style = "style='background: #82FF6C; text-align:center;'";
						} else {
							$status = "NON ACTIVE";
							$style = "style='background: #FF796C; text-align:center;'";
						}
						
						$this->db->select("
							lta.trainingActivity AS training_name,
							lta.induction,
							GROUP_CONCAT(DISTINCT lth.history_feedback_id) AS history_feedback_id,
							DATE_FORMAT(ltd.test_date, '%Y-%m-%d') AS test_date,
							GROUP_CONCAT(DISTINCT ltd.score_pre_test) AS score_pre_test,
							GROUP_CONCAT(DISTINCT ltd.score_post_test) AS score_post_test
						");

						$this->db->from("lnd_detail_trainer_history ldth");
						$this->db->join('lnd_training_history lth', "ldth.training_history_id = lth.id", "left");
						$this->db->join('lnd_test_form_detail ltd', "lth.id = ltd.test_id", "left");
						$this->db->join('lnd_master_form_test lmft', "lth.test_id = lmft.id", "left");
						$this->db->join('lnd_schedule_training lst', "lmft.training_name = lst.id", "left");
						$this->db->join('lnd_training_activity lta', "lst.trainingName = lta.id", "left");
						$this->db->join('lnd_feedback_history lfh', "lth.history_feedback_id = lfh.id", "left");

						// Filter berdasarkan trainer dan rentang tanggal
						$this->db->where('ldth.trainer_name', $dataEmployee['employee_id']);
						$this->db->where("DATE(ltd.test_date) BETWEEN '".$form['filter_from']."' AND '".$form['filter_to']."'");

						if (!empty($form['filter_training_name'])) {
							$this->db->where('lth.test_id', $form['filter_training_name']);
						}

						// Group by training_name, induction, dan test_date (agar test_date yang sama digabung)
						$this->db->group_by('lta.trainingActivity');
						$this->db->group_by('lta.induction');
						$this->db->group_by('DATE_FORMAT(ltd.test_date, "%Y-%m-%d")');


						// --- Perbaikan untuk error "No tables used" ---
						// Hitung total data TANPA mereset query builder
						// Parameter kedua 'FALSE' mencegah reset Active Record class
						$totalRows = $this->db->count_all_results(null, FALSE);

						// Mengurutkan hasil (ini harus setelah count_all_results jika Anda ingin menghitung sebelum order/limit)
						$this->db->order_by('lta.trainingActivity'); // Mengurutkan berdasarkan nama pelatihan

						// Jalankan query untuk mendapatkan data aktual
						$query = $this->db->get();

						// Ambil hasil
						$history_training = $query->result_array(); // atau $query->result() untuk objek
						
						if (count($history_training) > 0) { 
							foreach ($history_training as $training) {
								// $feedback_history = json_decode($training['json_feedback_history'], true);
								
								// Cek apakah feedback history valid
								// if (!empty($feedback_history) && isset($feedback_history['feedbackItems'])) {
									// $rowspan = count($feedback_history['feedbackItems']);

									if (!empty($training['history_feedback_id'])) {
										$feedback_query = $this->db->select('b.json_response')
																->from('lnd_training_history a')
																->join('lnd_feedback_history b', "a.history_feedback_id = b.id", "left")
																->join('lnd_detail_trainer_history c', 'a.id=c.training_history_id', 'left')
																->join('lnd_test_form_detail d', 'a.id=d.test_id', 'left')
																->where('c.trainer_name', '20230116001455')
																->where('d.test_completed_date IS NOT NULL')
																->get();
										$feedback_result = $feedback_query->result_array();

										// Inisialisasi array untuk menyimpan data per question
										$question_data = array();

										// Loop melalui setiap feedback result
										foreach ($feedback_result as $feedback_data) {
											if (!empty($feedback_data['json_response'])) {
												// Decode JSON response
												$feedback_history = json_decode($feedback_data['json_response'], true);
												
												// Cek apakah feedbackItems ada dan valid
												if (isset($feedback_history['feedbackItems']) && is_array($feedback_history['feedbackItems'])) {
													// Loop melalui setiap feedback item
													foreach ($feedback_history['feedbackItems'] as $item) {
														if (isset($item['question']) && isset($item['point']) && is_numeric($item['point'])) {
															$question = $item['question'];
															
															// Inisialisasi data question jika belum ada
															if (!isset($question_data[$question])) {
																$question_data[$question] = array(
																	'points' => array(),
																	'count' => 0
																);
															}
															
															// Tambahkan point ke array
															$question_data[$question]['points'][] = $item['point'];
															$question_data[$question]['count']++;
														}
													}
												}
											}
										}

										// Hitung rata-rata untuk setiap question dan buat JSON result
										$result_feedback = array();
										foreach ($question_data as $question => $data) {
											$average_score = ($data['count'] > 0) ? round(array_sum($data['points']) / $data['count'], 2) : 0;
											
											// Tentukan feedbackText berdasarkan rata-rata
											$feedbackText = '';
											if ($average_score <= 4) {
												$feedbackText = 'Kurang Baik';
											} elseif ($average_score <= 6) {
												$feedbackText = 'Cukup';
											} elseif ($average_score <= 8) {
												$feedbackText = 'Baik';
											} elseif ($average_score < 10) {
												$feedbackText = 'Baik Sekali';
											} else {
												$feedbackText = 'Sangat Baik';
											}
											
											$result_feedback[] = array(
												'question' => $question,
												'scoreAverage' => $average_score,
												'feedbackText' => $feedbackText
											);
										}

										// Debug: print hasil dalam format JSON
										// echo "Hasil rata-rata feedback:<br>";
										// echo json_encode($result_feedback, JSON_PRETTY_PRINT);
										// echo "<br><br>";
									}
									
									$content = "<tr>
												<td>" . $no . "</td>
												<td style='mso-number-format:\@;width:100px'>" . htmlspecialchars($training['training_name']) . "</td>
												<td style='mso-number-format:\@;width:100px'>" . htmlspecialchars($training['test_date']) . "</td>
												<td style='mso-number-format:\@;width:100px' colspan='2'>
													<table style='width:100%; border:none;'>";
									if (isset($result_feedback) && is_array($result_feedback)) {
										foreach($result_feedback as $itemsFeedback) {
											$content .= "<tr>
															<td style='width:50%; border:none; border-right:1px solid #ccc;'>".htmlspecialchars($itemsFeedback['question'])."</td>
															<td style='width:50%; border:none;'>".htmlspecialchars($itemsFeedback['scoreAverage'])."</td>
														</tr>";
										}
									} else {
										$content .= "<tr><td colspan='2'>No detailed feedback available.</td></tr>";
									}
														
									// foreach($feedback_history['feedbackItems'] as $itemsFeedback) {
									// 	$content .= "<tr>
									// 					<td style='width:50%; border:none; border-right:1px;'> </td>
									// 					<td style='width:50%; border:none;'></td>
									// 				</tr>";
									// // }
									
									$content .= "</table></td></tr>";
									$no++;
									$html .= $content;
								// }
							}
						}
						else {
							$html .= "<tr>
										<td colspan='7' style='text-align:center'>Data tidak ditemukan</td>
									</tr>";
						}


					// }

					$html .= '</table> <br> <br>';
					
				}

				
				$html .= '<table id="approver" width="20%" style="float: right; font-size: 12px; text-align: center;">';
				$html .= "<tr>
							<th>Disetujui</th>
							<th>Diperiksa</th>
							<th>Dibuat</th>
						</tr>
						<tr>";
				$html .= "<td style='height: 80px'></td>";
				$html .= "<td></td>
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
				$html .= "<div style='height:350px;'></div>";
				$html .= '</body></html>';
				echo $html;
			// }
		}
	}
}
