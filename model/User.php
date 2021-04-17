<?php 

namespace PCR\model;

use Serializable;
use PCR\utils\PCCon;

class User implements Serializable {
    
    const PC_REPORT_ACCESS_TEAM = "4438974";
    const ACCESS_SONG = "24030414";
    const ACCESS_CELLGROUP_PLANNING = "24030415"; 
    const ACCESS_SEMESTER_PLANNING = "24098834";
    const ACCESS_TEST = "24113876";

    private static $instance;

    private $name;
    private $userid;
    private $access;

    public function __construct() {

    }

    public function loadUserFromPC() {
        //var_dump("Entering constructor for User");
        $pccon = new PCCon();
        $d = $pccon->getData("https://api.planningcenteronline.com/people/v2/me");
        //var_dump($d);
        $this->name = $d['attributes']['name'] ?? null;
        $this->userid = $d['id'];
        //var_dump($this);
        
        //teamid=4438974 is the team giving access to reports
        //sangrapporter = 24030414
        //cellegruppeplanlegging = 24030415


        $a = $pccon->getRaw("https://api.planningcenteronline.com/services/v2/teams/4438974?include=person_team_position_assignments");
        //$a = $pccon->getRaw("https://api.planningcenteronline.com/services/v2/teams/" . USER::ACCESS_CELLGROUP_PLANNING ."?include=person_team_position_assignments");
        $this->access = [];

        //if(sizeof($a['included'])>0) {
            foreach ($a['included'] as $ptpa) {
                if($ptpa['relationships']['person']['data']['id'] == $this->userid) {
                    $this->access[] = $ptpa['relationships']['team_position']['data']['id'];
                }

            }
          //  }

        //var_dump($this);
    }


    public function getName() {
        //var_dump("GetName called2");
        return $this->name;
    }

    public function getAccess() {
        return $this->access;
        
    }

    public function hasAccess($posid) {
        return in_array($posid, $this->access);
    }
    


    public function serialize() {
        //var_dump($this);
        return serialize([
            'name' => $this->name,
            'userid' => $this->userid,
            'access' => $this->access, 
        ]);
    }

    public function unserialize($data) {
        //var_dump($this);
        $d = unserialize($data);
        $this->name = $d['name'];
        $this->userid = $d['userid'];
        $this->access = $d['access'];
    }


    public static function getUser() {

        //session_start();
        //var_dump(self::$instance);
        if (self::$instance == null) {
            //var_dump("stored session: ");
            //var_dump($_SESSION['userinfo2']);
            //if (false && isset($_SESSION['userinfo'])) {
            if (isset($_SESSION['userinfo'])) {
                self::$instance = unserialize($_SESSION['userinfo']);
            } else {
                $user = new User();
                $user->loadUserFromPC();
                $_SESSION['userinfo'] = serialize($user);
                self::$instance = $user;
            }
        }

        return self::$instance;
    }

}//