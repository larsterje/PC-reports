<?php

    require_once 'utils/ClassLoader.php';
        
    use PCR\utils\OAuthUtils;
    use PCR\utils\Environment;
    use PCR\model\Helper;
    use PCR\model\AllPlans;
   
    //session_start();
    //require_once "team.php";
    
    $actionurl ="./responsibilities.php";

    $fp = new AllPlans();
    $plansAll = $fp->getPlans();


    if (isset($_POST['plans'])) {
        $selectedplans = $_POST['plans'];
        $fp->fillSelectedPlansWithTeams($selectedplans);
    //  foreach ($plans as $plan) {

        //}
    }
    $plans = $fp->getSelectedPlans();

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

        <br><input type="submit" name="submit" value="Generer rapport">
    </select>
    </form>
    <span> Det kan ta litt tid å generere oversikt hvis mange blir valgt. (10 planer tar ca 15 sekund)<br>
    
    <?php
    if (isset($plans)){
        echo "<table>";
        echo "<tr>";
        echo "<th class=\"teamname\" th>Arrangement</th>";
        //td>".$row['table_name']."</td><td>".$row['table_name']."</td><td>".$row['table_name']."</td><td>".$row['table_name’]."</td></tr>";

        foreach (Helper::$teams as $team) {
            echo "<th class=\"teamname\">".$team->teamname."</th>";
        }
        echo "</tr>";
        
        foreach ($plans as $plan) {
            echo "<tr>";

            $planId = $plan->planid;
            $date = $plan->time;
            $seriestitle = $plan->seriestitle;
            echo "<td><div  class=\"plandate\">".$date."</div><div class=\"seriestitle\">".$seriestitle."</div></td>";

            foreach (Helper::$teams as $team) {
                echo "<td>";
                $schedTeam = $plan->get_team($team->id);
                foreach ($schedTeam->positions as $pos) {
                    echo "<span class=\"position\">".$pos->position."</span><br>";
                    foreach ($pos->planpersons as $pl) {
                        echo "<span ".$pl->status."\" class=\"personname\">".$pl->fullname."</span><br>";
                    }
                    
                }
                echo "</td>";
            }
            echo "</tr>";


        }

        echo "</table>";

    }?>
    <span style="color:green">Grønn betyr bekreftet</span><br>
    <span style="color:orange">Orange betyr ubekreftet</span><br>
    <span style="color:red">Rød betyr at vedkommende har "declined"</span>

    </html>