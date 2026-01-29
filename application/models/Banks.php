<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Banks extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->default = $this->load->database('default', TRUE);
        $this->load->library('uuid');
    }

    function index()
    {
        show_404();
    }

    public function banks($act = '')
    {
        if (empty($act)) {
            $data = [
                ['id' => '20251222000001', 'name' => 'Bank Mandiri'],
                ['id' => '20251222000002', 'name' => 'Bank Central Asia'],
                ['id' => '20251222000003', 'name' => 'Bank Syariah Indonesia']
            ];
        } else {
            $data = [
                '20251222000001' => 'Bank Mandiri',
                '20251222000002' => 'Bank Central Asia',
                '20251222000003' => 'Bank Syariah Indonesia'
            ];
        }

        return $data;
    }  
}