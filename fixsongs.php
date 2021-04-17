<?php


    require_once 'utils/ClassLoader.php';

    use PCR\model\User;
 
    use PCR\utils\PCCon;
 
 

    $songoverview = array();
    
    $pccon = new PCCon();
    
    $actionurl ="./fixsongs.php";


    include "top.php";

   
    if (User::getUser()->hasAccess(USER::ACCESS_SONG) == false) {
        header("Location: index.php");
    }

    echo "<br><h2>Dette er en rapport for å masseoppdatere metadata på sangarkivet. Her må du vite hva du gjør når du oppdaterer! :-)</H2><br>";
    
    //Get all available songs from PCS song database and put them in session
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


    ?>

    <form method="Post" action=<?php echo $actionurl ?> > 
    <label for="plans">Velg sanger som skal fikses (bruk shift eller ctrl for å velge flere):</label><br>
    <select multiple size=12 name="songlist[]" id="songlist">

        <?php
            foreach ($songs as $song) {
                echo "<option value=\"".$song['id']."\">".$song['attributes']['title']."</option>";
                $songoverview[$song['id']] = $song;
            }
        ?>

        <br><input type="submit" name="submit" value="Hent sanger">
    </select>
    </form>

<?php


    //#############
    //Songid is set after action button "udate songs" has been pushed.
    //use this section to PATCH the updates to all songs
    if ((isset($_POST['fixsongspushed'])) && ($_POST['fixsongspushed'] == "true")) {
    //if (isset($_POST['songid'])) {
        $songids = $_POST['songid'];
        $titles = $_POST['title'];
        //a small check to ensure function is not called by accident
        if($titles == "") return;
        $admins = $_POST['admin'];
        $authors = $_POST['author'];
        $copyrights = $_POST['copyright'];
//        var_dump($_POST['songid']);

        echo "<table>";           
        echo "<tr>";
        echo "<th class=\"songhead\">Sang id</th>";
        echo "<th class=\"songhead\">Sang Tittel</th>";
        echo "<th class=\"songhead\">Rettighetshaver (eks for salmer: <br>Norsk Salmebok 2013: (N13) 56 <br>Salmer 97: (S97) 56 <br>Norsk Salmebok 1985: (N85) 56 <br)</th>";  //admin
        echo "<th class=\"songhead\">Tekstforfatter | komponist | oversetter | arrangør</th>";
        echo "<th class=\"songhead\">Copyright</th>";
        echo "<th class=\"songhead\">API response code</th>";
        echo "</tr>";

        
        for ($i=0; $i < count($songids); $i++) {
            echo "<tr>";
            echo "<td class=\"song\">".$songids[$i]."</td>"; 
            echo "<td class=\"song\">".$titles[$i]."</td>";
            echo "<td class=\"song\">".$admins[$i]."</td>";
            echo "<td class=\"song\">".$authors[$i]."</td>";
            echo "<td class=\"song\">".$copyrights[$i]."</td>";

            $url = "https://api.planningcenteronline.com/services/v2/songs/".$songids[$i];


            $jsonData = [
                'data' => [
                    'attributes' => [
                        'title' => $titles[$i],
                        'admin' => $admins[$i],
                        'author' => $authors[$i],
                        'copyright' => $copyrights[$i]
                    ]
                ]
            ];
            $payload = json_encode($jsonData);
            
            //commented out while testing "new tono report"
            $return = $pccon->patch($url, $payload);

            echo "<td class=\"song\">".$return."</td>";
            echo "</tr>";
        }
        echo "</table>";
        unset($_SESSION['allsongs']);

    //Called after selecting the songs from the dropdownlist with songs
    //this report can also be called from other POST actions. 
    //requires an array of songid's as input called songlist[]
    //requires an
    } elseif (isset($_POST['songlist'])){

        $songlist = $_POST['songlist'];
        ///var_dump($songlist);
        //var_dump($songoverview);
        $selectedsongs = array();       
        foreach($songlist as $songelement) {
            $selectedsongs[$songelement] = $songoverview[$songelement];
        }
        echo "<form method=\"Post\" action=".$actionurl.">";
        echo "<table>";


        echo "<tr>";
        echo "<th class=\"songhead\">Sang id</th>";
        echo "<th class=\"songhead\">link til sang</th>";
        echo "<th class=\"songhead\">Sang Tittel</th>";
        echo "<th class=\"songhead\">Rettighetshaver (eks for salmer: <br>Norsk Salmebok 2013: (N13) 56 <br>Salmer 97: (S97) 56 <br>Norsk Salmebok 1985: (N85) 56 <br)</th>";  //admin
        echo "<th class=\"songhead\">Her må det legges inn 4 bolker skilt med tegnet '|'<br>Tekstforfatter | komponist | oversetter | arrangør</th>";
        echo "<th class=\"songhead\">Copyright</th>";
        echo "</tr>";

        $i=0;
        foreach($selectedsongs as $song) {
            ?>
            <tr>
                <td class="song"><input type="text" name="songid[<?php echo $i ?>]" value="<?php echo $song['id'] ?>" readonly></td>
                <td class="song">
                    <a href="https://services.planningcenteronline.com/songs/<?php echo $song['id']?>" target="_blank">link</a>
                </td>
                <td class="song"><input type="text" name="title[<?php echo $i ?>]" value="<?php echo $song['attributes']['title'] ?>" readonly></td>
                <td class="song"><input type="text" name="admin[<?php echo $i ?>]" value="<?php echo $song['attributes']['admin'] ?>"></td>
                <td class="song"><input type="text" name="author[<?php echo $i ?>]" value="<?php echo $song['attributes']['author'] ?>"></td>
                <td class="song"><input type="text" name="copyright[<?php echo $i ?>]>" value="<?php echo $song['attributes']['copyright'] ?>"></td>
            </tr>
            <?php

            $i++;
        }
        
        
        echo "</table>";
        echo "<input type=\"hidden\" name=\"fixsongspushed\" value=\"true\">";
        echo "<input type=\"submit\" value=\"Oppdater sanger\">";
        echo "</form>";
    }
    
            
            ?>




</html>