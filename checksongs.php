<?php
     require_once 'utils/ClassLoader.php';

    use PCR\model\User;
    use PCR\model\Song;
    use PCR\utils\PCCon;
    use PCR\utils\Environment;
 

    $songoverview = array();
     
    $pccon = new PCCon();
    $actionurl ="./checksongs.php";

    $createspotifyurl ="./playlistgenerator.php";

    $spotifylistserviceids = array();
    $spotifylist = array();

    ///if getRaw() r['links']['next'] exists

    include "top.php";

    if (User::getUser()->hasAccess(USER::ACCESS_SONG) == false) {
        header("Location: index.php");
    }


    echo "<br><h2>Sjekk at sanger som ligger i databasen er lagt inn riktig, med riktig info</H2><br>";

  
    // check if songs are in session if not load all
    if (isset($_SESSION['allsongs'])){
        $songs = unserialize($_SESSION['allsongs']);
    } else { 
        $r = $pccon->getRaw("https://api.planningcenteronline.com/services/v2/songs?per_page=100&order=title");
        $songs = $r['data'];

        while (isset($r['links']['next'])) {
            $r = $pccon->getRaw($r['links']['next']);
            $songs = array_merge($songs, $r['data']);
        }
        $_SESSION['allsongs'] = serialize($songs);
    }

    $services = null;
    $servicetypes =null;

    //load all future services and put them in session
    if (isset($_SESSION['futureservices'])){
        $services = unserialize($_SESSION['futureservices']);
        $servicetypes = unserialize($_SESSION['servicetypes']);
    } else {
        $sts= $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types");
        $servicetypes = array();
        foreach($sts as $st) {
            $servicetypes[$st['id']] = $st;
        }
        $services = array();
        foreach($servicetypes as $type) {
            $s = $pccon->getdata("https://api.planningcenteronline.com/services/v2/service_types/".$type['id']."/plans?filter=future&per_page=100");
            $services = array_merge($services, $s);
       }

       usort($services, function ($a, $b) {
       return $a['attributes']['sort_date'] <=> $b['attributes']['sort_date'] ;
       });


        $_SESSION['futureservices'] = serialize($services);
        $_SESSION['servicetypes'] = serialize($servicetypes);
    }

    //var_dump($services);



    ?>
    <table>
    <tr>
    <td>

    <form method="Post" action=<?php echo $actionurl ?> >

    <label for="plans">Velg sanger som skal kontrolleres<br>(bruk shift eller ctrl for å velge flere):</label><br>
    
    <select multiple size=12 name="songlist[]" id="songlist">

        <?php
            // put all songs in table with songid as index
            foreach ($songs as $song) {
                echo "<option value=\"".$song['id']."\">".$song['attributes']['title']."</option>";
                $songoverview[$song['id']] = $song;
            }
        ?>

    </select>
    <br><input type="submit" name="submit" value="Sjekk valgte sanger">
    </form>
    </td>
    <td>
    <form method="Post" action=<?php echo $actionurl ?> > 
    <label for="plans">Velg gudstjeneste eller møte for å sjekke at sangene som skal brukes er ok <br>(bruk shift eller ctrl for å velge flere):</label><br>
    <select multiple size=12 name="servicelist[]" id="servicelist">

        <?php
            foreach ($services as $service) {
                $sttemp = $servicetypes[$service['relationships']['service_type']['data']['id']];
                $listid = $service['relationships']['service_type']['data']['id']."#".$service['id'];
                echo "<option value=\"".$listid."\">".$service['attributes']['dates']." - ".$sttemp['attributes']['name']." - ".$service['attributes']['series_title']."</option>";
                //echo "<option value=\""$service['relationships']['service_type']['data']['id'].";".$service['id']."\">".$service['attributes']['dates']." - ".$sttemp['attributes']['name']." - ".$service['attributes']['series_title']."</option>";
                $serviceoverview[$service['id']] = $service;
            }
            //find all songsids from songs in selected services
            //<input type="hidden" name="name" value="<?php echo $songlist; ?>">

        ?>
    </select>
    <br><input type="submit" name="submit" value="Sjekk valgte møter">
    </form>
    </td></tr>
    </table>

