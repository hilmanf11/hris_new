<?php
defined('BASEPATH') or exit('No direct script access allowed');

require(APPPATH . 'libraries/excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Spreadsheet_lib {
    public function newSpreadsheet() {
        return new Spreadsheet();
    }

    public function load($filePath) {
        return IOFactory::load($filePath);
    }
}
