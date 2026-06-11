<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Employee_lists extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->helper(array("form", "url"));
        $this->load->library("form_validation");
        $this->load->library("session");
        $this->load->model("crud");
    }

    public function index()
    {
        $this->output
        ->set_content_type("application/json")
        ->set_output(json_encode([
            "status" => "ok",
            "message" => "Welcome to the API endpoint",
            "available_endpoints" => [
                "GET ".base_url()."api/employee_lists/reads/",
                "GET Find name ".base_url()."api/employee_lists/reads/name"
            ]
        ]));
    }

    public function reads($like_name="")
    {
        $this->db->distinct()->select(
            "a.number,".
            "a.name,".
            "a.nickname,".
            "c.number AS division_number,".
            "c.name AS division_name,".
            "d.number AS departement_number,".
            "d.name AS departement_name,".
            "e.number AS departement_sub_number,".
            "e.name AS departement_sub_name,".
            "(CASE WHEN a.job_type IS NULL THEN e.type ELSE a.job_type END) AS type,".
            "g.number AS position_number,".
            "g.name AS position_name,".
            "g.level,".
            "h.number AS contract_number,".
            "h.name AS contract_name,".
            "i.number AS group_number,".
            "i.name AS group_name,".
            "l.number AS source_number,".
            "l.name AS source_name,".
            "n.name AS shift_name,".
            "a.date_sign,".
            "a.date_expired,".
            "a.date_sign AS service,".
            "a.address,".
            "a.place_birth,".
            "a.birthday,".
            "a.gender,".
            "a.blood,".
            "j.name AS religion_name,".
            "k.name AS marital_name,".
            "a.national_id,".
            "a.tax_id,".
            "a.jamsostek,".
            "a.jamsostek_date,".
            "a.jkn,".
            "a.jkn_date,".
            "a.telphone,".
            "a.mobile_phone,".
            "a.emergency_no,".
            "a.email,".
            "a.driving_no,".
            "a.driving_date,".
            "a.stnk_no,".
            "a.stnk_date,".
            "a.bank_name,".
            "a.bank_no,".
            "a.image_id,".
            "a.image_profile,".
            "a.status,".
            "a.status_date"
        )->from("employees a")
            ->join("divisions c", "c.id = a.division_id")
            ->join("departements d", "d.id = a.departement_id")
            ->join("departement_subs e", "e.id = a.departement_sub_id")
            ->join("positions g", "g.id = a.position_id", "LEFT")
            ->join("contracts h", "h.id = a.contract_id", "LEFT")
            ->join("groups i", "i.id = a.group_id", "LEFT")
            ->join("religions j", "j.id = a.religion_id", "LEFT")
            ->join("maritals k", "k.id = a.marital_id", "LEFT")
            ->join("sources l", "l.id = a.source_id", "LEFT")
            ->join("resignations m", "a.id = m.employee_id", "LEFT")
            ->join("shift_employees f", "a.id = f.employee_id", "LEFT")
            ->join("shifts n", "f.shift_id = n.id", "LEFT")
        ->where(["a.status" => 0])->like(["a.name" => $like_name])->order_by("name");
        // die($this->db->get_compiled_select());
        $data = [];
        foreach ($this->db->get()->result() as $index => $row) {
            $row->service = $this->readService($row->service);
            $data[] = $row;
        }

        $this->output
        ->set_content_type("application/json")
        ->set_output(json_encode($data));
    }

    public function readService($dateSign = "")
    {
        $diff  = date_diff(date_create($dateSign), date_create());

        $txt = "";
        if ($diff->y != 0) { $txt .= $diff->y . " Years, "; }
        if ($diff->m != 0) { $txt .= $diff->m . " Month, "; }
        $txt .= $diff->d . " Days ";

        return $txt;
    }
}
