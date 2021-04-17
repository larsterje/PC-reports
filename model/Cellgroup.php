<?php
namespace PCR\model;

class Cellgroup {
        public $id; //same as id for the TeamPosition
        public $name; //team_position_name
        public $leaders = array();

        public function addleaders($cellgroupleader) {
            $this->leaders[$cellgroupleader->id] = $cellgroupleader;
        }

    }