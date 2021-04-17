<?php

namespace PCR\model;

use PCR\utils\PCCon;
use PCR\model\Helper;
use PCR\model\Team;

class Plan {
    public $planid;
    public $time;
    public $sort_date;
    public $seriestitle;
    public $title;
    public $teams; //array

    private $pccon;
    //private $helper;
  
    //arrays of static information about future plans and all teams
    public $planCollection;
    public $teamCollection;
  
    function __construct($planId, $dates, $sort_date, $title, $seriestitle) {
      //    var_dump($id);
            $this->pccon = new PCCon();
            //Helper::getInstance();
            $this->planid = $planId;
            $this->time = $dates;
            $this->sort_date = $sort_date;
            $this->title = $title;
            $this->seriestitle =$seriestitle;
            $this->teams = array();
      //      var_export($id);
      //     var_export($this->planid);
        }
  
        function get_teams() {
            return $this->teams;
        }
      
        function get_team($id) {
            //called from presentation layer. If team with ID is not scheduled, add a blank position to avoid errors
            if(!array_key_exists($id, $this->teams)) {
              $t = new Team($id, null);
              $t->set_teamname(Helper::findTeamName($id)->get_name());
              $t->setPosition("", "", "", "", "");
  
              $this->teams[$id] = $t;
  
            }
            return $this->teams[$id];
        }
  
 
  
    function set_teams() {                  
  
          //returns a list of planperson objects
          $pp = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/1031248/plans/".$this->planid."/team_members");
  
          foreach ($pp as $planperson) {
              $teamId = $planperson['relationships']['team']['data']['id'];
              //var_export("## teamID: ".$teamId." ##");
              $t=null;
  
              if (array_key_exists($teamId, $this->teams)) {
                  //var_export("###team exsists ### ");
                  $t = $this->teams[$teamId];
              } else {
                  $t = new Team($teamId, null);
                  $t->set_teamname(Helper::findTeamName($teamId));
                  $this->teams[$teamId] = $t;
              }
  
              $t->setPosition($teamId, $planperson['attributes']['name'], $planperson['attributes']['team_position_name'], $planperson['attributes']['status'], $planperson['id']);
              $t->sortPositions();
          }
      }
  
      function set_cellgroup() {
  
      }
  
  }