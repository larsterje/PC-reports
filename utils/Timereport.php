<?php

namespace PCR\utils;

class Timereport {
    public $qstart;
    public $qend;
    public $availableQuarters = array("Q1-2020", "Q2-2020", "Q3-2020", "Q4-2020", "Q1-2021", "Q2-2021", "Q3-2021", "Q4-2021");

    function setQuarter($quarter) {
        
        if ($quarter == "Q1-2020") {
            $this->qstart = "2020-01-01";
            $this->qend = "2020-03-31";
        } elseif ($quarter == "Q2-2020") {
            $this->qstart = "2020-04-01";
            $this->qend = "2020-06-30";
        } elseif ($quarter == "Q3-2020") {
            $this->qstart = "2020-07-01";
            $this->qend = "2020-09-30";
        } elseif ($quarter == "Q4-2020") {
            $this->qstart = "2020-10-01";
            $this->qend = "2020-12-31";
        } elseif ($quarter == "Q1-2021") {
            $this->qstart = "2021-01-01";
            $this->qend = "2021-03-31";
        } elseif ($quarter == "Q2-2021") {
            $this->qstart = "2021-04-01";
            $this->qend = "2021-06-30";
        } elseif ($quarter == "Q3-2021") {
            $this->qstart = "2021-07-01";
            $this->qend = "2021-09-30";
        } elseif ($quarter == "Q4-2021") {
            $this->qstart = "2021-10-01";
            $this->qend = "2021-12-31"; 
        } else {
            var_export("error with input;");
            var_dump($quarter);
        }

    }


}
