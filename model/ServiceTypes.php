<?php

namespace PCR\model;

use PCR\model\ServiceType;
use PCR\utils\PCCon;

class ServiceTypes {
    //private $pccon;
    private $serviceTypes = [];

    public function __construct() {
        $this->setServiceTypesWithTemplates();
    }

    private function setServiceTypesWithTemplates() {
        $pccon = new PCCon();
        $types = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types");
        foreach ($types as $type) {
            $this->serviceTypes[$type['id']] =  new ServiceType($type);
        }
    }

    public function getServiceTypes() {
        return $this->serviceTypes;
    }

    public function getServiceTypesAsJson() {

        $types = [];
        foreach ($this->serviceTypes as $stype) {
            $templates = $stype->getTemplates();
            $teams = $stype->getTeams();
            $data = $stype->getData();

            $type = [
                'id' => $data['id'],
                'type' => $data['type'],
                'name' => $data['attributes']['name'],
                'templates' => $templates,
                'teams' => $teams
            ];
            array_push($types, $type);
        }
        return json_encode($types);
    }

}