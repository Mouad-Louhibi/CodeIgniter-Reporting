<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class ApiReportController extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ReportModel');
    }

    public function reporting_get()
    {
        $report = new ReportModel;
        $report = $report->get_appel();
        $this->response($report, 200);
    }

    public function reporting_post()
    {
        $report = new ReportModel;
        $date1 = $this->post('date1');
        $date2 = $this->post('date2');
        $report = $report->get_appelByDate($date1, $date2);

        if ($report != []) {
            $result = [];
            foreach ($report as $value) {
                $data  = [
                    'Id' => $value['Id'],
                    'Id_Agent' => $value['Id_Agent'],
                    'Id_Client' => $value['Id_Client'],
                    'Id_Produit' => $value['Id_Produit'],
                    'Id_commercial' => $value['Id_commercial'],
                    'Id_typeAppel' => $value['Id_typeAppel'],
                    'Quantite' => $value['Quantite'],
                    'Prix' => $value['Prix'],
                    'Point' => $value['Point'],
                ];
                array_push($result, $data);
            }
            $this->response($result, RestController::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => "APPEL DOESN'T EXIST"
            ], RestController::HTTP_NOT_FOUND);
        }
    }
}
