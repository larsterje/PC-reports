<?php
    require_once 'utils/ClassLoader.php';
    
//    use PCR\utils\OAuthUtils;
    use PCR\utils\Environment;
    use PCR\model\User;
    use PCR\model\Helper;
    use PCR\model\AllPlans;
    use PCR\model\AllCellgroups;

    //session_start();
    //require_once "cellgroups.php";

  //  $pccon = new PCCon();

    $actionurl ="./cellresp.php";

    if (User::getUser()->hasAccess(USER::ACCESS_CELLGROUP_PLANNING) == false) {
        header("Location: index.php");
    }

    //Helper::getInstance();
    $fp = new AllPlans();
    $plansAll = $fp->getPlans();


    if (isset($_POST['plans'])) {
        $selectedplans = $_POST['plans'];
//        $fp->fillSelectedPlansWithCell($selectedplans);
        $fp->fillSelectedPlansWithTeams($selectedplans);
    //  foreach ($plans as $plan) {

        //}
    }
    $plans = $fp->getSelectedPlans();

//    if (isset($_POST['cellgroups'])) {
        //var_dump($_POST['cellgroups']);
//    }

    //$_POST['plans'] ?? null;

    include "top.php";

    ?>

    <form method="Post" action=<?php echo $actionurl?> > 
    <label for="plans">Velg planer (bruk shift eller ctrl for å velge flere):</label><br>
    <select multiple size=12 name="plans[]" id="plans">

        <?php
            foreach ($plansAll as $plan) {
                $planId = $plan->planid;
                $date = $plan->time;
                $type = $plan->seriestitle;
                echo "<option value=\"".$planId."\">".$date." ".$type."</option>";
            }
        ?>
        <input type=hidden name=selectplanpushed value=true>    
        <br><input type="submit" name="submit" value="Endre cellegruppe">
    </select>
    </form>
    <span> Det kan ta litt tid å generere oversikt hvis mange blir valgt. (10 planer tar ca 15 sekund)<br>
    
    <?php 
    
    //genereate the overview of selected plans with scheduled cell groups
    if (isset($_POST['selectplanpushed']) && $_POST['selectplanpushed'] == "true")  {

        //Hardcode that we are working with Team cellegrupper in servicetype Gudstjeneste
        $cellteam = Helper::getTeam("4207047"); 
        $cellgrouppos = $cellteam->get_position();
//        $posnames = array_keys($cellgrouppos);
//        $posids = array_values($cellgrouppos);
//        var_dump($cellgrouppos);

        ?>

        <form method="Post" action=<?php echo $actionurl?> > 
            <table>
            <tr>
            <th class="teamname">Arrangement</th>
            <th class="teamname">Cellegruppe med ansvar</th>
            <th class="teamname">Ny cellegruppe</th>
            </tr>
            <?php
            $j=0;
            $k=0;
            foreach ($plans as $plan) {
                echo "<tr>";

                $planId = $plan->planid;
                $date = $plan->time;
                $seriestitle = $plan->seriestitle;
                echo "<td><input type=hidden name=planid[".$j++."] value=\"".$planId."\"><div  class=\"plandate\">".$date."</div><div class=\"seriestitle\">".$seriestitle."</div></td>";
                    //var_dump($plan);
                    //return;4048205
                    $schedTeam = $plan->get_team("4207047"); //ID for cellgroup 4207047
                    echo "<td>";

                    foreach ($schedTeam->positions as $pos) {
                        echo "<span class=\"position\">".$pos->position."</span><input type=hidden name=existingcellid[".$k++."] value=\"".$pos->id."\"><br>";
                        foreach ($pos->planpersons as $pl) {
                            echo "<span ".$pl->status."\" class=\"personname\">".$pl->fullname."</span><br>";
                        }
                        
                    }
                ?>
                </td>
                <td>
                <select name="cellgroups[]" id="cell">
                    <option selected value="0"></option>
                    <option value="99">#slett cellegruppegruppe fra plan</option> 

                    <?php
                        foreach ($cellgrouppos as $pos) {
                            $posname = $pos->position;
                            $posid = $pos->id;
                            echo "<option value=\"".$posid."\">".$posname."</option>";
                        }
                    ?>

                    <br>
                </select>
                
                </td>
                </tr>

      <?php } ?>
            </table>
    
            <input type=hidden name=updatecellpushed value=true>
            <input type="submit" name="submit" value="Oppdater cellegrupper">
        </form>
    <?php } ?>
    <span style="color:green">Grønn betyr bekreftet</span><br>
    <span style="color:orange">Orange betyr ubekreftet</span><br>
    <span style="color:red">Rød betyr at vedkommende har "declined"</span>

    <?php
    //!!Deletes the old cellegruppe and adds the new one to the plan with leaders.
    if (isset($_POST['updatecellpushed']) && $_POST['updatecellpushed'] == "true")  {
        $planids = $_POST['planid'];
        $newcellgroups = $_POST['cellgroups'];
        $existingcellid = $_POST['existingcellid'];


        $c = new AllCellgroups();

        $cellGroupTeamId = "4207047"; //Hardcode team type Cellegruppe
        $serviceTypeId = "1031248"; //Hardcode servicetype Gudstjeneste
        $c->fillcellgroups($cellGroupTeamId);


        echo "<table>";
        ?>
        <tr>
            <th>Arrangement</th>
            <th>Cellegruppe id</th>
            <th>Ny cellegruppe</th>
            <th>Status code</th>
        </tr>
        <?php
        for($i=0; $i<count($planids); $i++) {
            //$planId = $plans[$i]->planid;
            //var_dump($fp);
            $date = $fp->plans[$planids[$i]]->time;
            //var_dump($date);
            $seriestitle = $fp->plans[$planids[$i]]->seriestitle;
        ?>
            <tr>
            <td><div class="plandate"><?php echo $date ?></div><div class="seriestitle"><?php echo $seriestitle ?></div></td>
            <td><?php echo $newcellgroups[$i] ?></td>
            <td><?php $c->printcellgroup($newcellgroups[$i]) ?></td>
            <td><?php echo $c->scheduleNewCellGroup($cellGroupTeamId, $existingcellid[$i],$newcellgroups[$i], $serviceTypeId, $planids[$i]) ?></td>


            <tr>

        <?php } 
        echo "</table>";

        //var_dump($_POST);
    }
    ?>

    </html>