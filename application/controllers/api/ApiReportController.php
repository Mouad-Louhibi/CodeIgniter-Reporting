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

    // GET All From Appel Table
    public function reporting_get()
    {
        $report = new ReportModel;
        $report = $report->get_appel();
        $this->response($report, 200);
    }

    // POST Dates and GET Report
    public function reporting_post()
    {
        // ReportModel Instance
        $report = new ReportModel;
        // This function get the dates values from the POST Method then set them in date1 & date2
        $date1 = $this->post('date1');
        $date2 = $this->post('date2');
        // Return all Appels between date1 & date2
        $report = $report->get_appelByDate($date1, $date2);

        // Checking if the report Array is empty or not
        if ($report != []) {
            // Declaration of a new Array
            $result = array();
            // The idea here is to create a shorthand to the first Array ($report) with the important elements
            foreach ($report as $value) {
                // We use this loop for making a new structre of ($report)
                // Declaration of Ojects ($ligne) & ($point) using stdClass
                $ligne = new stdClass;
                $point = new stdClass;
                // Declaration of Array ($data) with 'Id_Agent' & 'Points' & 'Point' & 'Ligne' as elements
                $data  = [
                    // Declaration & Initialization of elements
                    'Id_Agent' => $value['Id_Agent'],
                    'Points' => $value['Point'],
                    'Point' => null,
                    'Ligne' => null,
                ];

                // Set properties Centrale & Poste_RDV & Voicelog into Point & Ligne objects then we initialize them
                $ligne->Centrale = $ligne->Poste_RDV = $ligne->Voicelog = $point->Centrale = $point->Poste_RDV = $point->Voicelog = 0;

                // We use strpos function for checking the existance of the word 'Centrale' in $value['ServiceVendu'] that contain a string 
                if (strpos($value['ServiceVendu'], 'Centrale') !== false) {
                    // If 'Centrale' exist, we increment Centrale values
                    $ligne->Centrale++;
                    // Then we have to remove the Centrale points from $data['Points'] and add them to $point->Centrale
                    $data['Points'] -= 1.3;
                    $point->Centrale += 1.3;
                }

                // The Voie elemets could be contain multiple word, and many word can be used for the same property
                // For exemple if the value of $value['Voie'] is 'Poste RDV' or 'Poste Email' or 'Poste Normal' then $point->Poste_RDV must get the same points
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
                        // In this case we incremante the Voicelog points
                        $ligne->Voicelog++;
                        $point->Voicelog = $data['Points'];
                        break;
                }

                // Reinitialization of Points
                $data['Points'] = $value['Point'];
                // Here we link each of point & ligne with $data['Point'] & $data['Ligne']
                $data['Point'] = $point;
                $data['Ligne'] = $ligne;

                // After to create a new Array we push it in the old one
                array_push($result, $data);
            }

            // In the Array $result we can find a duplicated Agent
            // We declare this Array $final
            $final = array();
            $exist = false;

            // By this loop we can remove duplicated Agent
            foreach ($result as $result_val) {

                // Remove Points from $result, we will never need it, for not to display with the results neither
                unset($result_val['Points']);

                // Create new instance of ReportModel
                $agent = new ReportModel;
                // Call the get_agent() function that get the Id_Agent as parametre & return Agent informatios
                $agent = $agent->get_agent($result_val['Id_Agent']);
                // Concatainate tow strings to get the Full name
                $result_val['Full_Name'] = $agent[0]['Prenom'] . ' ' . $agent[0]['Nom'];

                foreach ($final as $final_val) {

                    // Here we move all of Agent values to one object that must be exist
                    if ($result_val['Id_Agent'] === $final_val['Id_Agent']) {

                        $final_val['Point']->Centrale += $result_val['Point']->Centrale;
                        $final_val['Point']->Voicelog += $result_val['Point']->Voicelog;
                        $final_val['Point']->Poste_RDV += $result_val['Point']->Poste_RDV;
                        // Total Points
                        $final_val['Point']->Total = $final_val['Point']->Centrale + $final_val['Point']->Voicelog + $final_val['Point']->Poste_RDV;

                        $final_val['Ligne']->Centrale += $result_val['Ligne']->Centrale;
                        $final_val['Ligne']->Voicelog += $result_val['Ligne']->Voicelog;
                        $final_val['Ligne']->Poste_RDV += $result_val['Ligne']->Poste_RDV;
                        // Total Lignes
                        $final_val['Ligne']->Total = $final_val['Ligne']->Centrale + $final_val['Ligne']->Voicelog + $final_val['Ligne']->Poste_RDV;

                        $exist = true;
                    }
                }

                // If the object is not exist in $final Array we push it
                if (!$exist) {
                    array_push($final, $result_val);
                }

                // Reinitialization of $exist
                $exist = false;
            }

            // Return $final Array
            $this->response($final, RestController::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => "APPEL DOESN'T EXIST"
            ], RestController::HTTP_NOT_FOUND);
        }
    }
}
