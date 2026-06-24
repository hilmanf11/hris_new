<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Print_contract extends CI_Controller
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
    }

    public function index()
    {
        if ($this->session->username != "") {
            $id = base64_decode($this->input->get()['id']);
            $ids = [
                '20221119000001' => '1', 
                '20221119000002' => '2', 
                '20230112000001' => '3', 
                '20230124000001' => '4', 
                '20240104000001' => '5'
            ];
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $months = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];
            $fields = [];

            $spreadsheet = IOFactory::load(FCPATH.'template/contract_example.xls');
            $spreadsheet->setActiveSheetIndex(1);
            $sheet = $spreadsheet->getActiveSheet();
            $kolomB = $sheet->rangeToArray('B1:B'.$sheet->getHighestRow());
            unset($kolomB[0]);
            foreach ($kolomB as $key => $value) { array_push($fields, $value[0]); }

            $this->db->distinct()->select(
                 "a.date_sign AS date,"
                ."COALESCE(TRIM(b.contract_id),'') AS contract,"
                ."TRIM(a.pwkt_no) AS pwkt,"
                ."COALESCE(TRIM(b.name), '') AS fullname,"
                ."TRIM(a.number) AS nik,"
                ."COALESCE(TRIM(b.national_id), '') AS ktp,"
                ."COALESCE(TRIM(b.place_birth), '') AS birth,"
                ."COALESCE(DATE_FORMAT(b.birthday, '%d %M %Y'), '') AS birth_date,"
                ."COALESCE(TRIM(b.gender), '') AS gender,"
                ."COALESCE(TRIM(b.address), '') AS address,"
                ."TRIM(a.duration) AS duration,"
                ."DATE_FORMAT(a.date_sign, '%d %M %Y') AS sign,"
                ."DATE_FORMAT(a.date_expired, '%d %M %Y') AS expired,"
                ."COALESCE(TRIM(c.name), '') AS dept,"
                ."COALESCE(TRIM(d.name), '') AS sub_dept,"
                ."TRIM(a.process) AS process,"
                ."COALESCE(TRIM(e.name), '') AS position,"
                ."COALESCE(f.amount, 0) AS salary,"
                ."COALESCE(g.amount, 0) AS tunj,"
                ."COALESCE(h.amount, 0) AS tunj_else,"
                ."COALESCE(TRIM(i.attachment), '') AS template"
            )->from("agreements a")
                ->join("employees b", "a.number = b.number", "LEFT")
                ->join("departements c", "b.departement_id = c.id", "LEFT")
                ->join("departement_subs d", "b.departement_sub_id = d.id", "LEFT")
                ->join("positions e", "a.position_id = e.id", "LEFT")
                ->join("setup_salaries f", "b.id = f.employee_id", "LEFT")
                ->join("(SELECT DISTINCT TRIM(employee_id) AS emp, amount FROM setup_allowances WHERE allowance_id = '20230221000001') g", "b.id = g.emp", "LEFT")
                ->join("(SELECT DISTINCT TRIM(employee_id) AS emp, SUM(amount) AS amount FROM setup_allowances WHERE allowance_id != '20230221000001' GROUP BY employee_id) h", "b.id = h.emp", "LEFT")
                ->join("master_contract i", "a.position_id = i.position_id AND FIND_IN_SET(a.contract_id, i.contracts)", "LEFT")
            ->where("a.id", $id);
            // die($this->db->get_compiled_select());
            $get = $this->db->get()->row();
            $contract_no = $ids[$get->contract];
            $roman = $this->convertNumber($contract_no, 'roman');
            $create_date = strtotime($get->date);

            preg_match('/(\d+)\s*tahun/', $get->duration, $matchTahun);
            preg_match('/(\d+)\s*bulan/', $get->duration, $matchBulan);

            $tahun = isset($matchTahun[1]) ? (int)$matchTahun[1] : 0;
            $bulan = isset($matchBulan[1]) ? (int)$matchBulan[1] : 0;
            $duration_stat = $tahun == 0;
            $duration_num = ($duration_stat) ? $bulan : $tahun;

            $title = "DRAFT KONTRAK STAFF $roman KI";

            $filePath = FCPATH."assets/document/master_contract/".$get->template;
            if (file_exists($filePath)) {
                $spreadsheet = IOFactory::load($filePath);
                $spreadsheet->setActiveSheetIndex(0);
                $sheet = $spreadsheet->getActiveSheet();

                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // termasuk cell kosong

                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        if ($value !== null) {
                            foreach ($fields as $key => $cari) {
                                if (stripos($value, $cari) !== false) {
                                    if ($cari == '<contract:no>') { $value = str_replace($cari, $roman, $value); }
                                    if ($cari == '<agreement:pwkt>') { $value = str_replace($cari, $get->pwkt, $value); }
                                    if ($cari == '<agreement:day>') { $value = str_replace($cari, $days[date('l', $create_date)], $value); }
                                    if ($cari == '<agreement:date>') { $value = str_replace($cari, date('d', $create_date), $value); }
                                    if ($cari == '<agreement:date_name>') { $value = str_replace($cari, $this->convertNumber(date('d', $create_date)), $value); }
                                    if ($cari == '<agreement:month>') { $value = str_replace($cari, date('n', $create_date), $value); }
                                    if ($cari == '<agreement:month_name>') { $value = str_replace($cari, $months[date('n', $create_date)], $value); }
                                    if ($cari == '<agreement:year>') { $value = str_replace($cari, date('Y', $create_date), $value); }
                                    if ($cari == '<agreement:year_name>') { $value = str_replace($cari, $this->convertNumber(date('Y', $create_date)), $value); }
                                    if ($cari == '<agreement:sign>') { $value = str_replace($cari, $get->sign, $value); }
                                    if ($cari == '<agreement:expired>') { $value = str_replace($cari, $get->expired, $value); }
                                    if ($cari == '<agreement:process>') { $value = str_replace($cari, ucwords($get->process), $value); }
                                    if ($cari == '<duration:number>') { $value = str_replace($cari, $duration_num, $value); }
                                    if ($cari == '<duration:number_name>') { $value = str_replace($cari, $this->convertNumber($duration_num), $value); }
                                    if ($cari == '<duration:name>') { $value = str_replace($cari, ($duration_stat) ? 'Bulan' : 'Tahun', $value); }
                                    if ($cari == '<employee:departement>') { $value = str_replace($cari, ucwords($get->dept), $value); }
                                    if ($cari == '<employee:sub_departement>') { $value = str_replace($cari, ucwords($get->sub_dept), $value); }
                                    if ($cari == '<employee:position>') { $value = str_replace($cari, ucwords($get->position), $value); }
                                    if ($cari == '<employee:name>') { $value = str_replace($cari, $get->fullname, $value); }
                                    if ($cari == '<employee:nik>') { $value = str_replace($cari, $get->nik, $value); }
                                    if ($cari == '<employee:national>') { $value = str_replace($cari, $get->ktp, $value); }
                                    if ($cari == '<employee:birth_place>') { $value = str_replace($cari, $get->birth, $value); }
                                    if ($cari == '<employee:birth_date>') { $value = str_replace($cari, $get->birth_date, $value); }
                                    if ($cari == '<employee:gender>') { $value = str_replace($cari, ($get->gender == 'MALE') ? 'Laki-Laki' : 'Perempuan', $value); }
                                    if ($cari == '<employee:address>') { $value = str_replace($cari, $get->address, $value); }
                                    if ($cari == '<salaries:amount>') { $value = str_replace($cari, number_format(intval($get->salary), 0, ',', '.'), $value); }
                                    if ($cari == '<allowance:amount>') { $value = str_replace($cari, number_format(intval($get->tunj), 0, ',', '.'), $value); }
                                    if ($cari == '<allowance:amount_else>')
                                        { $value = str_replace($cari, number_format(intval($get->tunj_else), 0, ',', '.'), $value); }
                                    if ($cari == '<sum:salaries>') { $value = str_replace($cari, number_format(intval($get->salary) + intval($get->tunj) + intval($get->tunj_else), 0, ',', '.'), $value); }

                                    $sheet->setCellValueExplicit($cell->getCoordinate(), $value, DataType::TYPE_STRING);
                                    if ($cari == '<contract:no>') { $title = $value; }
                                }
                            }
                        }
                    }
                }

                $writer = IOFactory::createWriter($spreadsheet, 'Html');
                ob_start();
                $writer->save('php://output');
                $html_excel = ob_get_clean();

                echo <<<HTML
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <title>$title</title>
                        <style>
                            .excel-container {
                                width: 210mm; /* setara ukuran A4 */
                                margin: 0 auto;
                                overflow-x: auto;
                            }
                            table {
                                width: 100% !important;
                                table-layout: fixed;
                            }
                            td, th {
                                word-wrap: break-word;
                            }
                        </style>
                    </head>
                    <body>
                        <div>
                            $html_excel
                        </div>
                        <script>window.onload = () => window.print();</script>
                    </body>
                    </html>
                HTML;
            }
        } else {
            redirect('login');
        }
    }

    public function convertNumber($num, $format = 'indo')
    {
        $returnValue = '';
        $num = intval($num);

        if ($format == 'roman') {
            $map = ['M' => 1000,'CM' => 900,'D' => 500,'CD' => 400,'C' => 100,'XC' => 90,'L' => 50,'XL' => 40,'X' => 10,'IX' => 9,'V' => 5,'IV' => 4,'I' => 1];

            while ($num > 0) {
                foreach ($map as $roman => $int) {
                    if ($num >= $int) {
                        $num -= $int;
                        $returnValue .= $roman;
                        break;
                    }
                }
            }
        } elseif ($format == 'indo') {
            $angka = abs($num);
            $baca = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

            $hasil = "";
            if ($angka < 12) {
                $hasil = " " . $baca[$angka];
            } else if ($angka < 20) {
                $hasil = $this->convertNumber($angka - 10) . " belas";
            } else if ($angka < 100) {
                $hasil = $this->convertNumber(intval($angka / 10)) . " puluh " . $this->convertNumber($angka % 10);
            } else if ($angka < 200) {
                $hasil = " seratus " . $this->convertNumber($angka - 100);
            } else if ($angka < 1000) {
                $hasil = $this->convertNumber(intval($angka / 100)) . " ratus " . $this->convertNumber($angka % 100);
            } else if ($angka < 2000) {
                $hasil = " seribu " . $this->convertNumber($angka - 1000);
            } else if ($angka < 1000000) {
                $hasil = $this->convertNumber(intval($angka / 1000)) . " ribu " . $this->convertNumber($angka % 1000);
            } else if ($angka < 1000000000) {
                $hasil = $this->convertNumber(intval($angka / 1000000)) . " juta " . $this->convertNumber($angka % 1000000);
            } else if ($angka < 1000000000000) {
                $hasil = $this->convertNumber(intval($angka / 1000000000)) . " miliar " . $this->convertNumber($angka % 1000000000);
            } else if ($angka < 1000000000000000) {
                $hasil = $this->convertNumber(intval($angka / 1000000000000)) . " triliun " . $this->convertNumber($angka % 1000000000000);
            }

            $returnValue = ucwords(trim($hasil));
        }

        return $returnValue;
    }
}
?>