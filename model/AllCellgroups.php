<?php
namespace PCR\model;

use PCR\utils\PCCon;
use PCR\model\Helper;
use PCR\model\Cellgroup;
use PCR\model\Cellgroupleader;

class AllCellgroups {

    
    public $teamId; 
    public $cellgroups = array();
    public $cellGroupNameId = array();
    private $pccon;

    public function __construct() {
        $this->pccon = new PCCon();
    }

    /**
     * Reads all cellgroups in Bogafjell
     * 
     * @param integer $teamid The id of the team aøsdfaøsdf
     */
    public function fillcellgroups($teamId) {
        $this->teamId = $teamId;
        //$allpositions = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/1031248/team_positions?per_page=100");

        $alldata = $this->pccon->getRaw("https://api.planningcenteronline.com/services/v2/teams/".$teamId."?include=team_positions,person_team_position_assignments,people");

        $this->teamId = $alldata['data']['id'];
        $incl = $alldata['included'];
        $persons = array();

        foreach ($incl as $item) {
            if ($item['type'] == "TeamPosition") {
                $cell = new Cellgroup();
                $cell->id = $item['id'];
                $cell->name = $item['attributes']['name'];
                $this->cellgroups[$cell->id] = $cell;
            }
            if ($item['type'] == "Person") {
                $persons[$item['id']] = $item['attributes']['full_name'];
            }
            
        }
        
        foreach ($incl as $item) {
            if ($item['type'] == "PersonTeamPositionAssignment") {
                $cellgroupleader = new Cellgroupleader();
                $cellgroupleader->id = $item['id'];
                $cellgroupleader->personid = $item['relationships']['person']['data']['id'];
                $cellgroupleader->fullname = $persons[$cellgroupleader->personid];
                $this->cellgroups[$item['relationships']['team_position']['data']['id']]->addleaders($cellgroupleader);
            }
        }

        foreach ($this->cellgroups as $group) {
            $this->cellGroupNameId[$group->name] = $group->id;
        }

    }

    public function printcellgroup($id) {
        if(array_key_exists($id, $this->cellgroups)){
            $cellname = $this->cellgroups[$id]->name;
            $cellleaders = $this->cellgroups[$id]->leaders;
            echo "<span class=\"position\">".$cellname."</span><br>";
            foreach ($cellleaders as $l) {
                //echo "<span ".$pl->status."\" class=\"personname\">".$pl->fullname."</span><br>";
                echo "<span class=\"personname\">".$l->fullname."</span><br>";
            }
        } else {
            echo "";
        }



    }
    
    public function scheduleNewCellgroup($cellGroupTeamId, $existingCellGroupId, $newCellGroupId, $serviceTypeId, $planId) {
        //var_export("\n".$cellGroupTeamId."\n".$existingCellGroupId."\n".$newCellGroupId."\n".$serviceTypeId."\n".$planId)."\n";

        $statusOnUpdate ="";

        //using cellgroupId=99 as code for deleting
        if(intval($newCellGroupId) == 99) {
            //var_export("Removing cellgroup from plan: ".$planId."<br>");
            $returnCode = $this->removePersonsFromSchedule($cellGroupTeamId, $existingCellGroupId, $serviceTypeId, $planId);
            $statusOnUpdate =  $returnCode;
            $returnCode = $this->removeNeededPosition($cellGroupTeamId, $existingCellGroupId, $serviceTypeId, $planId);
            $statusOnUpdate = $statusOnUpdate . $returnCode;
        }

        // as long as cellgroupid is not 0 a update of cellgroup is requested
        elseif(intval($newCellGroupId) != 0) {
            //var_export("updating plan: ".$planId."<br>");
            $returnCode = $this->removePersonsFromSchedule($cellGroupTeamId, $existingCellGroupId, $serviceTypeId, $planId);
            $statusOnUpdate = $statusOnUpdate . $returnCode;

            $returnCode = $this->removeNeededPosition($cellGroupTeamId, $existingCellGroupId, $serviceTypeId, $planId);
            $statusOnUpdate = $statusOnUpdate . $returnCode;
            
            $returnCode = $this->addNewCellGroup($newCellGroupId, $serviceTypeId, $planId);
            $statusOnUpdate = $statusOnUpdate . $returnCode;
            
        }

        return $statusOnUpdate;
        
    }


