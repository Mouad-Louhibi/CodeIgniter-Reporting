<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ReportModel extends CI_Model
{

    public function get_appel()
    {
        $this->load->database();
        $data = $this->db->query("select * from appel where id=112351 limit 1")->result_array();
        return $data;
    }

    public function get_appelByDate($date1, $date2)
    {
        $this->load->database();
        $data = $this->db->query("select * from appel where Date>='$date1' and Date<='$date2'")->result_array();
        return $data;
    }
}
