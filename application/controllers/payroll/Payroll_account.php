<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Payroll_account extends CI_Controller
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
        $this->load->model('banks');
    }

    public function index()
    {
        if (empty($this->session->username)) { redirect('error_session'); }
        elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['banks'] = $this->banks->banks();

            $this->load->view('template/header', $data);
            $this->load->view('payroll/payroll_account');
        } else { redirect('error_access'); }
    }

    public function datatables($act = '')
    {
        $data_bank = [];
        foreach ($this->banks->banks() as $key => $value) { $data_bank[$value['id']] = $value['name']; }

        $this->db->select(
            "TRIM(a.id) AS id, ".
            "TRIM(a.bank_id) AS bank_id, ".
            "TRIM(a.bank_id) AS bank, ".
            "TRIM(a.account_no) AS account, ".
            "COALESCE(TRIM(b.name), '') AS c_by, ".
            "DATE_FORMAT(a.created_date, '%Y-%m-%d') AS c_date, ".
            "COALESCE(TRIM(c.name), '') AS up_by, ".
            "DATE_FORMAT(a.updated_date, '%Y-%m-%d') AS up_date"
        )->from("payroll_account a")
            ->join("users b", "a.created_by = b.username", "LEFT")
            ->join("users c", "a.updated_by = c.username", "LEFT")
        ->order_by("bank, account");
        // die($this->db->get_compiled_select());
        $datas = [];
        foreach ($this->db->get()->result() as $index => $row) {
            $row->bank = $data_bank[$row->bank];
            array_push($datas, $row);
        }

        if ($act == 'print') {
            $this->db->select("*")->from("config");
            $config = $this->db->get()->row();
            $logo = base_url('assets/image/config/' . $config->logo);

            $this->db->select("TRIM(name) AS name")->from("users")->where("username", $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $print_date = date("d M Y H:m:s");

            $html = <<<HTML
                <html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#datatables {border-collapse: collapse;width: 100%;font-size: 12px;}#datatables td, #datatables th {border: 1px solid #ddd;padding: 2px;}#datatables tr:nth-child(even){background-color: #f2f2f2;}#datatables tr:hover {background-color: #ddd;}#datatables th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="$logo" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>{$config->name}</b><br>
                                    <small></small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date $print_date<br>
                        Print By $fullname
                    </div>
                </center>
                <br><br><br>
                
                <table id="datatables" border="1">
                    <tr>
                        <th rowspan="2" width="20" style="text-align: center;">No</th>
                        <th rowspan="2" style="text-align: center;">Bank Name</th>
                        <th rowspan="2" style="text-align: center;">Account No</th>
                        <th colspan="2" style="text-align: center;">Created</th>
                        <th colspan="2" style="text-align: center;">Updated</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;">By</th>
                        <th style="text-align: center;">Date</th>
                        <th style="text-align: center;">By</th>
                        <th style="text-align: center;">Date</th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $html .= <<<HTML
                    <tr>
                        <td align="center">$no</td>
                        <td>{$row->bank}</td>
                        <td>{$row->account}</td>
                        <td align="center">{$row->c_by}</td>
                        <td align="center">{$row->c_date}</td>
                        <td align="center">{$row->up_by}</td>
                        <td align="center">{$row->up_date}</td>
                    </tr>
                HTML;
            }

            echo $html.'</table></body></html>';
        } else { echo json_encode(array('total' => count($datas), 'rows' => $datas)); }
    }

    public function excel()
    {
        if ($this->session->username != "") {
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
            $style_row_center = array_merge($style_row, ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']]);
            $style_row_right = array_merge($style_row, ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']]);

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
            $columns = [
                ['No', 'Bank Name', 'Account No', 'Created', '', 'Updated', ''],
                ['', '', '', 'By', 'Date', 'By', 'Date']
            ];
            $last_index = count($columns[0]) - 1;
            $title = 'Payroll Account';

            $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $row_no = 6;
            $bf_alfa = '';
            foreach ($columns as $index => $row) {
                $alfa = 'A';
                $bf_alfa = $alfa;
                foreach ($row as $key => $value) {
                    if ($index == 0 && $key == $last_index) {
                        $spreadsheet->setCellValue('A1', $title);
                        $spreadsheet->mergeCells('A1:'.$alfa.'1');
                        $spreadsheet->getStyle('A1')->applyFromArray($style_row_center);
                        $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                        $spreadsheet->setCellValue($bf_alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                        $spreadsheet->setCellValue($bf_alfa.'4', 'Print By : ' . $fullname);
                        $spreadsheet->mergeCells($bf_alfa.'3:'.$alfa.'3');
                        $spreadsheet->mergeCells($bf_alfa.'4:'.$alfa.'4');
                    }

                    $cell = $alfa.$row_no;
                    $spreadsheet->setCellValue($alfa.$row_no, $value);
                    if ($value == '') {
                        $merge_cell = ($index == 0) ? $bf_alfa.$row_no : $alfa.($row_no - 1);
                        $cell = $merge_cell.':'.$alfa.$row_no;
                        $spreadsheet->mergeCells($cell);
                    }
                    $spreadsheet->getStyle($cell)->applyFromArray($style_col);

                    $bf_alfa = $alfa;
                    $alfa++;
                }
                $row_no++;
            }

            $this->db->select(
                "TRIM(a.bank_id) AS bank, ".
                "TRIM(a.account_no) AS account, ".
                "TRIM(b.name) AS c_by, ".
                "DATE_FORMAT(a.created_date, '%Y-%m-%d') AS c_date, ".
                "TRIM(c.name) AS up_by, ".
                "DATE_FORMAT(a.updated_date, '%Y-%m-%d') AS up_date"
            )->from("payroll_account a")
                ->join("users b", "a.created_by = b.username")
                ->join("users c", "a.updated_by = c.username")
            ->order_by("bank, account");
            // die($this->db->get_compiled_select());
            $datas = $this->db->get()->result();

            $data_bank = [];
            foreach ($this->banks->banks('find') as $key => $value) { $data_bank[$value['id']] = $value['name']; }
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';
                $row->bank = $data_bank[$row->bank];

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if (in_array($key, ['c_by','c_date','up_by','up_date'])) { $style = $style_row_center; }
                    if (in_array($key, [''])) { $style = $style_row_right; }

                    // if (in_array($key, ['']))
                    //     { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    // else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING);
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) { ob_end_clean(); }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.date(' (Y-m-d)').'.xlsx"');
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
    }

    public function create()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $send_data = [
            'bank_id' => base64_decode($post['bank']),
            'account_no' => base64_decode($post['account'])
        ];

        $stat = $this->db->where($send_data)->get('payroll_account')->num_rows() > 0;

        if ($stat) { $result['stat'] = 'Duplicate account!'; }
        else {
            $this->db->trans_start();
            $this->crud->create('payroll_account', $send_data);
            $stat = $this->db->where($send_data)->get('payroll_account')->num_rows() > 0;

            if (!$stat) {
                $result['stat'] = 'Failed save!';
                // $result['stat'] = $this->db->error()['message'];
                // $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function update()
    {
        $post = $this->input->post();
        $id = base64_decode($post['id']);
        $result = ['stat' => ''];
        $send_data = [
            'bank_id' => base64_decode($post['bank']),
            'account_no' => base64_decode($post['account'])
        ];
        $where = ['id' => $id];

        $stat = $this->db->where(array_merge($send_data, ['id !=' => $id]))->get('payroll_account')->num_rows() > 0;
        if ($stat) { $result['stat'] = 'Duplicate account!'; }
        else {
            $this->db->trans_start();
            $this->crud->update('payroll_account', $where, $send_data);
            $stat = $this->db->where($where)->get('payroll_account')->num_rows() > 0;

            if (!$stat) {
                $result['stat'] = 'Failed save!';
                $result['stat'] = $this->db->error()['message'];
                // $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function delete()
    {
        $result = ['stat' => ''];

        $post = $this->input->post();
        $where = ['id' => base64_decode($post['id'])];

        $this->db->trans_start();
        $this->crud->delete('payroll_account', $where);
        if ($this->db->where($where)->get('payroll_account')->num_rows() != 0) {
            $result['stat'] = $this->db->error()['message'];
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }
}
