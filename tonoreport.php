<?php
    
    require_once 'utils/ClassLoader.php';

    use PCR\model\User;
    use PCR\model\Songplan;
    use PCR\model\Song;
    use PCR\utils\Timereport;
    use PCR\utils\PCCon;
    
 
    $pccon = new PCCon();

    $actionurl ="./tonoreport.php";
    $actionurlfix ="./fixsongs.php";

    $servicetypelist =array();
    include "top.php";

    
    
    if (User::getUser()->hasAccess(USER::ACCESS_SONG) == false) {
        header("Location: index.php");
    }

    echo "<br><br>";
    
 ?>

    <form method="Post" action=<?php echo $actionurl?> > 
    <label for="quarter">Velg kvartal for å generere Tonorapport:</label><br>
        <select size=5 name="servicetype" id="servicetype">

    <?php

        $servicetype = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types");
        $i=0;
        foreach ($servicetype as $st) {
            if ($i++ == 1) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $servicetypelist[$st['id']] = $st['attributes']['name'];
            echo "<option value=\"".$st['id']."\"".$selected.">".$st['attributes']['name']."</option>";
        }
    ?>
    </select>
   <select size=5 name="quarter" id="quarter">

        <?php

            $timequarter = new Timereport();
            foreach ($timequarter->availableQuarters as $q) {
                echo "<option value=\"".$q."\">".$q."</option>";
            }
        ?>

        <br><input type="submit" name="submit" value="Generer rapport">
    </select>

    </form> 

    <?php
    if (isset($_POST['servicetype'])) {
        $servicetypeid = $_POST['servicetype'];
        //var_export($servicetypeid);
    }
    if (isset($_POST['quarter'])) {
        $quarter = $_POST['quarter'];
        $timequarter = new Timereport();
        $timequarter->setQuarter($quarter);
        $url = "https://api.planningcenteronline.com/services/v2/service_types/".$servicetypeid."/plans?per_page=100&filter=after,before&order=sort_date&after=".$timequarter->qstart."&before=".$timequarter->qend."";
    //  foreach ($plans as $plan) {

            //}
        ///var_export($url);
        $planoverview = array();
        //$plans = getData("https://api.planningcenteronline.com/services/v2/service_types/1031248/plans?per_page=100&filter=after,before&after=2020-07-01&before=2020-09-30");
        $plans = $pccon->getData($url);
        //var_dump($plans);
        //return;
        foreach($plans as $plan) {
            $plandate = $plan['attributes']['short_dates'];
            $plantitle = $plan['attributes']['title'];
            $planid = $plan['id'];
            $planinst = new Songplan($planid, $plandate, $plantitle);
            $songs = $pccon->getIncluded("https://api.planningcenteronline.com/services/v2/service_types/".$servicetypeid."/plans/$planid/items?include=song");
            //var_dump($songs);

            foreach($songs as $song) {
                //for each song in plan
                $songid = $song['id'];
                $author = $song['attributes']['author'];
                $ccli_number = $song['attributes']['ccli_number'];
                $copyright = $song['attributes']['copyright'];
                $title = $song['attributes']['title'];
                $admin = $song['attributes']['admin'];
                $planinst->songs[$songid] = new Song($songid, $author, $ccli_number, $copyright, $title, $admin);
            }
            $planoverview[$planid] = $planinst;
        }

    }

    
    if (isset($planoverview)){

        ?>

        <h3><?php echo $quarter?> Rapport for <?php echo $servicetypelist[$servicetypeid]?></h3>
        <form method="Post" action=<?php echo $actionurlfix?>> 
        <input type="submit" value="Fiks metadata på sanger i rapporten">
        <table>

        <tr>
        <th class="song">Salme nr eller <br>/verkets tittel og rettighetshaver</th>
        <th class="song">Komponist/arrangør:</th>
        <th class="song">Tekstforfatter/oversetter:</th>
        </tr>
        <?php
        $i=0;
        $songidarray = array();
        foreach ($planoverview as $po) {
            $so=$planoverview[$po->planid]->songs;
            foreach($so as $s) {

                //echo "<input  type=\"hidden\" name=\"songlist[".$i++."]\" value=\"".$song['id']."\">";
                //$songidarray[$i++] = $song['id'];

                $titleorphsalm ="";
                if(strpos($s->admin, "Salme") !== false){
                    $titleorphsalm = $s->admin;
                } else {
                    $titleorphsalm = $s->title;
                    if($s->admin != "") $titleorphsalm = $titleorphsalm." --- ".$s->admin;
                }
        ?>
                <tr>
                <input  type=hidden name="songlist[<?php echo $i++;?>]" value="<?php echo $s->songid;?>">
                <td class=song><?php echo $titleorphsalm;?></td>
                <?php
                $comparr = "";
                if($s->composer != "") {
                    $comparr = $s->composer;
                    if($s->arrangement != "") $comparr = $comparr."/".$s->arrangement;
                }
                else {
                    if($s->arrangement != "") $comparr = $s->arrangement;
                }

                $lyrtra = "";
                if($s->lyrics != "") {
                    $lyrtra = $s->lyrics;
                    if($s->translator != "") $lyrtra = $lyrtra."/".$s->translator;
                }
                else {
                    if($s->translator != "") $lyrtra = $s->translator;
                }
                echo "<td class=\"song\">".$comparr."</td>";
                echo "<td class=\"song\">".$lyrtra."</td>";

            }
        }
        echo "</tr>";
        echo "</table>";
        //echo "<input  type=\"hidden\" id=\"songlist\" name=\"songlist[]\" value=\"0\">";
        echo "</form>";
        echo "</html>";
    }

    //var_dump($planoverview);

