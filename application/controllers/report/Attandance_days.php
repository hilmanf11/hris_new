<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Attandance_days extends CI_Controller
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
            $this->load->view('report/attandance_days');
        } else {
            redirect('error_access');
        }
    }

    function cekStatusMasuk($shift, $masuk) {
        // Konversi waktu masuk ke format detik
        $masukTime = strtotime($masuk);
        
        // Variabel untuk menyimpan jarak waktu terkecil
        $closestShift = null;
        $minDiff = null;
    
        // Cari waktu shift terdekat
        foreach ($shift as $shiftTime) {
            $shiftTimeInSeconds = strtotime($shiftTime);
            $diff = abs($masukTime - $shiftTimeInSeconds);
    
            if (is_null($minDiff) || $diff < $minDiff) {
                $minDiff = $diff;
                $closestShift = $shiftTime;
            }
        }
    
        // Periksa apakah waktu masuk lebih awal atau lebih lambat dari shift terdekat
        if ($masukTime <= strtotime($closestShift)) {
            return "ON TIME";
        } else {
            return "LATE";
        }
    }

    function getShiftDetails($employee_id) {
        $this->db->select("d.start, d.end, d.working");
        $this->db->from('shift_employees b');
        $this->db->join('shifts c', 'c.id = b.shift_id');
        $this->db->join('shift_details d', 'd.shift_id = c.id');
        $this->db->where('b.employee_id', $employee_id);
        $this->db->order_by('d.start', 'ASC');
        $shifts = $this->db->get()->result_array();
        
        $shiftDetails = [];
        foreach ($shifts as $shift) {
            $start = date('H:i:s', strtotime($shift['start']));
            $end = date('H:i:s', strtotime($shift['end']));
            $working = $shift['working'];
            $shiftDetails[] = $start . '-' . $end . ' (' . $working . ')';
        }
        
        return implode('<br>', $shiftDetails);
    }

    // Helper function untuk memfilter attendance berdasarkan shift
    private function filterAttendanceByShift($attendances, $filter_shift) {
        if (empty($filter_shift)) {
            return $attendances;
        }
        
        $filtered_attendances = [];
        foreach ($attendances as $attendance) {
            $time_in = $attendance['time_in'] ?? '';
            
            // Skip jika time_in kosong
            if (empty($time_in)) {
                continue;
            }
            
            $show_this_attendance = false;
            
            switch ($filter_shift) {
                case "1": // Shift 1: 05:00-09:00
                    if ($time_in >= '05:00:00' && $time_in <= '09:00:00') {
                        $show_this_attendance = true;
                    }
                    break;
                case "2": // Shift 2: 15:00-22:00
                    if ($time_in >= '15:00:00' && $time_in <= '22:00:00') {
                        $show_this_attendance = true;
                    }
                    break;
                case "3": // Shift 3: 22:30-00:00 (melewati tengah malam)
                    if ($time_in >= '22:30:00' || $time_in <= '00:00:00') {
                        $show_this_attendance = true;
                    }
                    break;
            }
            
            if ($show_this_attendance) {
                $filtered_attendances[] = $attendance;
            }
        }
        
        return $filtered_attendances;
    }

    // Helper function untuk membagi array menjadi batch kecil
    private function chunkArray($array, $chunk_size = 1000) {
        return array_chunk($array, $chunk_size);
    }

    // Helper function untuk query dengan batch processing
    private function getDataInBatches($table, $select, $joins = [], $where_field, $values, $additional_where = '', $order_by = '') {
        $all_results = [];
        
        // Safety check untuk array kosong
        if (empty($values)) {
            return $all_results;
        }
        
        $chunks = $this->chunkArray($values, 500); // Batasi 500 item per query
        
        foreach ($chunks as $chunk) {
            try {
                $this->db->select($select);
                $this->db->from($table);
                
                // Apply joins if any
                foreach ($joins as $join) {
                    $this->db->join($join['table'], $join['condition'], $join['type'] ?? 'inner');
                }
                
                $this->db->where_in($where_field, $chunk);
                
                // Apply additional where conditions
                if (!empty($additional_where)) {
                    $this->db->where($additional_where);
                }
                
                // Apply order by if specified
                if (!empty($order_by)) {
                    $this->db->order_by($order_by);
                }
                
                $results = $this->db->get()->result_array();
                $all_results = array_merge($all_results, $results);
                
            } catch (Exception $e) {
                // Log error dan lanjutkan dengan chunk berikutnya
                error_log("Error in getDataInBatches for table $table: " . $e->getMessage());
                continue;
            }
        }
        
        return $all_results;
    }

    public function printcsv(){
        if ($this->input->get()) {

            header("Content-type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=attandance_days_" . time() . ".csv");
            $output = fopen("php://output", "w");

            fputcsv($output, array(
                "No","Departement", "Departement Sub", "Employee ID", "Employee Name", "Shift", "Shift Detail", "Join Date", "Resign Date", "Trans Date", "Time In/Out",
                "Status", "Permit", "Description"
            ));

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_permit_type = $this->input->get('filter_permit_type');
            $filter_status_in = $this->input->get('filter_status_in');
            $filter_status = $this->input->get('filter_status');
            $filter_status_employee = $this->input->get('filter_status_employee');
            $filter_shift = $this->input->get('filter_shift');

            // OPTIMIZATION: Get all employees data in batches to avoid regex too large error
            $employee_records = [];
            
            // Get all divisions, departments, and department subs first
            $this->db->select('id, name');
            $this->db->from('divisions');
            $divisions = $this->db->get()->result_array();
            $divisions_by_id = array_column($divisions, 'name', 'id');
            
            $this->db->select('id, name');
            $this->db->from('departements');
            $departements = $this->db->get()->result_array();
            $departements_by_id = array_column($departements, 'name', 'id');
            
            $this->db->select('id, name');
            $this->db->from('departement_subs');
            $departement_subs = $this->db->get()->result_array();
            $departement_subs_by_id = array_column($departement_subs, 'name', 'id');
            
            // Get employees in batches
            $this->db->select('id, number, name, division_id, departement_id, departement_sub_id, date_sign, status');
            $this->db->from('employees');
            $this->db->where('deleted', 0);
            
            // Apply filters
            if ($filter_status_employee !== '' && $filter_status_employee !== null) {
                $this->db->where('status', $filter_status_employee);
            }
            if (!empty($filter_employee)) {
                $this->db->where('id', $filter_employee);
            }
            
            $employees = $this->db->get()->result_array();
            
            // Filter by division, department, department_sub if specified
            $filtered_employees = [];
            foreach ($employees as $emp) {
                $include_employee = true;
                
                if (!empty($filter_division) && $emp['division_id'] != $filter_division) {
                    $include_employee = false;
                }
                if (!empty($filter_departement) && $emp['departement_id'] != $filter_departement) {
                    $include_employee = false;
                }
                if (!empty($filter_departement_sub) && $emp['departement_sub_id'] != $filter_departement_sub) {
                    $include_employee = false;
                }
                // Exclude employees with missing master data to mimic original JOIN behavior
                if (!isset($divisions_by_id[$emp['division_id']])) {
                    $include_employee = false;
                }
                if (!isset($departements_by_id[$emp['departement_id']])) {
                    $include_employee = false;
                }
                if (!isset($departement_subs_by_id[$emp['departement_sub_id']])) {
                    $include_employee = false;
                }
                
                if ($include_employee) {
                    $filtered_employees[] = $emp;
                }
            }
            
            // Get shift information for filtered employees
            $employee_ids = array_column($filtered_employees, 'id');
            $shift_joins = [
                ['table' => 'shifts c', 'condition' => 'c.id = b.shift_id', 'type' => 'left'],
                ['table' => 'shift_details d', 'condition' => 'd.shift_id = c.id', 'type' => 'left']
            ];
            $all_shifts = $this->getDataInBatches(
                'shift_employees b',
                'b.employee_id, d.start, d.end, d.days, d.working, d.tolerance, c.name as shift_name, d.name as shift_detail_name',
                $shift_joins,
                'b.employee_id',
                $employee_ids
            );
            
            // Group shifts by employee_id
            $shifts_by_employee = [];
            foreach ($all_shifts as $shift) {
                if (!isset($shifts_by_employee[$shift['employee_id']])) {
                    $shifts_by_employee[$shift['employee_id']] = [];
                }
                $shifts_by_employee[$shift['employee_id']][] = $shift;
            }
            
            // Build final employee records
            foreach ($filtered_employees as $emp) {
                $shift_name = '';
                $days = '';
                if (isset($shifts_by_employee[$emp['id']])) {
                    // Prefer main shift name from shifts table; fallback to shift_detail_name if needed
                    $shift_name = $shifts_by_employee[$emp['id']][0]['shift_name'] ?? '';
                    if (empty($shift_name)) {
                        $shift_name = $shifts_by_employee[$emp['id']][0]['shift_detail_name'] ?? '';
                    }
                    $days = $shifts_by_employee[$emp['id']][0]['days'] ?? '';
                }
                
                $employee_records[] = [
                    'employee_id' => $emp['id'],
                    'employee_number' => $emp['number'],
                    'employee_name' => $emp['name'],
                    'division_name' => $divisions_by_id[$emp['division_id']] ?? '',
                    'departement_name' => $departements_by_id[$emp['departement_id']] ?? '',
                    'departement_sub_name' => $departement_subs_by_id[$emp['departement_sub_id']] ?? '',
                    'shift_name' => $shift_name,
                    'days' => $days,
                    'date_sign' => $emp['date_sign']
                ];
            }
            
            // Sort records
            usort($employee_records, function($a, $b) {
                $dept_cmp = strcmp($a['departement_name'], $b['departement_name']);
                if ($dept_cmp !== 0) return $dept_cmp;
                
                $dept_sub_cmp = strcmp($a['departement_sub_name'], $b['departement_sub_name']);
                if ($dept_sub_cmp !== 0) return $dept_sub_cmp;
                
                return strcmp($a['employee_name'], $b['employee_name']);
            });
            
            $records = $employee_records;

            // OPTIMIZATION: Get all holidays in one query
            $this->db->select('trans_date, description');
            $this->db->from('calendars');
            $this->db->where("trans_date between '$filter_from' and '$filter_to'");
            $holidays = $this->db->get()->result_array();
            $arrHoliday = [];
            foreach ($holidays as $holiday) {
                $arrHoliday[] = ["trans_date" => $holiday['trans_date'], "description" => $holiday['description']];
            }

            // Note: Shifts data already fetched above during employee processing
            
            // Get employee IDs and numbers for batch queries
            $employee_ids = array_column($records, 'employee_id');
            $employee_numbers = array_column($records, 'employee_number');
            
            // Safety check - jika tidak ada employee, return early
            if (empty($employee_ids)) {
                fputcsv($output, array("No employees found matching the criteria"));
                fclose($output);
                return;
            }

            // OPTIMIZATION: Get all permits data in batches
            $joins = [
                ['table' => 'reasons c', 'condition' => 'a.reason_id = c.id', 'type' => 'inner'],
                ['table' => 'permit_types d', 'condition' => 'a.permit_type_id = d.id', 'type' => 'inner']
            ];
            $all_permits = $this->getDataInBatches(
                'permits a',
                'a.employee_id, a.permit_date, a.note, c.name as reason_name, d.name as permit_name',
                $joins,
                'a.employee_id',
                $employee_ids,
                "a.permit_date between '$filter_from' and '$filter_to'"
            );
            
            // Group permits by employee_id
            $permits_by_employee = [];
            foreach ($all_permits as $permit) {
                if (!isset($permits_by_employee[$permit['employee_id']])) {
                    $permits_by_employee[$permit['employee_id']] = [];
                }
                $permits_by_employee[$permit['employee_id']][] = $permit;
            }

            // OPTIMIZATION: Get all resignations data in batches
            $all_resignations = $this->getDataInBatches(
                'resignations',
                'employee_id, resign_date, remarks',
                [],
                'employee_id',
                $employee_ids,
                "resign_date <= '$filter_to'"
            );
            
            // Group resignations by employee_id
            $resignations_by_employee = [];
            foreach ($all_resignations as $resignation) {
                $resignations_by_employee[$resignation['employee_id']] = $resignation;
            }

            // OPTIMIZATION: Get all change_days data in batches
            $all_change_days = $this->getDataInBatches(
                'change_days',
                'employee_id, start, end, remarks',
                [],
                'employee_id',
                $employee_ids,
                "(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')"
            );
            
            // Group change_days by employee_id
            $change_days_by_employee = [];
            $all_change_day_start_dates = [];
            foreach ($all_change_days as $change_day) {
                if (!isset($change_days_by_employee[$change_day['employee_id']])) {
                    $change_days_by_employee[$change_day['employee_id']] = [];
                }
                $change_days_by_employee[$change_day['employee_id']][] = $change_day;
                $all_change_day_start_dates[] = $change_day['start'];
            }

            // OPTIMIZATION: Get all attendance data in batches
            $whereDate = "a.date_in between '$filter_from' and '$filter_to'";
            if (count($all_change_day_start_dates) > 0) {
                // Hapus batasan jumlah tanggal untuk memastikan semua change day start dates ter-cover
                $escapedDates = array_map(function($d) { return "'" . addslashes($d) . "'"; }, $all_change_day_start_dates);
                $whereDate .= " OR a.date_in IN (" . implode(",", $escapedDates) . ")";
            }
            
            $all_attendances = $this->getDataInBatches(
                'attandances a',
                'a.number, a.date_in, a.time_in, a.date_out, a.time_out',
                [],
                'a.number',
                $employee_numbers,
                "($whereDate)",
                'a.number ASC, a.date_in ASC'
            );
            
            // Group attendances by employee_number
            $attendances_by_employee = [];
            foreach ($all_attendances as $attendance) {
                if (!isset($attendances_by_employee[$attendance['number']])) {
                    $attendances_by_employee[$attendance['number']] = [];
                }
                $attendances_by_employee[$attendance['number']][] = $attendance;
            }
            
            // Apply shift filter to each employee's attendance data
            if (!empty($filter_shift)) {
                foreach ($attendances_by_employee as $employee_number => $attendances) {
                    $attendances_by_employee[$employee_number] = $this->filterAttendanceByShift($attendances, $filter_shift);
                }
            }

            $no = 1;
            $total_attendance_records = 0;
            foreach ($records as $data) {
                $start = strtotime($filter_from);
                $finish = strtotime($filter_to);

                // Get data for this employee from pre-fetched arrays
                $arrShift = [];
                if (isset($shifts_by_employee[$data['employee_id']])) {
                    foreach ($shifts_by_employee[$data['employee_id']] as $shift) {
                        $arrShift[] = $shift['start'];
                    }
                }

                $arrPermit = [];
                if (isset($permits_by_employee[$data['employee_id']])) {
                    foreach ($permits_by_employee[$data['employee_id']] as $permit) {
                        $arrPermit[] = ["permit_date" => $permit['permit_date'], "permit_name" => $permit['permit_name'], "note" => $permit['note']];
                    }
                }

                $resignation = isset($resignations_by_employee[$data['employee_id']]) ? $resignations_by_employee[$data['employee_id']] : null;

                $arrChangeDay = [];
                $allChangeDayStart = [];
                if (isset($change_days_by_employee[$data['employee_id']])) {
                    foreach ($change_days_by_employee[$data['employee_id']] as $change_day) {
                        $arrChangeDay[] = ["start" => $change_day['start'], "end" => $change_day['end'], "remarks" => $change_day['remarks']];
                        $allChangeDayStart[] = $change_day['start'];
                    }
                }
                
                $arrAttandance = [];
                if (isset($attendances_by_employee[$data['employee_number']])) {
                    foreach ($attendances_by_employee[$data['employee_number']] as $attendance) {
                        $arrAttandance[] = ["date_in" => $attendance['date_in'], "time_in" => $attendance['time_in'], "date_out" => $attendance['date_out'], "time_out" => $attendance['time_out']];
                    }
                }

                $arrAbsence = [];
                foreach ($arrAttandance as $arrAB) {
                    $arrAbsence[] = $arrAB['date_in'];
                }
                
                $permit = "";
                $time_in = "";
                $time_out = "";
                $status = "";
                $remarks = "";
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    // Cari apakah tanggal ini adalah change day start atau end
                    $isChangeDayStart = false;
                    $isChangeDayEnd = false;
                    $changeDayStartDate = null;
                    $changeDayEndDate = null;
                    $changeDayRemarks = '';
                    foreach ($arrChangeDay as $arrC) {
                        if ($working_date == $arrC['start']) {
                            $isChangeDayStart = true;
                            $changeDayStartDate = $arrC['start'];
                            $changeDayEndDate = $arrC['end'];
                            $changeDayRemarks = $arrC['remarks'];
                        }
                        if ($working_date == $arrC['end']) {
                            $isChangeDayEnd = true;
                            $changeDayStartDate = $arrC['start'];
                            $changeDayEndDate = $arrC['end'];
                            $changeDayRemarks = $arrC['remarks'];
                        }
                    }

                    // Cek kehadiran pada tanggal ini
                    $adaKehadiran = false;
                    foreach ($arrAttandance as $arrA) {
                        if ($working_date == $arrA['date_in']) {
                            $adaKehadiran = true;
                            $date_in = $arrA['time_in'];
                            $time_in = $arrA['time_in'];
                            $time_out = $arrA['time_out'];
                        }
                    }

                    // Cek kehadiran pada tanggal change day start (untuk end)
                    $adaKehadiranChangeDayStart = false;
                    if ($isChangeDayEnd && $changeDayStartDate) {
                        foreach ($arrAttandance as $arrA) {
                            if ($changeDayStartDate == $arrA['date_in']) {
                                $adaKehadiranChangeDayStart = true;
                            }
                        }
                    }

                    // Reset status/remarks/permit
                    $status = "";
                    $remarks = "";
                    $permit = "";
                    // Logika utama
                    if ($isChangeDayStart) {
                        if ($adaKehadiran) {
                            $status = $this->cekStatusMasuk($arrShift, $date_in);
                            $remarks = "";
                        } else {
                            $status = "CHANGE DAY START";
                            $remarks = $changeDayRemarks;
                        }
                    } else if ($isChangeDayEnd) {
                        if ($adaKehadiranChangeDayStart) {
                            // Jika hadir di START, maka END dianggap sebagai pengganti yang valid
                            $status = "CHANGE DAY END";
                            $remarks = $changeDayRemarks;
                        } else {
                            // Jika TIDAK hadir di START, maka END dianggap ABSENCE
                            $status = "ABSENCE";
                            $remarks = $changeDayRemarks;
                        }
                    } else {
                        if (@$data['days'] == "5") {
                            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                                if(!in_array($working_date, $arrAbsence)){
                                    $status = "ABSENCE";
                                    $remarks = "";
                                }
                                if (!empty($resignation) && strtotime($working_date) >= strtotime($resignation['resign_date'])) {
                                    $status = "RESIGN";
                                    $remarks = $resignation['remarks'];
                                }
                                if (strtotime($data['date_sign']) > strtotime($working_date)) {
                                    $status = "NOT JOIN YET";
                                    $remarks = "";
                                }
                                if (empty($arrShift)) {
                                    $status = "UN SETTING";
                                    $remarks = "Check Shift Employee";
                                }
                                foreach ($arrHoliday as $arrH) {
                                    if ($working_date == $arrH['trans_date']) {
                                        $status = "HOLIDAY";
                                        $remarks = $arrH['description'];
                                    }
                                }
                                foreach ($arrPermit as $arrP) {
                                    if ($working_date == $arrP['permit_date']) {
                                        $status = "PERMIT";
                                        $permit = $arrP['permit_name'];
                                        $remarks = $arrP['note'];
                                    }
                                }
                                foreach ($arrAttandance as $arrA) {
                                    if ($working_date == $arrA['date_in']) {
                                        $date_in = $arrA['time_in'];
                                        $time_in = $arrA['time_in'];
                                        $time_out = $arrA['time_out'];
                                        $status = $this->cekStatusMasuk($arrShift, $date_in);
                                        $remarks = "";
                                    }
                                }
                            } else {
                                $status = "WEEKEND";
                                $remarks = "";
                            }
                        } else {
                            if (date('w', $i) !== '0') {
                                foreach ($arrAttandance as $arrAB) {
                                    $arrAbsence[] = $arrAB['date_in'];
                                }
                                if(!in_array($working_date, $arrAbsence)){
                                    $status = "ABSENCE";
                                    $remarks = "";
                                }
                                if (!empty($resignation) && strtotime($working_date) >= strtotime($resignation['resign_date'])) {
                                    $status = "RESIGN";
                                    $remarks = $resignation['remarks'];
                                }
                                if (strtotime($data['date_sign']) > strtotime($working_date)) {
                                    $status = "NOT JOIN YET";
                                    $remarks = "";
                                }
                                if (empty($arrShift)) {
                                    $status = "UN SETTING";
                                    $remarks = "Check Shift Employee";
                                }
                                foreach ($arrHoliday as $arrH) {
                                    if ($working_date == $arrH['trans_date']) {
                                        $status = "HOLIDAY";
                                        $remarks = $arrH['description'];
                                    }
                                }
                                foreach ($arrPermit as $arrP) {
                                    if ($working_date == $arrP['permit_date']) {
                                        $status = "PERMIT";
                                        $permit = $arrP['permit_name'];
                                        $remarks = $arrP['note'];
                                    }
                                }
                                foreach ($arrAttandance as $arrA) {
                                    if ($working_date == $arrA['date_in']) {
                                        $date_in = $arrA['time_in'];
                                        $time_in = $arrA['time_in'];
                                        $time_out = $arrA['time_out'];
                                        $status = $this->cekStatusMasuk($arrShift, $date_in);
                                        $remarks = "";
                                    }
                                }
                            } else {
                                $status = "WEEKEND";
                                $remarks = "";
                            }
                        }
                    }

                    // Cek apakah hari ini sesuai dengan filter shift
                    $showThisDay = true;
                    if (!empty($filter_shift)) {
                        $showThisDay = false; // Default tidak tampilkan
                        
                        // Cek apakah ada kehadiran pada hari ini
                        if ($adaKehadiran) {
                            // Cek apakah waktu masuk sesuai dengan shift
                            if ($filter_shift == "1" && $time_in >= '05:00:00' && $time_in <= '09:00:00') {
                                $showThisDay = true;
                            } elseif ($filter_shift == "2" && $time_in >= '15:00:00' && $time_in <= '22:00:00') {
                                $showThisDay = true;
                            } elseif ($filter_shift == "3" && ($time_in >= '22:30:00' || $time_in <= '00:00:00')) {
                                $showThisDay = true;
                            }
                        } else {
                            // Jika tidak ada kehadiran, cek apakah status adalah PERMIT, ABSENCE, HOLIDAY, dll
                            // Untuk status ini, kita tetap tampilkan karena tidak ada time_in
                            if ($status == "PERMIT" || $status == "ABSENCE" || $status == "HOLIDAY" || 
                                $status == "WEEKEND" || $status == "RESIGN" || $status == "NOT JOIN YET" || 
                                $status == "UN SETTING" || $status == "CHANGE DAY START" || $status == "CHANGE DAY END") {
                                $showThisDay = true;
                            }
                        }
                    }
                    
                    // Hanya tampilkan jika sesuai dengan filter shift
                    if ($showThisDay) {
                        // Cek apakah tanggal ini setelah tanggal resign
                        $showAfterResign = true;
                        if (!empty($resignation) && strtotime($working_date) > strtotime($resignation['resign_date'])) {
                            $showAfterResign = false;
                        }
                        
                        if ($showAfterResign) {
                            // Hitung total record kehadiran (baik in, out, atau in&out dihitung 1)
                            if (!empty($time_in) || !empty($time_out)) {
                                $total_attendance_records++;
                            }
                            
                            // Get shift details with fallback to prefetched data
                            $shiftDetails = $this->getShiftDetails($data['employee_id']);
                            $shiftDetailsText = str_replace('<br>', '; ', $shiftDetails);
                            if (empty($shiftDetailsText) && isset($shifts_by_employee[$data['employee_id']])) {
                                $detailsParts = [];
                                foreach ($shifts_by_employee[$data['employee_id']] as $sd) {
                                    if (!empty($sd['start']) && !empty($sd['end'])) {
                                        $startStr = date('H:i:s', strtotime($sd['start']));
                                        $endStr = date('H:i:s', strtotime($sd['end']));
                                        $workingStr = isset($sd['working']) ? $sd['working'] : '';
                                        $detailsParts[] = trim($startStr . '-' . $endStr . (strlen($workingStr) ? ' (' . $workingStr . ')' : ''));
                                    }
                                }
                                $shiftDetailsText = implode('; ', $detailsParts);
                            }
                            
                            $data2 = array(
                                $no,
                                $data['departement_name'],
                                $data['departement_sub_name'],
                                "'".$data['employee_number'],
                                $data['employee_name'],
                                $data['shift_name'],
                                $shiftDetailsText,
                                date("d-m-Y", strtotime($data['date_sign'])),
                                !empty($resignation) ? date("d-m-Y", strtotime($resignation['resign_date'])) : '',
                                date("d F Y", strtotime(@$working_date)),
                                @$time_in . ' - ' . @$time_out,
                                $status,
                                $permit,
                                $remarks
                            );

                            fputcsv($output, $data2);
                            $no++;
                        }
                    }

                    $permit = "";
                    $time_in = "";
                    $time_out = "";
                    $status = "";
                    $remarks = "";
                }
            }
            
            // Tambahkan baris total di CSV
            fputcsv($output, array("", "", "", "", "", "", "", "", "", "", "Total Attendance Records", "", "", $total_attendance_records));
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=attandance_days_$format.xls");
        }

        if ($this->input->get()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_permit_type = $this->input->get('filter_permit_type');
            $filter_status_in = $this->input->get('filter_status_in');
            $filter_status = $this->input->get('filter_status');
            $filter_status_employee = $this->input->get('filter_status_employee');
            $filter_shift = $this->input->get('filter_shift');

            // OPTIMIZATION: Get all employees data in batches to avoid regex too large error
            $employee_records = [];
            
            // Get all divisions, departments, and department subs first
            $this->db->select('id, name');
            $this->db->from('divisions');
            $divisions = $this->db->get()->result_array();
            $divisions_by_id = array_column($divisions, 'name', 'id');
            
            $this->db->select('id, name');
            $this->db->from('departements');
            $departements = $this->db->get()->result_array();
            $departements_by_id = array_column($departements, 'name', 'id');
            
            $this->db->select('id, name');
            $this->db->from('departement_subs');
            $departement_subs = $this->db->get()->result_array();
            $departement_subs_by_id = array_column($departement_subs, 'name', 'id');
            
            // Get employees in batches
            $this->db->select('id, number, name, division_id, departement_id, departement_sub_id, date_sign, status');
            $this->db->from('employees');
            $this->db->where('deleted', 0);
            
            // Apply filters
            if ($filter_status_employee !== '' && $filter_status_employee !== null) {
                $this->db->where('status', $filter_status_employee);
            }
            if (!empty($filter_employee)) {
                $this->db->where('id', $filter_employee);
            }
            
            $employees = $this->db->get()->result_array();
            
            // Filter by division, department, department_sub if specified
            $filtered_employees = [];
            foreach ($employees as $emp) {
                $include_employee = true;
                
                if (!empty($filter_division) && $emp['division_id'] != $filter_division) {
                    $include_employee = false;
                }
                if (!empty($filter_departement) && $emp['departement_id'] != $filter_departement) {
                    $include_employee = false;
                }
                if (!empty($filter_departement_sub) && $emp['departement_sub_id'] != $filter_departement_sub) {
                    $include_employee = false;
                }
                // Exclude employees with missing master data to avoid empty names
                if (!isset($divisions_by_id[$emp['division_id']])) {
                    $include_employee = false;
                }
                if (!isset($departements_by_id[$emp['departement_id']])) {
                    $include_employee = false;
                }
                if (!isset($departement_subs_by_id[$emp['departement_sub_id']])) {
                    $include_employee = false;
                }
                
                if ($include_employee) {
                    $filtered_employees[] = $emp;
                }
            }
            
            // Get shift information for filtered employees
            $employee_ids = array_column($filtered_employees, 'id');
            $shift_joins = [
                ['table' => 'shifts c', 'condition' => 'c.id = b.shift_id', 'type' => 'left'],
                ['table' => 'shift_details d', 'condition' => 'd.shift_id = c.id', 'type' => 'left']
            ];
            $all_shifts = $this->getDataInBatches(
                'shift_employees b',
                'b.employee_id, d.start, d.end, d.days, d.working, d.tolerance, c.name as shift_name, d.name as shift_detail_name',
                $shift_joins,
                'b.employee_id',
                $employee_ids
            );
            
            // Group shifts by employee_id
            $shifts_by_employee = [];
            foreach ($all_shifts as $shift) {
                if (!isset($shifts_by_employee[$shift['employee_id']])) {
                    $shifts_by_employee[$shift['employee_id']] = [];
                }
                $shifts_by_employee[$shift['employee_id']][] = $shift;
            }
            
            // Build final employee records
            foreach ($filtered_employees as $emp) {
                $shift_name = '';
                $days = '';
                if (isset($shifts_by_employee[$emp['id']])) {
                    $shift_name = $shifts_by_employee[$emp['id']][0]['shift_name'] ?? '';
                    if (empty($shift_name)) {
                        $shift_name = $shifts_by_employee[$emp['id']][0]['shift_detail_name'] ?? '';
                    }
                    $days = $shifts_by_employee[$emp['id']][0]['days'] ?? '';
                }
                
                $employee_records[] = [
                    'employee_id' => $emp['id'],
                    'employee_number' => $emp['number'],
                    'employee_name' => $emp['name'],
                    'division_name' => $divisions_by_id[$emp['division_id']] ?? '',
                    'departement_name' => $departements_by_id[$emp['departement_id']] ?? '',
                    'departement_sub_name' => $departement_subs_by_id[$emp['departement_sub_id']] ?? '',
                    'shift_name' => $shift_name,
                    'days' => $days,
                    'date_sign' => $emp['date_sign']
                ];
            }
            
            // Sort records
            usort($employee_records, function($a, $b) {
                $dept_cmp = strcmp($a['departement_name'], $b['departement_name']);
                if ($dept_cmp !== 0) return $dept_cmp;
                
                $dept_sub_cmp = strcmp($a['departement_sub_name'], $b['departement_sub_name']);
                if ($dept_sub_cmp !== 0) return $dept_sub_cmp;
                
                return strcmp($a['employee_name'], $b['employee_name']);
            });
            
            $records = $employee_records;

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            echo '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <style> .str{ mso-number-format:\@; } </style>
            <body>
            <center>
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
                <h3 style="margin:0;">Report Attandance Days</h3>
                <span>Period ' . $filter_from . ' to ' . $filter_to . '</span>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Departement</th>
                    <th>Departement Sub</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Shift</th>
                    <th>Shift Detail</th>
                    <th>Join Date</th>
                    <th>Resign Date</th>
                    <th>Trans Date</th>
                    <th>Time In/Out</th>
                    <th>Status</th>
                    <th>Permit</th>
                    <th>Description</th>
                </tr>';
            $no = 1;
            $total_attendance_records = 0;

            $start = strtotime($filter_from);
            $finish = strtotime($filter_to);

            // OPTIMIZATION: Get all holidays in one query
            $this->db->select('trans_date, description');
            $this->db->from('calendars');
            $this->db->where("trans_date between '$filter_from' and '$filter_to'");
            $holidays = $this->db->get()->result_array();
            $arrHoliday = [];
            foreach ($holidays as $holiday) {
                $arrHoliday[] = ["trans_date" => $holiday['trans_date'], "description" => $holiday['description']];
            }

            // OPTIMIZATION: Get all employee IDs for batch queries
            $employee_ids = array_column($records, 'employee_id');
            $employee_numbers = array_column($records, 'employee_number');
            
            // Safety check - jika tidak ada employee, return early
            if (empty($employee_ids)) {
                echo '<tr><td colspan="14" style="text-align:center; color:red;">No employees found matching the criteria</td></tr>';
                echo '</table>';
                echo '</body></html>';
                return;
            }

            // OPTIMIZATION: Get all shifts data in batches
            $joins = [
                ['table' => 'shifts c', 'condition' => 'c.id = b.shift_id', 'type' => 'inner'],
                ['table' => 'shift_details d', 'condition' => 'd.shift_id = c.id', 'type' => 'inner']
            ];
            $all_shifts = $this->getDataInBatches(
                'shift_employees b',
                'b.employee_id, d.start, d.end, d.days, d.working, d.tolerance, c.name, d.name as shift_name',
                $joins,
                'b.employee_id',
                $employee_ids
            );
            
            // Group shifts by employee_id
            $shifts_by_employee = [];
            foreach ($all_shifts as $shift) {
                if (!isset($shifts_by_employee[$shift['employee_id']])) {
                    $shifts_by_employee[$shift['employee_id']] = [];
                }
                $shifts_by_employee[$shift['employee_id']][] = $shift;
            }

            // OPTIMIZATION: Get all permits data in batches
            $joins = [
                ['table' => 'reasons c', 'condition' => 'a.reason_id = c.id', 'type' => 'inner'],
                ['table' => 'permit_types d', 'condition' => 'a.permit_type_id = d.id', 'type' => 'inner']
            ];
            $all_permits = $this->getDataInBatches(
                'permits a',
                'a.employee_id, a.permit_date, a.note, c.name as reason_name, d.name as permit_name',
                $joins,
                'a.employee_id',
                $employee_ids,
                "a.permit_date between '$filter_from' and '$filter_to'"
            );
            
            // Group permits by employee_id
            $permits_by_employee = [];
            foreach ($all_permits as $permit) {
                if (!isset($permits_by_employee[$permit['employee_id']])) {
                    $permits_by_employee[$permit['employee_id']] = [];
                }
                $permits_by_employee[$permit['employee_id']][] = $permit;
            }

            // OPTIMIZATION: Get all resignations data in batches
            $all_resignations = $this->getDataInBatches(
                'resignations',
                'employee_id, resign_date, remarks',
                [],
                'employee_id',
                $employee_ids,
                "resign_date <= '$filter_to'"
            );
            
            // Group resignations by employee_id
            $resignations_by_employee = [];
            foreach ($all_resignations as $resignation) {
                $resignations_by_employee[$resignation['employee_id']] = $resignation;
            }

            // OPTIMIZATION: Get all change_days data in batches
            $all_change_days = $this->getDataInBatches(
                'change_days',
                'employee_id, start, end, remarks',
                [],
                'employee_id',
                $employee_ids,
                "(start between '$filter_from' and '$filter_to' or end between '$filter_from' and '$filter_to')"
            );
            
            // Group change_days by employee_id
            $change_days_by_employee = [];
            $all_change_day_start_dates = [];
            foreach ($all_change_days as $change_day) {
                if (!isset($change_days_by_employee[$change_day['employee_id']])) {
                    $change_days_by_employee[$change_day['employee_id']] = [];
                }
                $change_days_by_employee[$change_day['employee_id']][] = $change_day;
                $all_change_day_start_dates[] = $change_day['start'];
            }

            // OPTIMIZATION: Get all attendance data in batches
            $whereDate = "a.date_in between '$filter_from' and '$filter_to'";
            if (count($all_change_day_start_dates) > 0) {
                // Hapus batasan jumlah tanggal untuk memastikan semua change day start dates ter-cover
                $escapedDates = array_map(function($d) { return "'" . addslashes($d) . "'"; }, $all_change_day_start_dates);
                $whereDate .= " OR a.date_in IN (" . implode(",", $escapedDates) . ")";
            }
            
            $all_attendances = $this->getDataInBatches(
                'attandances a',
                'a.number, a.date_in, a.time_in, a.date_out, a.time_out',
                [],
                'a.number',
                $employee_numbers,
                "($whereDate)",
                'a.number ASC, a.date_in ASC'
            );
            
            // Note: Filter shift sudah diterapkan di level data processing, bukan di query level
            
            // Group attendances by employee_number
            $attendances_by_employee = [];
            foreach ($all_attendances as $attendance) {
                if (!isset($attendances_by_employee[$attendance['number']])) {
                    $attendances_by_employee[$attendance['number']] = [];
                }
                $attendances_by_employee[$attendance['number']][] = $attendance;
            }
            
            // Apply shift filter to each employee's attendance data
            if (!empty($filter_shift)) {
                foreach ($attendances_by_employee as $employee_number => $attendances) {
                    $attendances_by_employee[$employee_number] = $this->filterAttendanceByShift($attendances, $filter_shift);
                }
            }

            foreach ($records as $data) {
                // Get data for this employee from pre-fetched arrays
                $arrShift = [];
                if (isset($shifts_by_employee[$data['employee_id']])) {
                    foreach ($shifts_by_employee[$data['employee_id']] as $shift) {
                        $arrShift[] = $shift['start'];
                    }
                }

                $arrPermit = [];
                if (isset($permits_by_employee[$data['employee_id']])) {
                    foreach ($permits_by_employee[$data['employee_id']] as $permit) {
                        $arrPermit[] = ["permit_date" => $permit['permit_date'], "permit_name" => $permit['permit_name'], "note" => $permit['note']];
                    }
                }

                $resignation = isset($resignations_by_employee[$data['employee_id']]) ? $resignations_by_employee[$data['employee_id']] : null;

                $arrChangeDay = [];
                $allChangeDayStart = [];
                if (isset($change_days_by_employee[$data['employee_id']])) {
                    foreach ($change_days_by_employee[$data['employee_id']] as $change_day) {
                        $arrChangeDay[] = ["start" => $change_day['start'], "end" => $change_day['end'], "remarks" => $change_day['remarks']];
                        $allChangeDayStart[] = $change_day['start'];
                    }
                }
                
                $arrAttandance = [];
                if (isset($attendances_by_employee[$data['employee_number']])) {
                    foreach ($attendances_by_employee[$data['employee_number']] as $attendance) {
                        $arrAttandance[] = ["number" => $attendance['number'], "date_in" => $attendance['date_in'], "time_in" => $attendance['time_in'], "date_out" => $attendance['date_out'], "time_out" => $attendance['time_out']];
                    }
                }

                $arrAbsence = [];
                foreach ($arrAttandance as $arrAB) {
                    $arrAbsence[] = $arrAB['date_in'];
                }
                
                $permit = "";
                $time_in = "";
                $time_out = "";
                $status = "";
                $remarks = "";
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    // Cari apakah tanggal ini adalah change day start atau end
                    $isChangeDayStart = false;
                    $isChangeDayEnd = false;
                    $changeDayStartDate = null;
                    $changeDayEndDate = null;
                    $changeDayRemarks = '';
                    foreach ($arrChangeDay as $arrC) {
                        if ($working_date == $arrC['start']) {
                            $isChangeDayStart = true;
                            $changeDayStartDate = $arrC['start'];
                            $changeDayEndDate = $arrC['end'];
                            $changeDayRemarks = $arrC['remarks'];
                        }
                        if ($working_date == $arrC['end']) {
                            $isChangeDayEnd = true;
                            $changeDayStartDate = $arrC['start'];
                            $changeDayEndDate = $arrC['end'];
                            $changeDayRemarks = $arrC['remarks'];
                        }
                    }

                    // Cek kehadiran pada tanggal ini
                    $adaKehadiran = false;
                    foreach ($arrAttandance as $arrA) {
                        if ($working_date == $arrA['date_in']) {
                            $adaKehadiran = true;
                            $date_in = $arrA['time_in'];
                            $time_in = $arrA['time_in'];
                            $time_out = $arrA['time_out'];
                        }
                    }

                    // Cek kehadiran pada tanggal change day start (untuk end)
                    $adaKehadiranChangeDayStart = false;
                    if ($isChangeDayEnd && $changeDayStartDate) {
                        foreach ($arrAttandance as $arrA) {
                            if ($changeDayStartDate == $arrA['date_in']) {
                                $adaKehadiranChangeDayStart = true;
                            }
                        }
                    }

                    // Reset status/remarks/permit
                    $status = "";
                    $remarks = "";
                    $permit = "";
                    // Logika utama
                    if ($isChangeDayStart) {
                        if ($adaKehadiran) {
                            $status = $this->cekStatusMasuk($arrShift, $date_in);
                            $remarks = "";
                        } else {
                            $status = "CHANGE DAY START";
                            $remarks = $changeDayRemarks;
                        }
                    } else if ($isChangeDayEnd) {
                        if ($adaKehadiranChangeDayStart) {
                            // Jika hadir di START, maka END dianggap sebagai pengganti yang valid
                            $status = "CHANGE DAY END";
                            $remarks = $changeDayRemarks;
                        } else {
                            // Jika TIDAK hadir di START, maka END dianggap ABSENCE
                            $status = "ABSENCE";
                            $remarks = $changeDayRemarks;
                        }
                    } else {
                        if (@$data['days'] == "4") {
                            $status = "";
                            $remarks = "";
                            if (!empty($resignation) && strtotime($working_date) >= strtotime($resignation['resign_date'])) {
                                $status = "RESIGN";
                                $remarks = $resignation['remarks'];
                            } elseif (strtotime($data['date_sign']) > strtotime($working_date)) {
                                $status = "NOT JOIN YET";
                            }

                            if (empty($status)) {
                                foreach ($arrAttandance as $arrA) {
                                    if (empty($status)) {
                                        $q = "SELECT MIN(date_in) AS date FROM attandances WHERE number = '".$arrA['number']."'";
                                        $start_date = new DateTime($this->db->query($q)->row()->date);
                                        if ($working_date == $arrA['date_in']) {
                                            $date_in = $arrA['time_in'];
                                            $time_in = $arrA['time_in'];
                                            $time_out = $arrA['time_out'];
                                            $status = $this->cekStatusMasuk($arrShift, $date_in);
                                        } elseif (in_array((($start_date->diff(new DateTime(date('Y-m-d', $i)))->days + 1) % 6), [5,0])) { $status = "OFF SECURITY"; }
                                    }
                                }
                                foreach ($arrHoliday as $arrH) {
                                    if (empty($status)) {
                                        if ($working_date == $arrH['trans_date']) {
                                            $status = "HOLIDAY";
                                            $remarks = $arrH['description'];
                                        }
                                    }
                                }
                                foreach ($arrPermit as $arrP) {
                                    if (empty($status)) {
                                        if ($working_date == $arrP['permit_date']) {
                                            $status = "PERMIT";
                                            $permit = $arrP['permit_name'];
                                            $remarks = $arrP['note'];
                                        }
                                    }
                                }
                            }

                            if (empty($status)) { $status = "ABSENCE"; }
                        } elseif (@$data['days'] == "5") {
                            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                                if(!in_array($working_date, $arrAbsence)){
                                    $status = "ABSENCE";
                                    $remarks = "";
                                }
                                if (!empty($resignation) && strtotime($working_date) >= strtotime($resignation['resign_date'])) {
                                    $status = "RESIGN";
                                    $remarks = $resignation['remarks'];
                                }
                                if (strtotime($data['date_sign']) > strtotime($working_date)) {
                                    $status = "NOT JOIN YET";
                                    $remarks = "";
                                }
                                if (empty($arrShift)) {
                                    $status = "UN SETTING";
                                    $remarks = "Check Shift Employee";
                                }
                                foreach ($arrHoliday as $arrH) {
                                    if ($working_date == $arrH['trans_date']) {
                                        $status = "HOLIDAY";
                                        $remarks = $arrH['description'];
                                    }
                                }
                                foreach ($arrPermit as $arrP) {
                                    if ($working_date == $arrP['permit_date']) {
                                        $status = "PERMIT";
                                        $permit = $arrP['permit_name'];
                                        $remarks = $arrP['note'];
                                    }
                                }
                                foreach ($arrAttandance as $arrA) {
                                    if ($working_date == $arrA['date_in']) {
                                        $date_in = $arrA['time_in'];
                                        $time_in = $arrA['time_in'];
                                        $time_out = $arrA['time_out'];
                                        $status = $this->cekStatusMasuk($arrShift, $date_in);
                                        $remarks = "";
                                    }
                                }
                            } else {
                                $status = "WEEKEND";
                                $remarks = "";
                            }
                        } else {
                            if (date('w', $i) !== '0') {
                                foreach ($arrAttandance as $arrAB) {
                                    $arrAbsence[] = $arrAB['date_in'];
                                }
                                if(!in_array($working_date, $arrAbsence)){
                                    $status = "ABSENCE";
                                    $remarks = "";
                                }
                                if (!empty($resignation) && strtotime($working_date) >= strtotime($resignation['resign_date'])) {
                                    $status = "RESIGN";
                                    $remarks = $resignation['remarks'];
                                }
                                if (strtotime($data['date_sign']) > strtotime($working_date)) {
                                    $status = "NOT JOIN YET";
                                    $remarks = "";
                                }
                                if (empty($arrShift)) {
                                    $status = "UN SETTING";
                                    $remarks = "Check Shift Employee";
                                }
                                foreach ($arrHoliday as $arrH) {
                                    if ($working_date == $arrH['trans_date']) {
                                        $status = "HOLIDAY";
                                        $remarks = $arrH['description'];
                                    }
                                }
                                foreach ($arrPermit as $arrP) {
                                    if ($working_date == $arrP['permit_date']) {
                                        $status = "PERMIT";
                                        $permit = $arrP['permit_name'];
                                        $remarks = $arrP['note'];
                                    }
                                }
                                foreach ($arrAttandance as $arrA) {
                                    if ($working_date == $arrA['date_in']) {
                                        $date_in = $arrA['time_in'];
                                        $time_in = $arrA['time_in'];
                                        $time_out = $arrA['time_out'];
                                        $status = $this->cekStatusMasuk($arrShift, $date_in);
                                        $remarks = "";
                                    }
                                }
                            } else {
                                $status = "WEEKEND";
                                $remarks = "";
                            }
                        }
                    }

                    // Cek apakah hari ini sesuai dengan filter shift
                    $showThisDay = true;
                    if (!empty($filter_shift)) {
                        $showThisDay = false; // Default tidak tampilkan
                        
                        // Cek apakah ada kehadiran pada hari ini
                        if ($adaKehadiran) {
                            // Cek apakah waktu masuk sesuai dengan shift
                            if ($filter_shift == "1" && $time_in >= '05:00:00' && $time_in <= '09:00:00') {
                                $showThisDay = true;
                            } elseif ($filter_shift == "2" && $time_in >= '15:00:00' && $time_in <= '22:00:00') {
                                $showThisDay = true;
                            } elseif ($filter_shift == "3" && ($time_in >= '22:30:00' || $time_in <= '00:00:00')) {
                                $showThisDay = true;
                            }
                        } else {
                            // Jika tidak ada kehadiran, cek apakah status adalah PERMIT, ABSENCE, HOLIDAY, dll
                            // Untuk status ini, kita tetap tampilkan karena tidak ada time_in
                            if ($status == "PERMIT" || $status == "ABSENCE" || $status == "HOLIDAY" ||
                                $status == "WEEKEND" || $status == "RESIGN" || $status == "NOT JOIN YET" ||
                                $status == "UN SETTING" || $status == "CHANGE DAY START" || $status == "CHANGE DAY END" ||
                                $status == "OFF SECURITY") {
                                $showThisDay = true;
                            }
                        }
                    }
                    
                    // Hanya tampilkan jika sesuai dengan filter shift
                    if ($showThisDay) {
                        // Cek apakah tanggal ini setelah tanggal resign
                        $showAfterResign = true;
                        if (!empty($resignation) && strtotime($working_date) > strtotime($resignation['resign_date'])) {
                            $showAfterResign = false;
                        }
                        
                        if ($showAfterResign) {
                            // Hitung total record kehadiran (baik in, out, atau in&out dihitung 1)
                            if (!empty($time_in) || !empty($time_out)) {
                                $total_attendance_records++;
                            }

                            if ($status == "WEEKEND" || $status == "OFF SECURITY") {
                                $style = "style='color:red; font-weight:bold;'";
                            } elseif ($status == "PERMIT") {
                                $style = "style='color:blue; font-weight:bold;'";
                            } elseif ($status == "CHANGE DAY START" || $status == "CHANGE DAY END") {
                                $style = "style='color:gray; font-weight:bold;'";
                            } elseif ($status == "LATE") {
                                $style = "style='color:orange; font-weight:bold;'";
                            } elseif ($status == "ON TIME") {
                                $style = "style='color:green; font-weight:bold;'";
                            } elseif ($status == "NOT JOIN YET") {
                                $style = "style='color:gray; font-weight:bold;'";
                            } else {
                                $style = "style='color:red; font-weight:bold;'";
                            }

                            // Get shift details for HTML
                            $shiftDetails = $this->getShiftDetails($data['employee_id']);
                            
                            $html =     '<tr>
                                            <td>' . $no . '</td>
                                            <td>' . $data['departement_name'] . '</td>
                                            <td>' . $data['departement_sub_name'] . '</td>
                                            <td style="mso-number-format:\@;">' . $data['employee_number'] . '</td>
                                            <td>' . $data['employee_name'] . '</td>
                                            <td>' . $data['shift_name'] . '</td>
                                            <td>' . $shiftDetails . '</td>
                                            <td>' . date("d-m-Y", strtotime($data['date_sign'])) . '</td>
                                            <td>' . (!empty($resignation) ? date("d-m-Y", strtotime($resignation['resign_date'])) : '') . '</td>
                                            <td>' . date("d F Y", strtotime(@$working_date)) . '</td>
                                            <td>' . @$time_in . ' - ' . @$time_out . '</td>
                                            <td ' . $style . '>' . $status . '</td>
                                            <td ' . $style . '>' . @$permit . '</td>
                                            <td>' . $remarks . '</td>
                                        </tr>';

                            if ($status == $filter_status) {
                                echo  $html;      
                                $no++;
                            } else if (!empty($permit) && strtoupper($permit) == strtoupper($filter_permit_type)) {
                                echo  $html;      
                                $no++;
                            } else if ($filter_status_in == "INOUT" && !empty($time_in) && !empty($time_out)){
                                echo  $html;      
                                $no++;
                            } else if ($filter_status_in == "IN" && !empty($time_in) && empty($time_out)){
                                echo  $html;      
                                $no++;
                            } else if ($filter_status_in == "OUT" && empty($time_in) && !empty($time_out)){
                                echo  $html;      
                                $no++;
                            } else if($filter_status == "" && $filter_permit_type == "" && $filter_status_in == "") {
                                echo  $html;      
                                $no++;
                            }
                        }
                    }

                    $permit = "";
                    $time_in = "";
                    $time_out = "";
                    $status = "";
                    $remarks = "";
                }
            }

            // Tambahkan baris total ke dalam tabel sebelum penutup tabel
            echo '<tr style="font-weight:bold; background:#f4f4f4;"><td colspan="10" style="text-align:center;">Total Attendance Records</td><td style="text-align:left;">' . $total_attendance_records . '</td><td colspan="3"></td></tr>';
            echo '</table>';
            echo '</body></html>';
        }
    }
}