<?php

    //First verify if one or more song is selected, or one or more services is selected
    if (isset($_POST['songlist']) || isset($_POST['servicelist'])){

        // request is comming from service select
        // if the meeting is selected - we need to extract the songitems for that plan and add them to the songlist
        if(isset($_POST['servicelist'])){
            $songlist = array();
            $arrlist = array();
            $slids = $_POST['servicelist'];
            
            $spotifylistserviceids = $slids; //to be used to generate spotify playlist for service

            //var_dump($slids);
            foreach($slids as $sl){
                //var_export($sl);
                $ids = explode("#", $sl);
                //var_export("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]."/items?include=song");
                //$p = getRaw("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]."/items?include=song");
                $p = $pccon->getRaw("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]."/items?include=arrangement&per_page=100");
                //var_export("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]."/items?include=arrangement");
                //var_dump($p['included']['id']);
                /*
                foreach($p['included'] as $song){
                    $songlist[$song['id']] = $song['id'];
                }
                */
                foreach($p['included'] as $arr){
                    $songid = $arr['relationships']['song']['data']['id'];
                    $songarr[$songid] = $arr['id'];
                    $songlist[$songid] = $songid;
                }

                //$songlist =  array_merge($songlist, $p['included']['id']);
            }
            //var_dump($songlist);

        //request is comming from the song selection list.
        } else {
            $songlist = $_POST['songlist'];
        }
//        $r = getRaw("https://api.planningcenteronline.com/services/v2/songs?per_page=2&offset=4");
///        $r = getRaw("https://api.planningcenteronline.com/services/v2/songs?per_page=20");
        ///var_dump($songlist);
        //var_dump($songoverview);
        $selectedsongs = array();

        //$songlist is an array of songid's select either in song overview or comming from selected services
        foreach($songlist as $songid) {
            $selectedsongs[$songid] = $songoverview[$songid];
        }

        ?>
        <table>

       <tr>
       <th class="songhead">Sang id</th>
       <th class="songhead">Sang Tittel</th>
       <th class="songhead">Arrangment tittel</th>
       <th class="songhead">Tid</th>
       <th class="songhead">BPM</th>
       <th class="songhead">Har lyrics</th>
       <th class="songhead">Har cord chart key satt</th>
       <th class="songhead">Sequence OK?</th>
       <th class="songhead">Versinndeling ok?</th>
       <th class="songhead">Tekstlengde ok?</th>
       <th class="songhead">Sequence og antall verselementer ok?</th>
       <th class="songhead">Fontsize</th>
       <th class="songhead">Spotify links</th>
       </tr>

        <?php

        foreach($selectedsongs as $song) {
            
            if (isset($songarr) && array_key_exists($song['id'], $songarr)) {
                $s = new Song($song['id'], $songarr[$song['id']]);
            } else {
                $s = new Song($song['id'], null);
            }

            //$s = new Song($song['id']);
            $ars = $s->checkSong();
            //var_dump($ars);
            foreach($ars as $ar) {
                ?>
                <tr>
                    <td class="song">
                        <a href="https://services.planningcenteronline.com/songs/<?php echo $song['id']?>" target="_blank"><?php echo $song['id']?></a>
                    </td>
                    <td class="song"><?php echo $s->title ?></td>
                    <td class="song">
                        <a href="https://services.planningcenteronline.com/songs/<?php echo $song['id']."/arrangements/".$ar->id?>" target="_blank"><?php echo $ar->name?></a>
                    </td>
                    <?php 
                        if(array_key_exists("length", $ar->songconfig)) {
                            echo "<td class=\"song\">".$ar->songconfig['length']."</td>";
                        } else {
                            echo "<td class=\"song_error\">mangler</td>";
                        }
                        if(array_key_exists("bpm", $ar->songconfig)) {
                            echo "<td class=\"song\">".$ar->songconfig['bpm']."</td>";
                        } else {
                            echo "<td class=\"song_error\">mangler</td>";
                        }
                        if(array_key_exists("lyrics_enabled", $ar->songconfig)) {
                            echo "<td class=\"song\">".$ar->songconfig['lyrics_enabled']."</td>";
                        } else {
                            echo "<td class=\"song_error\"></td>";
                        }
                        if($ar->songconfig['chord_chart_key'] != "") {
                            echo "<td class=\"song\">".$ar->songconfig['chord_chart_key']."</td>";
                        } else {
                            echo "<td class=\"song_error\">mangler</td>";
                        }

                        if(array_key_exists("sequence_short", $ar->songconfig)) {
                            echo "<td class=\"song\">".$ar->songconfig['sequence_short']."</td>";
                        } else {
                            echo "<td class=\"song_error\">mangler</td>";
                        }

                        if($ar->songconfig['lyricsOK'] == "OK") {
                            echo "<td class=\"song\">".$ar->songconfig['lyricsOK']."</td>";
                        } else {
                            echo "<td class=\"song_error\">".$ar->songconfig['lyricsOK']."</td>";
                        }
                        if($ar->songconfig['lyricsLinesOK'] == "OK") {
                            echo "<td class=\"song\">".$ar->songconfig['lyricsLinesOK']."</td>";
                        } else {
                            echo "<td class=\"song_error\">".$ar->songconfig['lyricsLinesOK']."</td>";
                        }

                        if(array_key_exists("sequenceAndVerse", $ar->songconfig) && strpos($ar->songconfig['sequenceAndVerse'], "OK")) {
                            echo "<td class=\"song\">".$ar->songconfig['sequenceAndVerse']."</td>";
                        } else {
                            echo "<td class=\"song_warning\">".$ar->songconfig['sequenceAndVerse']."</td>";
                        }
                        
                        if(array_key_exists("chord_chart_font_size", $ar->songconfig) && (intval($ar->songconfig['chord_chart_font_size'])>17)) {
                            echo "<td class=\"song\">".$ar->songconfig['chord_chart_font_size']."</td>";
                        } else {
                            echo "<td class=\"song_error\">".$ar->songconfig['chord_chart_font_size']."</td>";
                        }
                        if(!empty($ar->spotifylink)) {
                            echo "<td class=\"song\">";
                            foreach($ar->spotifylink as $link) {
                                echo "<a href=".$link." target=\"_blank\">Spotify</a><br>";
                                array_push($spotifylist, $link);
                            }
                            echo "</td>";

                        } else {
                            echo "<td class=\"song_error\">mangler</td>";
                        }
                    ?>
                </tr>
            <?php
            }
        }
    }
        ?>
        
        </table>
    
    <form method="Post" action=<?php echo $createspotifyurl ?> >
        <?php
//            var_dump($spotifylist);
            unset($_SESSION['spotifylist']);
            unset($_SESSION['spotifylistserviceids']);


            $_SESSION['spotifylist'] = serialize($spotifylist);
            $_SESSION['spotifylistserviceids'] = serialize($spotifylistserviceids);
           
        ?>
    <br><input type="submit" name="submit" value="Generer Spotify playlist">
    </form>


</html>