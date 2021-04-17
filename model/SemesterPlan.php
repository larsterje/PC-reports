<?php 

namespace PCR\model;

use PCR\utils\PCCon;
use DateTime;

class SemesterPlan {

    private $pccon = null;

    function __construct()
    {
        $this->pccon = new PCCon();
        
    }

    function postCreatePlanFromTemplate($serviceTypeSelector, $templateId) {

        $postCreateService = [
            'data' =>[
                'type'=> 'ServiceType',
                'attributes' => [
                    'count' => 1,
                ],
            ], 'relationships' => [
                'plan_templates' => [
                    'data' => [
                        'type' => 'PlanTemplate',
                        'id' => $templateId
                    ]
                ]
            ]
        ];
        //echo "<br><br>";
        //var_dump($postCreateService);
        
        $postresponse = $this->pccon->POST("https://api.planningcenteronline.com/services/v2/service_types/". $serviceTypeSelector ."/create_plans", json_encode($postCreateService));
        //var_dump($postresponse);

        $responseData = $postresponse['ResponseData'];
        $resp = [];
        $resp['ResponseCode'] = $postresponse['ResponseCode'];
        $resp['planId'] = $responseData['data'][0]['id'];

        //var_dump($resp);
        return $resp;
        

    }

    function postCreatePlanTime($planId, $serviceTypeSelector, $indate, $intime, $inEndTime) {
        
        $startDate = $this->fixDate($indate, $intime);
        $endDate = $this->fixDate($indate, $inEndTime);
        //echo "<br><br>OK";
        $postCreatePlanTime = [
            'data' =>[
                'type'=> 'PlanTime',
                'attributes' => [
                    'time_type' => 'service',
                    'starts_at' => $startDate,
                    'ends_at' => $endDate
                ]
            ]
        ];

        //echo "<br><br>postCreatePlanTime<br>"; 
        //var_dump(json_encode($postCreatePlanTime));

        $postUrl = "https://api.planningcenteronline.com/services/v2/service_types/". $serviceTypeSelector . "/plans/" . $planId ."/plan_times";
        //echo "<br><br>". $postUrl;
        $postresponse = $this->pccon->POST($postUrl, json_encode($postCreatePlanTime));

        //echo ("<br><br>Response from Create PlanTime <br><br>");
        //var_dump($postresponse);
        return $postresponse;
   
        //$endDate = $startDate;
        //date_add($endDate, date_interval_create_from_date_string("2 hours"));

    }

    function patchPlanTitle($planId, $serviceType, $serviceTitle, $serviceSeries) {
        $postCreatePlanTitle = [
            'data' =>[
                'type'=> 'Plan',
                'attributes' => [
                    'series_title' => $serviceSeries,
                    'title' => $serviceTitle
                ]
            ]
        ];

        //echo "<br><br>postCreatePlanTitle<br>"; 
        //var_dump(json_encode($postCreatePlanTitle));

        $postUrl = "https://api.planningcenteronline.com/services/v2/service_types/". $serviceType . "/plans/" . $planId;
        //echo "<br><br>". $postUrl . "<br>";
        $postresponse = $this->pccon->patch($postUrl, json_encode($postCreatePlanTitle));
        $resp = [];
        $resp['ResponseCode'] = $postresponse;
        //var_dump($postresponse);
        return $resp;

    }

    /**
     * 
     */
    function fixDate($indate, $intime) {
        $date = new DateTime($indate);
        $t = explode(':', $intime);
        $date->setTime($t[0], $t[1], $t[2]);

        $date = $this->checkSummertime($date);
        $date_as_string = date_format($date,"Y-m-d") . "T" . date_format($date, "H:i:s") ."Z";
        //$date->setTimezone(new DateTimeZone())
        //date_time_set($date, $t[0],$t[1],$t[2]);
        //date_timezone_set($date, timezone_open("Europe/Oslo"));
        return $date_as_string;
    }


    function checkSummertime($fdate) {
        if(date_format($fdate,"I")) {
            date_sub($fdate, date_interval_create_from_date_string("2 hours"));
        } else {
            date_sub($fdate, date_interval_create_from_date_string("1 hours"));
        }
        return $fdate;
    }


}


