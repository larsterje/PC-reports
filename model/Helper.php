<?php

namespace PCR\model;

use PCR\utils\PCCon;



class Helper {
    public static $teamsAndPositions;
    public static $allPlansRaw;
    //public static
    public static $teams;
    public static $teampositionid;
    private static $pccon;

    private function __construct() {

    }
    public static function setup() {
 
        self::$pccon = new PCCon();
        self::$teamsAndPositions = self::$pccon->getRaw("https://api.planningcenteronline.com/services/v2/service_types/1031248/teams?include=team_positions");
        self::$allPlansRaw = self::$pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/1031248/plans?filter=future&order=sort_date&per_page=100");
        //self::$allPlansRaw = getData("https://api.planningcenteronline.com/services/v2/service_types/1031248/plans?filter=future&order=sort_date&per_page=5");
        self::buildTeams();
    }

    public static function getAllPlansRaw() {
        return self::$allPlansRaw;
    }

    public static function buildTeams() {
        self::$teams = array();
        foreach (self::$teamsAndPositions['data'] as $team) {
            $teamId = $team['id'];
            $teamName = $team['attributes']['name'];
            self::$teams[$teamId] = new Team($teamId, $teamName);

        }
        foreach (self::$teamsAndPositions['included'] as $pos) {
            $teamid=$pos['relationships']['team']['data']['id'];
            $posid=$pos['id'];
            $posname=$pos['attributes']['name'];
            
            self::$teams[$teamid]->addPosition($posid, $posname);
        }
        //var_dump(self::$teams);
    }

    public static function findPositionId($teamid, $posname) {
        if(array_key_exists($teamid, self::$teams)) {

            return self::$teams[$teamid]->getPosistionId($posname);
        } else {
            return "00";
        }
    }

    public static function getTeam($teamId) {
        return self::$teams[$teamId];
    }

    public static function findTeamName($teamId) {
        return self::$teams[$teamId];
    }
}

Helper::setup();
