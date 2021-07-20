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
            $result =  array();
            foreach ($report as $value) {
                $ligne = new stdClass;
                $point = new stdClass;
                $data  = [
                    'Id_Agent' => $value['Id_Agent'],
                    'Points' => $value['Point'],
                    'Voie' => $value['Voie'],
                    'ServiceVendu' => $value['ServiceVendu'],
                    'Point' => null,
                    'Ligne' => null,
                ];
                $ligne->Centrale = $ligne->Poste_RDV = $ligne->Voicelog = $point->Centrale = $point->Poste_RDV = $point->Voicelog = 0;

                if (strpos($value['ServiceVendu'], 'Centrale') !== false) {
                    $ligne->Centrale++;
                    $data['Points'] -= 1.3;
                    $point->Centrale += 1.3;
                }

                switch ($value['Voie']) {
                    case 'Poste RDV':
                        if ($data['Points'])
                            $ligne->Poste_RDV++;
                        $point->Poste_RDV = $data['Points'];
                        break;
                    case 'Poste Email':
                        if ($data['Points'])
                            $ligne->Poste_RDV++;
                        $point->Poste_RDV = $data['Points'];
                        break;
                    case 'Poste Normal':
                        if ($data['Points'])
                            $ligne->Poste_RDV++;
                        $point->Poste_RDV = $data['Points'];
                        break;
                    case 'Voicelog':
                        $ligne->Voicelog++;
                        $point->Voicelog = $data['Points'];
                        break;
                }

                $data['Points'] = $value['Point'];
                $data['Point'] = $point;
                $data['Ligne'] = $ligne;

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
