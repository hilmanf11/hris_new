<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

// Import PhpSpreadsheet
require_once FCPATH . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Warning_letters extends CI_Controller

{
    public $location = 'assets/document/relation/warning_letter/';

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

            $this->load->view('relation/warning_letters', ['location' => $this->location]);

        } else {

            redirect('error_access');

        }

    }



    public function readTermination(){

        $this->db->select('b.*');

        $this->db->from('warning_letters a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->where('b.deleted', 0);

        $this->db->where('b.status', 0);

        $this->db->where('a.warning_letter', "4");

        $this->db->group_by('a.employee_id');

        $records = $this->db->get()->result_array();



        die(json_encode($records));

    }



    public function readWarningLetter($id){

        $this->db->select('MAX(warning_letter) as warning_letter');

        $this->db->from('warning_letters');

        $this->db->where('employee_id', $id);

        $records = $this->db->get()->result_array();



        if(@$records[0]['warning_letter'] == "3"){

            $data[] = array(

                "id" => "4",

                "name" => "TERMINATION",

            );

        }elseif(@$records[0]['warning_letter'] == "4"){

            $data[] = array();

        }else{

            $data[] = array(

                "id" => "1",

                "name" => "1",

            );



            $data[] = array(

                "id" => "2",

                "name" => "2",

            );



            $data[] = array(

                "id" => "3",

                "name" => "3",

            );

        }



        die(json_encode($data));

    }



    //GET DATATABLES

    public function datatables()

    {

        if ($this->input->post()) {

            $filter_from = $this->input->get('filter_from');

            $filter_to = $this->input->get('filter_to');

            $filter_division = $this->input->get('filter_division');

            $filter_departement = $this->input->get('filter_departement');

            $filter_departement_sub = $this->input->get('filter_departement_sub');

            $filter_employee = $this->input->get('filter_employee');

            $filter_warning_letter = $this->input->get('filter_warning_letter');

            $filter_violation = $this->input->get('filter_violation');



            $page = $this->input->post('page');

            $rows = $this->input->post('rows');

            //Pagination 1-10

            $page   = isset($page) ? intval($page) : 1;

            $rows   = isset($rows) ? intval($rows) : 10;

            $offset = ($page - 1) * $rows;

            $result = array();

            //Select Query

            $this->db->select('a.*, 

                b.name as employee_name, 

                b.number as employee_number,

                b.status as employee_status,

                b.date_sign, 

                d.name as division_name, 

                e.name as departement_name, 

                f.name as departement_sub_name, 

                c.name as violation_name');

            $this->db->from('warning_letters a');

            $this->db->join('employees b', 'a.employee_id = b.id');

            $this->db->join('violations c', 'a.violation_id = c.id');

            $this->db->join('divisions d', 'b.division_id = d.id');

            $this->db->join('departements e', 'b.departement_id = e.id');

            $this->db->join('departement_subs f', 'b.departement_sub_id = f.id');

            $this->db->where('a.deleted', 0);

            $this->db->where('a.issue_date >=', $filter_from);

            $this->db->where('a.issue_date <=', $filter_to);

            $this->db->like('b.id', $filter_employee);

            $this->db->like('b.division_id', $filter_division);

            $this->db->like('b.departement_id', $filter_departement);

            $this->db->like('b.departement_sub_id', $filter_departement_sub);

            $this->db->like('a.warning_letter', $filter_warning_letter);

            $this->db->like('a.violation_id', $filter_violation);

            $this->db->order_by('b.name', 'ASC');

            //Total Data

            $totalRows = $this->db->count_all_results('', false);

            //Limit 1 - 10

            $this->db->limit($rows, $offset);

            //Get Data Array
            $records = $this->db->get()->result_array();

            // Add logic for 6 months calculation
            foreach ($records as &$record) {
                $issue_date = new DateTime($record['issue_date']);
                $current_date = new DateTime();
                $interval = $current_date->diff($issue_date);
                $months_diff = ($interval->y * 12) + $interval->m;
                
                // Add flag for >= 6 months
                $record['is_six_months_or_more'] = ($months_diff >= 6) ? 1 : 0;
            }

            $result['total'] = $totalRows;
            $result = @array_merge($result, ['rows' => $records]);
            echo json_encode($result);

        }

    }


    public function upload()
    {
        if (!is_dir($this->location)) { mkdir($this->location, 0777, true); }
        echo json_encode($this->crud->upload('attachment', ['pdf', 'xlsx', 'xls'], $this->location));
    }


    //CREATE DATA

    public function create()

    {

        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post   = $this->input->post();

                $warning_letters = $this->crud->reads("warning_letters", [], [
                    "DATE(NOW()) <= DATE_ADD(issue_date, INTERVAL 6 MONTH)" => NULL,
                    "employee_id" => $post['employee_id'],
                    "warning_letter" => $post['warning_letter']
                ]);
                // die($this->db->last_query());

                if(count($warning_letters) > 0){

                    show_error("Duplicate");

                }else{

                    $send = $this->crud->create('warning_letters', $post);

                    echo $send;

                }



            } else {

                show_error(validation_errors());

            }

        } else {

            show_error("Cannot Process your request");

        }

    }



    //UPDATE DATA

    public function update()

    {

        if ($this->input->post()) {

            $id   = base64_decode($this->input->get('id'));

            $post = $this->input->post();

            $send = $this->crud->update('warning_letters', ["id" => $id], $post);

            echo $send;

        } else {

            show_error("Cannot Process your request");

        }

    }



    //DELETE DATA

    public function delete()

    {

        $data = $this->input->post();

        $send = $this->crud->delete('warning_letters', $data);

        echo $send;

    }



    //PRINT & EXCEL DATA

    public function print($option = "")

    {

        if ($option == "excel") {

            $this->exportExcel();

            return;

        }

        $filter_from = $this->input->get('filter_from');

        $filter_to = $this->input->get('filter_to');

        $filter_division = $this->input->get('filter_division');

        $filter_departement = $this->input->get('filter_departement');

        $filter_departement_sub = $this->input->get('filter_departement_sub');

        $filter_employee = $this->input->get('filter_employee');

        $filter_warning_letter = $this->input->get('filter_warning_letter');

        $filter_violation = $this->input->get('filter_violation');



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as employee_name, b.number as employee_number, b.date_sign, d.name as division_name, e.name as departement_name, f.name as departement_sub_name, c.name as violation_name');

        $this->db->from('warning_letters a');

        $this->db->join('employees b', 'a.employee_id = b.id');

        $this->db->join('violations c', 'a.violation_id = c.id');

        $this->db->join('divisions d', 'b.division_id = d.id');

        $this->db->join('departements e', 'b.departement_id = e.id');

        $this->db->join('departement_subs f', 'b.departement_sub_id = f.id');

        $this->db->where('a.deleted', 0);

        $this->db->where('a.issue_date >=', $filter_from);

        $this->db->where('a.issue_date <=', $filter_to);

        $this->db->like('b.id', $filter_employee);

        $this->db->like('b.division_id', $filter_division);

        $this->db->like('b.departement_id', $filter_departement);

        $this->db->like('b.departement_sub_id', $filter_departement_sub);

        $this->db->like('a.warning_letter', $filter_warning_letter);

        $this->db->like('a.violation_id', $filter_violation);

        $this->db->order_by('b.name', 'ASC');

        $records = $this->db->get()->result_array();

        // die($this->db->last_query());

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}.pink-bg {background-color: #FFDCDC !important;}.orange-bg {background-color: #FFA500 !important;}</style><body>

        <center>

            <div style="float: left; font-size: 12px; text-align: left;">

                <table style="width: 100%;">

                    <tr>

                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                            <img src="' . $config->favicon . '" width="30">

                        </td>

                        <td style="font-size: 14px; text-align: left; margin:2px;">

                            <b>' . $config->name . '</b><br>

                            <small>DATA WARNING LETTERS</small>

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

                <th>Division</th>

                <th>Departement</th>

                <th>Departement Sub</th>

                <th>Warning Letter</th>

                <th>Issue Date</th>

                <th>Violation</th>

                <th>Remarks</th>

                <th>Attachment</th>

            </tr>';

        $no = 1;

        foreach ($records as $data) {

            if($data['warning_letter'] == "4"){

                $warning_letter = "TERMINATION";

            }else{

                $warning_letter = $data['warning_letter'];

            }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\'@\';">' . $data['employee_number'] . '</td>
                            <td>' . $data['employee_name'] . '</td>
                            <td>' . $data['division_name'] . '</td>
                            <td>' . $data['departement_name'] . '</td>
                            <td>' . $data['departement_sub_name'] . '</td>
                            <td>' . $warning_letter . '</td>
                            <td>' . $data['issue_date'] . '</td>
                            <td>' . $data['violation_name'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . ((empty($data['attachment'])) ? '' : '<a href="' . base_url($this->location) . $data['attachment']) . '">Download</a>' . '</td>
                        </tr>';

            $no++;

        }

        $html .= '</table></body></html>';

        echo $html;

    }

    
    //EXPORT EXCEL WITH PHPSPREADSHEET
    private function exportExcel()
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 300);
            if (ob_get_level()) ob_end_clean();

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_departement = $this->input->get('filter_departement');
            $filter_departement_sub = $this->input->get('filter_departement_sub');
            $filter_employee = $this->input->get('filter_employee');
            $filter_warning_letter = $this->input->get('filter_warning_letter');
            $filter_violation = $this->input->get('filter_violation');

            // Get config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();
            
            // Debug: Log config values
            log_message('info', 'Config name: ' . $config->name);
            log_message('info', 'Config favicon: ' . $config->favicon);
            log_message('info', 'Config logo: ' . $config->logo);

            // Get data
            $this->db->select('a.*, b.name as employee_name, b.number as employee_number, b.status as employee_status, b.date_sign, d.name as division_name, e.name as departement_name, f.name as departement_sub_name, c.name as violation_name');
            $this->db->from('warning_letters a');
            $this->db->join('employees b', 'a.employee_id = b.id');
            $this->db->join('violations c', 'a.violation_id = c.id');
            $this->db->join('divisions d', 'b.division_id = d.id');
            $this->db->join('departements e', 'b.departement_id = e.id');
            $this->db->join('departement_subs f', 'b.departement_sub_id = f.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.issue_date >=', $filter_from);
            $this->db->where('a.issue_date <=', $filter_to);
            $this->db->like('b.id', $filter_employee);
            $this->db->like('b.division_id', $filter_division);
            $this->db->like('b.departement_id', $filter_departement);
            $this->db->like('b.departement_sub_id', $filter_departement_sub);
            $this->db->like('a.warning_letter', $filter_warning_letter);
            $this->db->like('a.violation_id', $filter_violation);
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();

            foreach ($records as &$record) {
                $issue_date = new DateTime($record['issue_date']);
                $current_date = new DateTime();
                $interval = $current_date->diff($issue_date);
                $months_diff = ($interval->y * 12) + $interval->m;
                $record['is_six_months_or_more'] = ($months_diff >= 6) ? 1 : 0;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $rowStart = 1;

            // Add company header - same format as print (float left and right)
            // Add logo first - try to add logo if favicon or logo exists
            $logoAdded = false;
            
            // Function to add logo to Excel
            function addLogoToExcel($logoPath, $sheet) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Company Logo');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(30);
                    $drawing->setWidth(30);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    log_message('info', 'Logo successfully added to Excel at: ' . $logoPath);
                    return true;
                } catch (Exception $e) {
                    log_message('error', 'Failed to add logo to Excel: ' . $e->getMessage() . ' Path: ' . $logoPath);
                    return false;
                }
            }
            
            // Try to find and add logo
            $logoPaths = [
                // Specific logo file as requested
                FCPATH . 'assets/image/config/favicon/1670834694.png',
                FCPATH . 'assets/image/favicon/1670834694.png',
                // Fallback options
                FCPATH . 'assets/image/logo.png',
                FCPATH . 'assets/image/pixelphy-logo.png',
                FCPATH . 'assets/image/logo/default.png',
                FCPATH . 'assets/image/logo/black.png',
                FCPATH . 'assets/image/logo/metro.png',
                FCPATH . 'assets/image/logo/sunny.png',
                FCPATH . 'assets/image/logo/material.png',
                FCPATH . 'assets/image/logo/bootstrap.png',
                FCPATH . 'assets/image/logo/cupertino.png',
                FCPATH . 'assets/image/logo/gray.png',
                FCPATH . 'assets/image/logo/pepper-grinder.png',
                FCPATH . 'assets/image/logo/metro-blue.png',
                FCPATH . 'assets/image/logo/metro-gray.png',
                FCPATH . 'assets/image/logo/metro-green.png',
                FCPATH . 'assets/image/logo/metro-orange.png',
                FCPATH . 'assets/image/logo/metro-red.png',
                FCPATH . 'assets/image/logo/material-blue.png',
                FCPATH . 'assets/image/logo/material-teal.png',
                // Config favicon and logo
                FCPATH . 'assets/image/config/favicon/' . basename($config->favicon ?? ''),
                FCPATH . 'assets/image/config/logo/' . basename($config->logo ?? ''),
                // Document root paths
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/config/favicon/1670834694.png',
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/favicon/1670834694.png',
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/logo.png',
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/pixelphy-logo.png',
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/config/favicon/' . basename($config->favicon ?? ''),
                $_SERVER['DOCUMENT_ROOT'] . '/assets/image/config/logo/' . basename($config->logo ?? ''),
            ];
            
            // Try each logo path
            foreach ($logoPaths as $logoPath) {
                if (!empty($logoPath) && file_exists($logoPath) && is_readable($logoPath)) {
                    log_message('info', 'Trying logo path: ' . $logoPath);
                    if (addLogoToExcel($logoPath, $sheet)) {
                        $logoAdded = true;
                        break;
                    }
                }
            }
            
            // If no logo found, try to find any logo file in common directories
            if (!$logoAdded) {
                log_message('info', 'No specific logo found, searching for any available logo');
                $commonDirs = [
                    FCPATH . 'assets/image/config/favicon/',
                    FCPATH . 'assets/image/config/logo/',
                    FCPATH . 'assets/image/logo/',
                    FCPATH . 'assets/image/header/',
                    FCPATH . 'assets/image/',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/image/config/favicon/',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/image/config/logo/',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/image/logo/',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/image/header/',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/image/'
                ];
                
                foreach ($commonDirs as $dir) {
                    if (is_dir($dir)) {
                        $files = glob($dir . '*.{png,jpg,jpeg,gif}', GLOB_BRACE);
                        if (!empty($files)) {
                            $logoPath = $files[0]; // Use first found logo
                            log_message('info', 'Using fallback logo: ' . $logoPath);
                            if (addLogoToExcel($logoPath, $sheet)) {
                                $logoAdded = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            // Company name and title on the left (like float left in print)
            $sheet->setCellValue('B1', $config->name);
            $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(10.5);
            $sheet->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
            $sheet->setCellValue('B2', 'DATA WARNING LETTERS');
            $sheet->getStyle('B2')->getFont()->setBold(false)->setSize(7.5);
            $sheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
            // Print info on the right (like float right in print) - size 9, merge right di table terkanan
            $sheet->setCellValue('K3', 'Print Date ' . date("d M Y H:m:s"));
            $sheet->getStyle('K3')->getFont()->setSize(9);
            $sheet->getStyle('K3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            
            $sheet->setCellValue('K4', 'Print By ' . $this->session->username);
            $sheet->getStyle('K4')->getFont()->setSize(9);
            $sheet->getStyle('K4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            
            // Add empty row
            $sheet->mergeCells('A5:K5');
            $sheet->setCellValue('A5', '');
            
            // Set headers
            $headers = [
                'No',
                'Employee ID',
                'Employee Name',
                'Division',
                'Departement',
                'Departement Sub',
                'Warning Letter',
                'Issue Date',
                'Violation',
                'Remarks',
                'Attachment'
            ];
            $rowStart = 6; // Start from row 6 after header
            
            foreach ($headers as $colIndex => $header) {
                $column = chr(65 + $colIndex);
                $sheet->setCellValue($column . $rowStart, $header);
                $sheet->getStyle($column . $rowStart)->getFont()->setBold(true);
                $sheet->getStyle($column . $rowStart)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
                $sheet->getStyle($column . $rowStart)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            // Write data
            $row = $rowStart + 1; // Start from row 7 (after headers)
            foreach ($records as $index => $data) {
                $no = $index + 1;
                $warning_letter = ($data['warning_letter'] == "4") ? "TERMINATION" : $data['warning_letter'];
                $sheet->setCellValue('A' . $row, $no);
                // Employee ID as text
                $sheet->setCellValueExplicit('B' . $row, $data['employee_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $data['employee_name']);
                $sheet->setCellValue('D' . $row, $data['division_name']);
                $sheet->setCellValue('E' . $row, $data['departement_name']);
                $sheet->setCellValue('F' . $row, $data['departement_sub_name']);
                $sheet->setCellValue('G' . $row, $warning_letter);
                $sheet->setCellValue('H' . $row, $data['issue_date']);
                $sheet->setCellValue('I' . $row, $data['violation_name']);
                $sheet->setCellValue('J' . $row, $data['remarks']);
                $sheet->setCellValue('K' . $row, (!empty($data['attachment'])) ? 'Download' : '');
                if (!empty($data['attachment'])) { $sheet->getCell('K' . $row)->getHyperlink()->setUrl($this->location.$data['attachment']); }
                $backgroundColor = null;
                if ($data['employee_status'] == 1) {
                    $backgroundColor = 'FFDCDC';
                } elseif ($data['is_six_months_or_more'] == 1) {
                    $backgroundColor = 'FFA500';
                }
                if ($backgroundColor) {
                    $sheet->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($backgroundColor);
                }
                // Add borders to data row
                $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                if (!empty($data['attachment']))
                    { $sheet->getStyle('K' . $row)->applyFromArray(['font' => ['color' => ['rgb' => '0000FF'], 'underline' => true]]); }

                $row++;
            }

            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $format = date("Ymd");
            $filename = "warning_letters_{$format}.xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Excel export error: ' . $e->getMessage());
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error generating Excel file: ' . $e->getMessage()
            ]);
            exit;
        }
    }

}

