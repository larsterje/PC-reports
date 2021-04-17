<?php

namespace PCR\model;

class Songplan {
    public $planid;
    public $plandate;
    public $plantitle;
    public $songs = array();

    function __construct($planid, $plandate, $plantitle) {
        $this->planid = $planid;
        $this->plandate = $plandate;
        $this->plantitle = $plantitle;
    }
}