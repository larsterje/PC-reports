<?php

namespace PCR\model;

use PCR\model\Helper;
use PCR\model\Position;

class Team {
    public $id;
    public $teamname;
    public $positions;

    function __construct($id, $teamname) {
        $this->id = $id;
        $this->teamname = $teamname;
        $this->positions  = array();
        //$this->helper = Helper::getInstance();
    }

    function get_position() {
        return $this->positions;
    }


    function setPosition($teamid, $full_name, $position, $status, $planpersonid) {
        $posid = Helper::findPositionId($teamid, $position);

        if(!array_key_exists($posid, $this->positions)) {
            $this->positions[$posid] = new Position($posid, $position);
            $this->positions[$posid]->addPlanPerson($planpersonid, $full_name, $status);
        } else {
            $this->positions[$posid]->addPlanPerson($planpersonid, $full_name, $status);
         }

    }

    //these to functions is only used by Helper class to get positionID from a posistion name
    function addPosition($id, $name) {
        $this->positions[$name] = new Position($id, $name);
        //$this->positions[$name] = $id;
    }
    function getPosistionId($name) {
        return $this->positions[$name]->id;
    }
    //


    function sortPositions() {
        return true;

    }
  
    function getId() {
        return $this->id;
    }

    // Methods
    function set_teamname($name) {
        $this->teamname = $name;
    }


    function get_name() {
        return $this->teamname;
        //shouldnt this be teamname
    }
}
