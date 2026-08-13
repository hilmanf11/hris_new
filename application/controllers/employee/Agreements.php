<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Agreements extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('crud');

        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Employee ID', 'required|min_length[1]|max_length[30]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('employee/agreements');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('agreements', ["number" => $post]);
        echo json_encode($send);
    }

    public function readEmployees()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->db->group_start()
            ->like('number', $post)
            ->or_like('name', $post)
        ->group_end()->order_by('name', 'asc')->get('employees')->result();
        echo json_encode(['rows' => $send, 'total' => count($send)]);
    }

    public function readDivisions()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->db->group_start()
            ->like('id', $post)
            ->or_like('name', $post)
        ->group_end()->order_by('name', 'asc')->get('divisions')->result();
        if (count($send) == 1) { $send[0]->selected = true; }
        echo json_encode($send);
    }

    public function readDepartments()
    {
        $get = isset($_GET['id']) ? base64_decode($_GET['id']) : '';
        if (!empty($get)) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->db->where('division_id', $get)
            ->group_start()
                ->like('id', $post)
                ->or_like('name', $post)
            ->group_end()->order_by('name', 'asc')->get('departements')->result();
            if (count($send) == 1) { $send[0]->selected = true; }
            echo json_encode($send);
        }
    }

    public function readDepartmentSubs()
    {
        $get = isset($_GET['id']) ? base64_decode($_GET['id']) : '';
        if (!empty($get)) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->db->where('departement_id', $get)
            ->group_start()
                ->like('id', $post)
                ->or_like('name', $post)
            ->group_end()->order_by('name', 'asc')->get('departement_subs')->result();
            if (count($send) == 1) { $send[0]->selected = true; }
            echo json_encode($send);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_expired = $this->input->get('filter_expired');
        $filter_status = $this->input->get('filter_status');
        $aprvDepartement = $this->checkApprovalAccess('agreements');

        $today = date("Y-m-d");
        if ($filter_expired == "0") {
            $whereExpired = $this->db->where("a.date_expired < '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "1") {
            $whereExpired = $this->db->where("a.date_expired = '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "2") {
            $tomorrow =  date("Y-m-d", strtotime("+1 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired = '$tomorrow' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "3") {
            $weeks =  date("Y-m-d", strtotime("+7 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$weeks' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "4") {
            $months =  date("Y-m-d", strtotime("+1 month", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$months' and a.date_expired != '0000-00-00'");
        } else {
            $whereExpired = "";
        }

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select(
             "a.*,"
            ."b.name as employee_name,"
            ."c.name as division_name,"
            ."d.name as departement_name,"
            ."e.name as departement_sub_name,"
            ."e.type,"
            ."f.name as position_name,"
            ."g.name as contract_name,"
            ."b.division_id,"
            ."b.departement_id,"
            ."b.departement_sub_id"
        )->from('agreements a')
            ->join('employees b', 'a.number = b.number')
            ->join('divisions c', 'b.division_id = c.id')
            ->join('departements d', 'b.departement_id = d.id')
            ->join('departement_subs e', 'b.departement_sub_id = e.id')
            ->join('positions f', 'f.id = a.position_id')
            ->join('contracts g', 'g.id = a.contract_id')
            ->join('groups h', 'h.id = a.group_id')
            ->where('a.deleted', 0)
            ->like('b.departement_id', $aprvDepartement)
            ->like('b.division_id', $filter_divisions)
            ->like('b.departement_id', $filter_departements)
            ->like('b.departement_sub_id', $filter_departement_subs)
            ->like('b.id', $filter_employees)
            ->like('a.status', $filter_status);
        $whereExpired;
        $this->db->order_by('b.name', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $approval = $this->crud->read('approvals', [], ["table_name" => "agreements"]);
                $attachment = $this->crud->upload('attachment', ['pdf', 'docx'], 'assets/document/agreement/');
                $postEmployee = [
                    "name" => $post['employee_name'],
                    "division_id" => $post['division_id'],
                    "departement_id" => $post['departement_id'],
                    "departement_sub_id" => $post['departement_sub_id'],
                    "position_id" => $post['position_id'],
                    "group_id" => $post['group_id'],
                    "contract_id" => $post['contract_id'],
                    "date_contract" => $post['date_sign'],
                    "date_expired" => $post['date_expired'],
                ];

                unset($post['employee_name']);
                unset($post['division_id']);
                unset($post['departement_id']);
                unset($post['departement_sub_id']);

                $this->crud->update('agreements', ["number" => $post['number']], ["status" => 1]);
                $send = $this->crud->create('agreements', array_merge($post, array("attachment" => $attachment)));
                if (empty($approval)) { $this->crud->update('employees', ["number" => $post['number']], $postEmployee); }
                echo $send;
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
            $approval = $this->crud->read('approvals', [], ["table_name" => "agreements"]);
            $attachment = $this->crud->upload('attachment', ['pdf', 'docx'], 'assets/document/agreement/');
            $postEmployee = [
                "name" => $post['employee_name'],
                "division_id" => $post['division_id'],
                "departement_id" => $post['departement_id'],
                "departement_sub_id" => $post['departement_sub_id'],
                "position_id" => $post['position_id'],
                "group_id" => $post['group_id'],
                "contract_id" => $post['contract_id'],
                "date_contract" => $post['date_sign'],
                "date_expired" => $post['date_expired'],
            ];

            unset($post['employee_name']);
            unset($post['division_id']);
            unset($post['departement_id']);
            unset($post['departement_sub_id']);
            // die(var_dump($postEmployee));
            $this->crud->update('agreements', ["number" => $post['number']], ["status" => 1]);
            $this->crud->delete('agreements', ["id" => $id]);
            $send = $this->crud->create('agreements', array_merge($post, array("attachment" => $attachment)));
            if (empty($approval)) { $this->crud->update('employees', ["number" => $post['number']], $postEmployee); }
            // die($this->db->last_query());
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('agreements', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=agreements_$format.xls");
        }

        //Filter Data
        $filter_divisions = $this->input->get('filter_divisions');
        $filter_departements = $this->input->get('filter_departements');
        $filter_departement_subs = $this->input->get('filter_departement_subs');
        $filter_employees = $this->input->get('filter_employees');
        $filter_expired = $this->input->get('filter_expired');
        $filter_status = $this->input->get('filter_status');
        $aprvDepartement = $this->checkApprovalAccess('agreements');

        $today = date("Y-m-d");
        if ($filter_expired == "0") {
            $whereExpired = $this->db->where("a.date_expired < '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "1") {
            $whereExpired = $this->db->where("a.date_expired = '$today' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "2") {
            $tomorrow =  date("Y-m-d", strtotime("+1 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired = '$tomorrow' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "3") {
            $weeks =  date("Y-m-d", strtotime("+7 day", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$weeks' and a.date_expired != '0000-00-00'");
        } elseif ($filter_expired == "4") {
            $months =  date("Y-m-d", strtotime("+1 month", strtotime($today)));
            $whereExpired = $this->db->where("a.date_expired between '$today' and '$months' and a.date_expired != '0000-00-00'");
        } else {
            $whereExpired = "";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

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
        $this->db->where('a.deleted', 0);
        $this->db->like('b.departement_id', $aprvDepartement);
        $whereExpired;
        $this->db->like('b.division_id', $filter_divisions);
        $this->db->like('b.departement_id', $filter_departements);
        $this->db->like('b.departement_sub_id', $filter_departement_subs);
        $this->db->like('b.id', $filter_employees);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('b.name', 'ASC');
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
                            <small>AGREEMENT EMPLOYEE</small>
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
                <th>Position</th>
                <th>Employee Type</th>
                <th>Join Date</th>
                <th>Exp Date</th>
                <th>Note</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['employee_name'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['departement_name'] . '</td>
                    <td>' . $data['departement_sub_name'] . '</td>
                    <td>' . $data['position_name'] . '</td>
                    <td>' . $data['contract_name'] . '</td>
                    <td>' . $data['date_sign'] . '</td>
                    <td>' . $data['date_expired'] . '</td>
                    <td>' . $data['note'] . '</td>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function template()
    {
        $sheet = 0;
        $excel = $this->spreadsheet_lib->newSpreadsheet();
        $spreadsheet = $excel->getActiveSheet($sheet);
        $excel->getProperties()->setCreator('Pixelphy')
            ->setLastModifiedBy('Pixelphy')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('PT ASKARA TEHNO INDONESIA');

        $style_row = [
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];
        $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
        $style_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];
        $style_row_center = array_merge($style_row, $style_center);
        $style_row_right = array_merge($style_row, $style_right);

        $style_col = array_merge($style_row, [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ]);

        // $drawing = new Drawing();
        // $drawing->setName('Logo');
        // $drawing->setDescription('Company Logo');
        // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
        // $drawing->setWidth(50);
        // $drawing->setHeight(50);
        // $drawing->setCoordinates('A1');
        // $drawing->setWorksheet($spreadsheet);

        //SHEET 1 ------------------------------------------------------------------------------------------------------------
        //Coloumn
        $columns = ['No', 'Employee ID', 'Employee Name', 'Division ID', 'Department ID', 'Department Sub ID', 'Process', 'Employee Type ID', 'Position ID', 'Group ID', 'Date Sign Contract', 'Date Expired', 'Note'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            if (in_array($key, [10,11])) {
                $msg = "";

                // if ($key == 0) { $msg = ""; }
                $msg = 'Format Date "YYYY-MM-DD"';

                $spreadsheet->getComment($alfa.$row_no)
                    ->getText()
                    ->createTextRun($msg);
            }

            $alfa++;
        }

        for ($i='A'; $i < $alfa; $i++) {
            $spreadsheet->getColumnDimension($i)->setAutoSize(true);
        }

        // Rename worksheet
        $spreadsheet->setTitle('template');

        $sheet++;
        $spreadsheet = $excel->createSheet();
        $excel->setActiveSheetIndex($sheet);
        $spreadsheet->setTitle('Data Employee');

        $columns = ['No', 'Employee ID', 'Employee Name'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            $alfa++;
        }

        $datas = $this->db->distinct()->select("number, name")->from("employees")->order_by("name")->get()->result();

        $row_no++;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $alfa = 'A';

            $spreadsheet->setCellValue($alfa.$row_no, $no);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        $sheet++;
        $spreadsheet = $excel->createSheet();
        $excel->setActiveSheetIndex($sheet);
        $spreadsheet->setTitle('Data Division');

        $columns = ['No', 'Division ID', 'Division Name', 'Department ID', 'Department Name', 'Department Sub ID', 'Department Sub Name'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            $alfa++;
        }

        $this->db->distinct()->select(
            "a.number AS div_id,".
            "a.name AS div_name,".
            "b.number AS dept_id,".
            "b.name AS dept_name,".
            "c.number AS sub_id,".
            "c.name AS sub_name"
        )->from("divisions a")
            ->join("departements b", "a.id = b.division_id")
            ->join("departement_subs c", "a.id = c.division_id AND b.id = c.departement_id")
        ->order_by("div_name, dept_name, sub_name");
        $datas = []; $merges = []; $no = 2;
        foreach ($this->db->get()->result() as $index => $row) {
            array_push($datas, $row);

            if (!isset($merges[$row->div_id])) { $merges[$row->div_id] = [$no]; }
            else { $merges[$row->div_id][1] = $no; }
            if (!isset($merges[$row->dept_id])) { $merges[$row->dept_id] = [$no]; }
            else { $merges[$row->dept_id][1] = $no; }

            $no++;
        }
        // die(json_encode($merges));

        $row_no++;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $alfa = 'A';

            $spreadsheet->setCellValue($alfa.$row_no, $no);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['div_id', 'dept_id', 'sub_id']))
                    { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);

                if (isset($merges[$value]) && count($merges[$value]) > 1) {
                    $next_alfa = $alfa;
                    $next_alfa++;
                    $spreadsheet->mergeCells($alfa.$merges[$value][0].':'.$alfa.$merges[$value][1]);
                    $spreadsheet->mergeCells($next_alfa.$merges[$value][0].':'.$next_alfa.$merges[$value][1]);
                }
            }

            $row_no++;
        }

        $sheet++;
        $spreadsheet = $excel->createSheet();
        $excel->setActiveSheetIndex($sheet);
        $spreadsheet->setTitle('Data Employee Type');

        $columns = ['No', 'Employee Type ID', 'Employee Type Name'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            $alfa++;
        }

        $datas = $this->db->distinct()->select("number, name")->from("contracts")->order_by("name")->get()->result();

        $row_no++;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $alfa = 'A';

            $spreadsheet->setCellValue($alfa.$row_no, $no);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        $sheet++;
        $spreadsheet = $excel->createSheet();
        $excel->setActiveSheetIndex($sheet);
        $spreadsheet->setTitle('Data Position');

        $columns = ['No', 'Position ID', 'Position Name'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            $alfa++;
        }

        $datas = $this->db->distinct()->select("number, name")->from("positions")->order_by("name")->get()->result();

        $row_no++;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $alfa = 'A';

            $spreadsheet->setCellValue($alfa.$row_no, $no);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        $sheet++;
        $spreadsheet = $excel->createSheet();
        $excel->setActiveSheetIndex($sheet);
        $spreadsheet->setTitle('Data Group');

        $columns = ['No', 'Group ID', 'Group Name'];

        $alfa = 'A';
        $row_no = 1;
        foreach ($columns as $key => $value) {
            $spreadsheet->setCellValue($alfa.$row_no, $value);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);

            $alfa++;
        }

        $datas = $this->db->distinct()->select("number, name")->from("groups")->order_by("name")->get()->result();

        $row_no++;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $alfa = 'A';

            $spreadsheet->setCellValue($alfa.$row_no, $no);
            $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        $excel->setActiveSheetIndex(0);
        // die(json_encode($excel->getActiveSheet()->toArray()));

        if (ob_get_length()) { ob_end_clean(); }

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template Upload  '.date('(Y-m-d)').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = IOFactory::createWriter($excel, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function uploadTemplate()
    {
        $result = ['stat' => '', 'data' => []];

        try {
            $spreadsheet = IOFactory::load($_FILES['file_upload']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet(0)->toArray(null, true, true, true);
            $head_col = $sheetData[1];

            $stat_format = false;
            $alfa = 'B';
            $columns = [
                'number' => 'Employee ID',
                'employee_name' => 'Employee Name',
                'division_id' => 'Division ID',
                'departement_id' => 'Department ID',
                'departement_sub_id' => 'Department Sub ID',
                'process' => 'Process',
                'contract_id' => 'Employee Type ID',
                'position_id' => 'Position ID',
                'group_id' => 'Group ID',
                'date_sign' => 'Date Sign Contract',
                'date_expired' => 'Date Expired',
                'note' => 'Note'
            ];
            foreach ($columns as $key => $value) {
                if (!$stat_format && $value != $head_col[$alfa])
                    { $stat_format = true; }
                $alfa++;
            }

            if ($stat_format) { $result['stat'] = 'The template format does not match!'; }
            elseif ((count($sheetData) - 1) == 0) { $result['stat'] = 'No data!'; }
            else {
                unset($sheetData[1]);
                foreach ($sheetData as $index => $cell) {
                    $alfa = 'B';
                    $arr = [];

                    foreach ($columns as $key => $value) {
                        $arr[$key] = base64_encode($cell[$alfa]);
                        $alfa++;
                    }

                    array_push($result['data'], $arr);
                }
            }
        } catch (Exception $e) { echo json_encode(['stat' => $e->getMessage()]); }

        echo json_encode($result);
    }

    public function templateProcess()
    {
        $post = $this->input->post();
        $result = ["theme" => "success", "title" => "", "message" => ""];
        // $customer = base64_decode($post["customer"]);
        $number = base64_decode($post["number"]);
        $employee_name = base64_decode($post["employee_name"]);
        $division_id = base64_decode($post["division_id"]);
        $departement_id = base64_decode($post["departement_id"]);
        $departement_sub_id = base64_decode($post["departement_sub_id"]);
        $process = base64_decode($post["process"]);
        $contract_id = base64_decode($post["contract_id"]);
        $position_id = base64_decode($post["position_id"]);
        $group_id = base64_decode($post["group_id"]);
        $date_sign = base64_decode($post["date_sign"]);
        $date_expired = base64_decode($post["date_expired"]);
        $note = base64_decode($post["note"]);

        $division = $this->crud->read("divisions", [], ["number" => $division_id]);
        $departement = $this->crud->read("departements", [], ["number" => $departement_id]);
        $departement_sub = $this->crud->read("departement_subs", [], ["number" => $departement_sub_id]);
        $contract = $this->crud->read("contracts", [], ["number" => $contract_id]);
        $position = $this->crud->read("positions", [], ["number" => $position_id]);
        $group = $this->crud->read("groups", [], ["number" => $group_id]);

        if (empty(count($this->crud->reads("employees", [], ["number" => $number]))))
            { $result["message"] = "Employee ID $number not found"; }
        if (empty($result["message"]) && empty($employee_name))
            { $result["message"] = "Employee Name cannot be empty!"; }
        if (empty($result["message"]) && !isset($division->id))
            { $result["message"] = "Division ID $division_id not found"; }
        if (empty($result["message"]) && !isset($departement->id))
            { $result["message"] = "Department ID $departement_id not found"; }
        if (empty($result["message"]) && !isset($departement_sub->id))
            { $result["message"] = "Department Sub ID $departement_sub_id not found"; }
        if (empty($result["message"]) && empty(count($this->crud->reads("departement_subs", [], [
            "division_id" => $division->id,
            "departement_id" => $departement->id
        ])))) { $result["message"] = "Department Sub ID $departement_sub_id not found"; }
        if (empty($result["message"]) && empty($process))
            { $result["message"] = "Process cannot be empty!"; }
        if (empty($result["message"]) && !isset($contract->id))
            { $result["message"] = "Employee Type ID $contract_id not found"; }
        if (empty($result["message"]) && !isset($position->id))
            { $result["message"] = "Position ID $position_id not found"; }
        if (empty($result["message"]) && !isset($group->id))
            { $result["message"] = "Group ID $group_id not found"; }
        if (empty($result["message"]) && empty($date_sign))
            { $result["message"] = "Date Sign Contract cannot be empty!"; }
        if (empty($result["message"]) && empty($date_expired))
            { $result["message"] = "Date Expired cannot be empty!"; }
        if (empty($result["message"]) && $date_expired < $date_sign)
            { $result["message"] = "Date Expired < Date Sign Contract!"; }

        if (empty($result["message"])) {
            $data = [
                "number" => $number,
                "process" => $process,
                "contract_id" => $contract->id,
                "position_id" => $position->id,
                "group_id" => $group->id,
                "date_sign" => $date_sign,
                "date_expired" => $date_expired,
                "note" => $note,
                "trans_date" => date("Y-m-d"),
                "duration" => $this->calculateDuration($date_sign, $date_expired)["text"]
            ];
            $dataEmployee = [
                "name" => $employee_name,
                "division_id" => $division->id,
                "departement_id" => $departement->id,
                "departement_sub_id" => $departement_sub->id,
                "contract_id" => $contract->id,
                "position_id" => $position->id,
                "group_id" => $group_id,
                "date_contract" => $date_sign,
                "date_expired" => $date_expired
            ];

            $send = $this->crud->update('agreements', ["number" => $number], ["status" => 1]);
            if (!empty($send)) { $send = $this->crud->create('agreements', $data); }
            if (!empty($send) && empty(count($this->crud->reads('approvals', [], ["table_name" => "agreements"]))))
                { $send = $this->crud->update('employees', ["number" => $number], $dataEmployee); }

            if (empty($send)) {
                $result = [
                    "theme" => "failed",
                    "title" => "Not Good",
                    "message" => "Data Saved unsuccessfully"
                ];
            } else { $result = json_decode($send); }
        } else { $result = array_merge($result, ["theme" => "failed", "title" => "Please Revise"]); }

        echo json_encode($result);
    }

    function calculateDuration($sign, $expired) {
        $text = '';
        $time = 0;

        $start = new DateTime($sign);
        $end   = new DateTime($expired);

        if ($start < $end) {
            // Mengambil selisih antara dua tanggal
            $interval = $start->diff($end);

            $year  = $interval->y;
            $month = $interval->m;
            $day   = $interval->d;
            
            // Total selisih dalam hitungan hari
            $time  = $interval->days; 

            $text  = "{$year} year {$month} month {$day} day";
        }

        return [
            'text' => $text,
            'time' => $time
        ];
    }
}
