<?php

namespace PCR\model;

use PCR\model\Helper;

class AllPlans {
   public $allPlansRaw;
   public $selectedPlans = null;
   public $plans;

   function __construct() {
      // new Helper();
       $this->allPlansRaw = Helper::getAllPlansRaw();
       $this->plans = array();
       $this->fillPlans();
//       $this->fillPlansWithTeams();
   }

   function fillSelectedPlansWithTeams($planArray) {
       if ($planArray == null) return;
       //var_dump($planArray);
       $selectedPlans = array();
       foreach ($planArray as $planId) {
           $this->selectedPlans[$planId] = $this->plans[$planId];
           $this->selectedPlans[$planId]->set_teams();
       }
   }

   function fillSelectedPlansWithCell($planArray) {
        if ($planArray == null) return;
        //var_dump($planArray);
        $selectedPlans = array();
        foreach ($planArray as $planId) {
            $this->selectedPlans[$planId] = $this->plans[$planId];
            //$this->selectedPlans[$planId]->set_teams();
            $this->selectedPlans[$planId]->set_cellgroup();
        }

   }

   function fillPlans(){

       foreach ($this->allPlansRaw as $planRaw) {

           $planId = $planRaw['id'];
           $dates = $planRaw['attributes']['dates'];
           $sort_date = $planRaw['attributes']['sort_date'];
           $title = $planRaw['attributes']['title'];
           $seriestitle = $planRaw['attributes']['series_title'];
           //var_export("planId: ".$planId);
           $p = new Plan($planId, $dates, $sort_date, $title, $seriestitle);
           //$p->set_plan();
           //$p->set_teams();
           $this->plans[$planId] = $p;
       }
   }

   function fillPlansWithTeams() {
        foreach ($this->plans as $plan) {
            $plan->set_teams();
        }

   }

   function getSelectedPlans() {
       return $this->selectedPlans;
   }
   function getPlans() {
       return $this->plans;
   }

}