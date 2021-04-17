<?php

namespace PCR\model;

use PCR\utils\PCCon;


class ServiceType {
    private $pccon;
    private $serviceType;
    private $serviceTemplates;
    private $serviceTeams;

    public function __construct($data) {
        $this->setServiceTemplates($data);
        $this->setServiceTeams($data);
    }

    private function setServiceTemplates($data) {
        $this->serviceType = $data;
        $pccon = new PCCon();
        error_log("https://api.planningcenteronline.com/services/v2/service_types/" . $this->serviceType['id'] . "/plan_templates");
        $this->serviceTemplates = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/" . $this->serviceType['id'] . "/plan_templates");
        return $this->serviceTemplates;
    }

    private function setServiceTeams($data) {
        $this->serviceTeams = $data;
        $pccon = new PCCon();
        error_log("https://api.planningcenteronline.com/services/v2/service_types/" . $this->serviceType['id'] . "/teams");
        $this->serviceTeams = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/" . $this->serviceType['id'] . "/teams");
        return $this->serviceTeams;
    }

    public function getServiceTemplates() {
        return $this->serviceTemplates;
    }

    public function getServiceType() {
        return $this->serviceType;
    }

    public function getData() {
        return $this->serviceType;
    }

    public function getTemplates() {
        $templates = [];
        foreach ($this->serviceTemplates as $data) {

            $t = [
                'id' => $data['id'],
                'type' => $data['type'],
                'name' => $data['attributes']['name'],
            ];
            array_push ($templates, $t);
        }
        return $templates; 
    }

    public function getTeams() {
        $teams = [];
        foreach ($this->serviceTeams as $data) {

            $t = [
                'id' => $data['id'],
                'type' => $data['type'],
                'name' => $data['attributes']['name'],
            ];
            array_push ($teams, $t);
        }
        return $teams; 
    }

}
