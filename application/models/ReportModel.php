<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ReportModel extends CI_Model
{

    // GET all Appels
    public function get_appel()
    {
        $this->load->database();
        $data = $this->db->query("select * from appel where id=112351 limit 1")->result_array();
        return $data;
    }

    // GET Appel by Dates
    public function get_appelByDate($date1, $date2)
    {
        $this->load->database();
        $data = $this->db->query("select * from appel where Date>='$date1' and Date<='$date2'")->result_array();
        return $data;
    }

    // GET Agent by Id
    public function get_agent($id)
    {
        // Database Connection
        $this->load->database();

        // Using inner join to get user informations by Id_User from agent
        $data = $this->db->query("
                                select users.Nom, users.Prenom
                                from agent
                                inner join users
                                on agent.Id_User = users.id
                                where agent.Id_User = {$id}
                                ")->result_array();
        return $data;
    }
}
