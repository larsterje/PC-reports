<?php
     require_once 'utils/ClassLoader.php';

     use PCR\model\User;
     use PCR\model\Song;
     use PCR\utils\SpotifyCon;
     use PCR\utils\PCCon;
     use PCR\utils\Environment;
  
    //$oauthutilspotify = OAuthUtils::getInstance("Spotify");
    //$oauthutilspotify->authenticate();

    $spcon = new SpotifyCon();
    $pccon = new PCCon();

    //require_once "common.php";
    $spotifyinfo = null;
    
    $spotifyinfo = $spcon->getRaw("https://api.spotify.com/v1/me");


    //for each plan in periode
    $songoverview = array();
    $spotifylist = unserialize($_SESSION['spotifylist']);
    $spotifylistserviceids = unserialize($_SESSION['spotifylistserviceids']);
    

    include "top.php";
    echo "<br><h2>Generer Spotify playlist</H2><br>";
    //var_dump($_POST['links']);
    //if(isset($_POST['links'])) {

    if(isset($spotifyinfo) && isset($spotifylist)) {
 
        $listname = "";
        $description = "";
        if(!empty($spotifylistserviceids)) {
            //echo "<br>SerivceIDs: ";
            //var_dump($spotifylistserviceids);
            //echo "<br>";
            $ids = explode("#", $spotifylistserviceids[0]);
            $p = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]);
            $listname = "BM - ". $p['attributes']['sort_date']. " - øveliste";
            $description = "Autogenerert spilleliste fra Planning Center i Bogafjell menighet med sangene for arrangement ".$p['attributes']['sort_date']. " - " .$p['attributes']['series_title'];
        } else {
            $listname = "BM - øveliste - generert: ". date("Y-m-d H:i:s");
            $description = "Autogenerert spilleliste fra Planning Center i Bogafjell menighet.";
        }

        // create playlist
        //echo "<br>Navn på spilleliste: ". $listname . "<br>";

        $jsonData = [
            'name' => $listname,
            'description' => $description,
            "public" => true

        ];
        $payload = json_encode($jsonData);
        $newUrl = "https://api.spotify.com/v1/users/" .$spotifyinfo['id']. "/playlists";
        $postresponse = $spcon->POST($newUrl, $payload);
        //var_dump($postresponse);
        if($postresponse['ResponseCode'] == "201") {
            echo "<br> Spilleliste laget. Navn: ".$listname."<br>";
        } else {
            echo "<br>Feil ved etablering av spilleliste. Feilkode: ". $postresponse['ResponseCode'];
            exit;
        }

        $uris = array();
        foreach ($spotifylist as $track) {
            $split = explode("/", $track);
            $uris[] = "spotify:track:".$split[4];
        }
        $jsonData = [
            //'playlist_id' => $postresponse['ResponseData']['id'],
            'uris' => $uris
        ];
        $payload = json_encode($jsonData);
        //var_dump("<br><br>body: ".$payload. "<br><br>");
                
        $payload = json_encode($jsonData);
  //      var_dump("<br><br>body: ".$payload. "<br><br>");
        $newUrl = "https://api.spotify.com/v1/playlists/".$postresponse['ResponseData']['id']."/tracks";

  //    var_dump("<br>playlisturl: ".$newUrl. "<br><br>");
        $postresponse = $spcon->POST($newUrl, $payload);
        if($postresponse['ResponseCode'] == "201") {
            echo "<br> Sanger lagt til spilleliste. - OK <br>";
        } else {
            echo "<br>Feil oppsto når sangene skulle legges til. Feilkode: ".$postresponse['ResponseCode'];
        }
  //      var_dump($postresponse);

    //test in dev enviromnet
    } else if(isset($spotifylist)) {
        echo "<br><h2>Ikke pålogget spotify</H2><br>";
        $listname = "";
        if(!empty($spotifylistserviceids)) {
            echo "<br>SerivceIDs: ";
            var_dump($spotifylistserviceids);
            echo "<br>";
            $ids = explode("#", $spotifylistserviceids[0]);
            $p = $pccon->getData("https://api.planningcenteronline.com/services/v2/service_types/".$ids[0]."/plans/".$ids[1]);
            $listname = "BM - ". $p['attributes']['sort_date']. " - øveliste";
            $description = "Autogenerert spilleliste fra Planning Center i Bogafjell menighet med sangene for arrangement ".$p['attributes']['sort_date']. " - " .$p['attributes']['series_title'];
        } else {
            $listname = "BM - øveliste - generert: ". date("Y-m-d H:i:s");
            $description = "Autogenerert spilleliste fra Planning Center i Bogafjell menighet.";
        }

        echo "<br>Navn på spilleliste: ". $listname . "<br>";
        $uris = array();
        foreach ($spotifylist as $track) {
            $split = explode("/", $track);
            $uris[] = "spotify:track:".$split[4];
        }
        $jsonData = [
            //'playlist_id' => $postresponse['ResponseData']['id'],
            'uris' => $uris
        ];
        $payload = json_encode($jsonData);
        var_dump("<br><br>body: ".$payload. "<br><br>");
    }



    //getAttachmentLink("https://services.planningcenteronline.com/attachments/94469163");
    //getAttachmentLink("https://login.planningcenteronline.com/login/new?return=Services%2Fattachments%2F94469163");

//    'uris' => [
//        "spotify:track:2R82c7jQq8k0MAkygP6zva", 
 //       "spotify:track:2R82c7jQq8k0MAkygP6zva"
  //  ],

?>
</html>