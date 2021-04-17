<?php
    require_once 'utils/ClassLoader.php';

    use PCR\model\SemesterPlan;
    use PCR\model\User;
    use PCR\utils\PCCon;

    $songoverview = array();
     
    $pccon = new PCCon();
    $actionurl ="./plansemester.php";

    include "top.php";

    if (User::getUser()->hasAccess(USER::ACCESS_SONG) == false) {
        header("Location: index.php");
    }
?>

<?php

    $semesterPlanning = new SemesterPlan();

?>

<h2>Nye planer lagt til Planning Center</h2>

<table>
    <tr>
        <th>SerivceId</th>
        <th>Dato</th>
        <th>Klokkeslett</th>
        <th>Serie</th>
        <th>Tittel</th>
        <th>Svar fra PC - Lag møte</th>
        <th>Svar fra PC - Legg til tidpunkt</th>
        <th>Svar fra PC - Legg til serie og tittel</th>
    </tr>
    
<?php

    if (isset($_POST['createsemesterpushed']) && $_POST['createsemesterpushed'] == "true")  {

        $planids = null;
        $respCreatePlan = null;
        $respPlanTime = null;
        $respTitle = null;


        for ($i=0; $i<count($_POST['serviceTypeSelector']); $i++ ) {
            $respCreatePlan  = $semesterPlanning->postCreatePlanFromTemplate($_POST['serviceTypeSelector'][$i], $_POST["templateSelector"][$i]);
            $planids = $respCreatePlan['planId'];

            if ($respCreatePlan['ResponseCode'] == 200 && $planids != null) {
                                             
                $respPlanTime = $semesterPlanning->postCreatePlanTime($planids, $_POST['serviceTypeSelector'][$i], $_POST['serviceDate'][$i], $_POST['serviceTime'][$i], $_POST['serviceEndTime'][$i]);
            }

            //echo("<br><br>Patch plan<br><br>");
            //var_dump($respPlanTime);
            if ($respPlanTime['ResponseCode'] == 201 && $planids  != null) {

                $respTitle = $semesterPlanning->patchPlanTitle($planids, $_POST['serviceTypeSelector'][$i], $_POST['serviceTitle'][$i], $_POST['serviceSeries'][$i]);

            }
            $planUrl = "https://services.planningcenteronline.com/plans/" . $planids;
            ?>
            <tr>
                <td><a href="<?php echo $planUrl?>" target="_blank"><?php echo $planids?></a></td>
                <td><?php echo $_POST['serviceDate'][$i]?> </td>
                <td><?php echo $_POST['serviceTime'][$i]?> </td>
                <td><?php echo $_POST['serviceSeries'][$i]?> </td>
                <td><?php echo $_POST['serviceTitle'][$i]?> </td>
                <td>
                <?php 
                    if($respCreatePlan['ResponseCode'] == 200) {
                        echo "OK";
                    } else {
                        echo "feilet - Feilkode: " . $respCreatePlan['ResponseCode'];
                    }?> 
                </td>
                <td>
                <?php 
                    if($respPlanTime['ResponseCode'] == 201) {
                        echo "OK";
                    } else {
                        echo "feilet - Feilkode: " . $respPlanTime['ResponseCode'];
                    }?> 
                </td>
                <td>
                <?php 
                    if($respTitle['ResponseCode'] == 200) {
                        echo "OK";
                    } else {
                        echo "feilet - Feilkode: " . $respTitle['ResponseCode'];
                    }?> 
                </td>
            </tr>
            <?php
        }
    }
?>

</table>