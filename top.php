<?php

    require_once 'utils/ClassLoader.php';
    
    use PCR\model\User;
    use PCR\utils\Environment;

    if (Environment::getInstance()->isTest()) {

        if (User::getUser()->hasAccess(USER::ACCESS_TEST) == false) {
            header("Location: https://bogafjellkirke.ddns.net/plc-report/");
        }
        // error_reporting(E_ALL);
        //requires XDebug. Må installeres på server
    }

    $user = User::getUser();

?>

<html>
    <head>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1> Planning Center rapporter for Bogafjell kirke</h1>
        <h2> Hei <?php echo $user->getName();?> </h2>

        Gå til startside for <a href="./">startside</a><br>
        Gå til rapport for <a href="responsibilities.php">Ansvarsoversikt i gudstjeneste</a><br>
        Reset session <a href="resetsession.php">Trykk på denne linken hvis det oppstår feil</a><br>
        <br>
        <?php 
        
        //teamid=4438974
        //sangrapporter = 24030414
        if($user->hasAccess(USER::ACCESS_SONG)) {

        ?>

            <h3>Diverse rapporter med begrenset tilgang: </h3>
            Gå til rapport for <a href="tonoreport.php">Generer kvartalsvis Tonorapport</a><br>
            Gå til rapport for <a href="fixsongs.php">Fiks metadata på sangarkiv</a><br>
            Gå til side for å  <a href="checksongs.php">kontrollere sanger i databasen</a><br>
        <?php
        } 
  
         //cellegruppeplanlegging = 24030415
        if($user->hasAccess(USER::ACCESS_CELLGROUP_PLANNING)) {
        ?>
            <br>Gå til side for å  <a href="cellresp.php">Planlegge cellegrupper til arrangement og gudstjenester</a><br>

            <?php
        } 
  
         
        if($user->hasAccess(USER::ACCESS_SEMESTER_PLANNING)) {
        ?>
            <br>Gå til side for å  <a href="plansemester.php">Planlegge semester med arrangement og gudstjenester</a><br>


        <?php
        }
        ?>