<?php

namespace PCR\model;

use PCR\model\PlanPerson;


class Position {
    public $id;
    public $position;
    public $planpersons; //array

    function __construct($posid, $position) {
        $this->planpersons = array();
        $this->id = $posid;
        $this->position =$position;
    }


    function addPlanPerson($planpersonid, $full_name, $status) {
        $this->planpersons[$planpersonid] = new PlanPerson($planpersonid, $full_name, $status);
    }

 
}
