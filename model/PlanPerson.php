<?php


namespace PCR\model;

class PlanPerson {
    public $id;
    public $fullname;
    public $status;

    function __construct($id, $fullname, $status) {
        if ($status == "C") {
            $this->status = "style=\"color:green;\"";
        } elseif ($status == "U") {
            $this->status = "style=\"color:orange;\"";
        } elseif ($status == "D") {
            $this->status = "style=\"text-decoration: line-through; color:red;\"";
        } else {
            $this->status = "black";
        }
        //$this->id = $id;
        $this->id = $id;
        //$this->status =$status;
        $this->fullname = $fullname;
    }

}