    public function removePersonsFromSchedule($teamId, $teamPositionId, $serviceTypeId, $planId) {
        $teamMembers = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/".$serviceTypeId."/plans/".$planId."/team_members?per_page=100");

        $PlanPersonsToRemove = array();
        foreach ($teamMembers as $planPerson) {
            //var_export($planPerson['attributes']['team_position_name']);
            //check if the team_postion_name is a cellgroup
            if(array_key_exists($planPerson['attributes']['team_position_name'], $this->cellGroupNameId)) {
                $PlanPersonsToRemove[$planPerson['id']] = $planPerson;
            }
        }
        $responsetext = "";
        foreach($PlanPersonsToRemove as $remove) {
            $personId = $remove['relationships']['person']['data']['id'];
            $planPersonId = $remove['id'];

            $jsonData = [
                'data' => [
                    'type' => $remove['type'],
                    'id' => $remove['id']
                ]
            ];
            $payload = json_encode($jsonData);
            
            $delurl = "https://api.planningcenteronline.com/services/v2/people/".$personId."/plan_people/".$planPersonId;
            
            //$this->pccon->DELETE CALL
            $returnvalue = $this->pccon->DELETE($delurl, $payload);
            $responsetext = $responsetext . "Fjernet personid fra plan: " . $remove['id']. " med returkode: " . $returnvalue . "; <br>";
            //var_export ("\nDelete url: ".$delurl."\nPayload:\n".$payload."\n\nReturn value: ".$returnvalue."\n\n");
            //commented out while testing "new tono report"
            //$return = patch($url, $payload);

        }
        return $responsetext;
    }


    public function removeNeededPosition($teamId, $teamPositionId, $serviceTypeId, $planId) {
        $neededPositions = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/".$serviceTypeId."/plans/".$planId."/needed_positions");

        $neededPositionsToRemove = array();
        foreach ($neededPositions as $position) {
            //var_export($planPerson['attributes']['team_position_name']);

            //check if the team_postion_name is a cellgroup
            if(array_key_exists($position['attributes']['team_position_name'], $this->cellGroupNameId)) {
                $neededPositionsToRemove[$position['id']] = $position;
            }
        }
        $responsetext = "";
        foreach($neededPositionsToRemove as $remove) {
            //$personId = $remove['relationships']['person']['data']['id'];
            $neededPositionId = $remove['id'];

            $jsonData = [
                'data' => [
                    'type' => $remove['type'],
                    'id' => $remove['id']
                ]
            ];
            $payload = json_encode($jsonData);
            
            $delurl = "https://api.planningcenteronline.com/services/v2/service_types/".$serviceTypeId."/plans/".$planId."/needed_positions/".$neededPositionId;

            //$this->pccon->DELETE CALL
            $returnvalue = $this->pccon->DELETE($delurl, $payload);
            $responsetext = $responsetext . "Fjernet posisjonsid: " . $remove['id']. " med returkode: " . $returnvalue . "; <br>";
            //var_export ("\nDelete url: ".$delurl."\nPayload:\n".$payload."\n\nReturn value: ".$returnvalue."\n\n");
            
        }

        return $responsetext;
    }

    public function addNewCellGroup($teamPositionId, $serviceTypeId, $planId) {
        $cellTeamLeaders = $this->cellgroups[$teamPositionId]->leaders;
        $responsetext = "";
        foreach($cellTeamLeaders as $leader) {
            $jsonData = [
                'data' => [
                    'type' => "PlanPerson",
                    'attributes' => [
                        'team_position_name' => $this->cellgroups[$teamPositionId]->name,
                        'status' => "C"
                    ],
                    'relationships' => [
                        'team' => [
                            'data' => [
                                'type' => "Team",
                                'id' => $this->teamId
                            ],
                        ],
                        'person' => [
                            'data' => [
                                'type' => "Person",
                                'id' => $leader->personid
                            ]
                        ]
                        
                    ]
                ]
            ];
            $payload = json_encode($jsonData);


            $newUrl = "https://api.planningcenteronline.com/services/v2/service_types/".$serviceTypeId."/plans/".$planId."/team_members";

            $postresponse =$this->pccon->POST($newUrl, $payload);
            $responsetext = $responsetext . "La til " . $leader->fullname . " med returkode: " . $postresponse['ResponseCode'] . "; <br>";
            // $postresponse['ResponseData'] = $resonse;
//            $postresponse['ResponseCode'] = $responseCode;
            //var_export ("\nPost url: ".$newUrl."\nPayload:\n".$payload."\nResponecode: ".$postresponse['ResponseCode']."\nResponseData: \n".$postresponse['ResponseData']."\n\n");
            
        }
        return $responsetext;
    }


}

