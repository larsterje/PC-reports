<?php
namespace PCR\model;

class Arrangement {
    public $id;
    public $name;
    public $attributes; 
    public $songconfig = array();
    public $spotifylink = array();

    function __construct($ar) {
        $this->id = $ar['id'];
        $this->attributes = $ar['attributes'];
        $this->name = $ar['attributes']['name'];
    }

}