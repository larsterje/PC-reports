<?php
     require_once 'utils/ClassLoader.php';

    use PCR\model\User;
    use PCR\model\Song;
    use PCR\utils\PCCon;
    use PCR\utils\Environment;
    use PCR\model\ServiceTypes; 

    $songoverview = array();
     
    $pccon = new PCCon();

    $actionurl = "./postsemester.php";

    include "top.php";

    if (User::getUser()->hasAccess(USER::ACCESS_SEMESTER_PLANNING) == false) {
        header("Location: index.php");
    }


    //$serviceTypes = (new ServiceTypes())->getServiceTypesAsJson();
    //$serviceTypes = (new ServiceTypes())->getServiceTypes();
    //echo $serviceTypes;
    ?>
    <br>

    <h2>Semesterplanlegging</h2>
    <form method="Post" id="myForm" action=<?php echo $actionurl?> > 
        <table id=semesterplanning>
                <tr>
                    <th>Dato</th>
                    <th>Møtestart</th>
                    <th>Møteslutt</th>
                    <th>Møtetype</th>
                    <th>Møtemal</th>
                    <th>Serie/Type (eks Gudstjeneset med nattverd)</th>
                    <th>Tittel (eks 20. søndag i treenigheten)</th>
                </tr>
        </table>

        <input type=hidden name=createsemesterpushed value=true>
        
    <br>
    </form>
    <input type="button" onclick="submitForm()" value="Send til PlanningCenter">
    <button onclick="addRowToTable()">Legg til rad</button>

    <script>
        
        //var servicetypes = null;
        
        const servicetypes = <?php echo (new ServiceTypes())->getServiceTypesAsJson(); ?>;
        var rowIndex = 0;
       // createForm();
        addRowToTable();


        function submitForm() {
            document.getElementById("myForm").submit();
        } 
/*
        function createForm() {
            var semesterForm = document.createElement("form");
            semesterForm.setAttribute("method", "post");
            semesterForm.setAttribute("action", "./postsemester.php");
            let semesterTable = document.createElement("table");
            semesterTable.id = "semesterplanning";
            semesterForm.appendChild(semesterTable);
        }
        */
        //async function loadServiceTypes() {
        function loadServiceTypes() {
            //const result = await fetch("api/getServiceTypes.php", {headers: {'Content-Type': 'application/json'}});
            //servicetypes = await result.json();
            //console.log(servicetypes);
            //console.log(servicetypes2);
            //document.getElementById("demo").innerText = json;
            
            const x = document.getElementById("serviceTypeSelector" + rowIndex);
            for(let types of servicetypes) {
                var option = document.createElement("option");
                option.text = types['name'];
                option.value = types['id'];
                x.add(option);
            }
 
        }
  
        function loadTemplates(val, selectRowIndex) {
            //console.log(val);
            //find index of selected servicetype
            var index = servicetypes.findIndex(x => x.id === val );
            var templates = servicetypes[index]['templates']
            //console.log(index);
//            alert("The input value has changed. The new value is: " + index);
            
            //delete all existing templates previous added to the select
            var options = document.querySelectorAll('#templateSelector' + selectRowIndex + ' option');
            options.forEach(o => o.remove());
            
            //add new options to the select based on servicetype selected
            //console.log("templateSelector"+selectRowIndex);
            var x = document.getElementById("templateSelector" + selectRowIndex);
            x.disabled=false;

            for(let temps of templates) {
                var option = document.createElement("option");
                option.text = temps['name'];
                option.value = temps['id'];
                x.add(option);
            }


        }

        function updateDefaultText(selectRowIndex) {
            console.log("selected row: "  + selectRowIndex);
            let serviceType = document.getElementById("serviceTypeSelector" + selectRowIndex);
            let template = document.getElementById("templateSelector" + selectRowIndex);
            console.log(serviceType.value);
            console.log(template.value);
            //console.log(serviceType.innerText);
            //console.log(template.innerText);

            //Gudstjeneste and REFILL
            if(serviceType.value == 1031248 && template.value == 47816229) {
                let serviceSeries = document.getElementById("serviceSeries" + selectRowIndex);
                serviceSeries.value = template.querySelector("option[value='" + template.value + "']").innerText;
                let serviceStartTime = document.getElementById("serviceTime" + selectRowIndex);
                serviceStartTime.value = "20:00:00";
                let serviceEndTime = document.getElementById("serviceEndTime" + selectRowIndex);
                serviceEndTime.value = "21:30:00";
            } 
            //Gudstjeneste
            else if(serviceType.value == 1031248) {
                let serviceSeries = document.getElementById("serviceSeries" + selectRowIndex);
                serviceSeries.value = template.querySelector("option[value='" + template.value + "']").innerText;
                let serviceStartTime = document.getElementById("serviceTime" + selectRowIndex);
                serviceStartTime.value = "11:00:00";
                let serviceEndTime = document.getElementById("serviceEndTime" + selectRowIndex);
                serviceEndTime.value = "12:30:00";
            } 
            //Greenhouse
            else if(serviceType.value == 1029117) {
                let serviceSeries = document.getElementById("serviceSeries" + selectRowIndex);
                serviceSeries.value = template.querySelector("option[value='" + template.value + "']").innerText;
                let serviceStartTime = document.getElementById("serviceTime" + selectRowIndex);
                serviceStartTime.value = "18:30:00";
                let serviceEndTime = document.getElementById("serviceEndTime" + selectRowIndex);
                serviceEndTime.value = "19:30:00";
            } 
            
            else {
                let serviceSeries = document.getElementById("serviceSeries" + selectRowIndex);
                serviceSeries.value = template.querySelector("option[value='" + template.value + "']").innerText;
            }


        }

        function checkTemplateType(val, selectRowIndex) {
            updateDefaultText(selectRowIndex);
        }



        function fixDateOnNewRow() {
            var tmpIndex = rowIndex-1;
            let tmp = "serviceDate" + (tmpIndex);
            //console.log(tmp);
            let newDate = Date();

            let prevDate = document.getElementById(tmp);
            //console.log(prevDate);
            if (prevDate != null) {

                let newRowDate = document.getElementById("serviceDate"+rowIndex);
                //console.log(newRowDate);
                newDate = new Date(prevDate.value);
                newDate.setDate(newDate.getDate() + 7);
                newRowDate.valueAsDate = newDate;
            } 
        }



        /**
         * called from push button event
         */
        function addRowToTable() {
            const thisRowIndex = rowIndex;
            
            var table = document.getElementById("semesterplanning");
            var row = table.insertRow(-1);
            var dateCell = row.insertCell(0);
            var timeCell = row.insertCell(1);
            var timeEndCell = row.insertCell(2);
            var serviceTypeCell = row.insertCell(3);
            var serviceTemplateCell = row.insertCell(4);
            var serviceSeries = row.insertCell(5);
            var serviceTitle = row.insertCell(6);

            //var serviceTeamCell = row.insertCell(7);

            var selectListServiceType = document.createElement("select");
            selectListServiceType.setAttribute("id", "serviceTypeSelector"+rowIndex);
            selectListServiceType.name = "serviceTypeSelector["+rowIndex+"]";
            selectListServiceType.addEventListener("change", () => loadTemplates(selectListServiceType.value, thisRowIndex));
            var optionServiceType = document.createElement("option");
            optionServiceType.text = "Velg møte";
            optionServiceType.value = 00;
            selectListServiceType.add(optionServiceType);
            serviceTypeCell.appendChild(selectListServiceType);

            var selectListTemplateSelector = document.createElement("select");
            selectListTemplateSelector.setAttribute("id", "templateSelector"+rowIndex);
            selectListTemplateSelector.name = "templateSelector["+rowIndex+"]";
            selectListTemplateSelector.addEventListener("change", () => checkTemplateType(selectListTemplateSelector.value, thisRowIndex));
            var optionTemplateSelector = document.createElement("option");
            optionTemplateSelector.text = "Velg møtemal";
            optionTemplateSelector.value = 00;
            //optionTemplateSelector.disabled = true;
            selectListTemplateSelector.disabled = true;
            selectListTemplateSelector.add(optionTemplateSelector);
            serviceTemplateCell.appendChild(selectListTemplateSelector);

            /*
            var selectListTeamSelector = document.createElement("select");
            selectListTeamSelector.setAttribute("id", "templateSelector"+rowIndex);
            selectListTeamSelector.name = "templateSelector["+rowIndex+"]";
            selectListTeamSelector.addEventListener("change", () => checkTemplateType(selectListTeamSelector.value));
            var optionTeamSelector = document.createElement("option");
            optionTeamSelector.text = "Velg møtemal";
            optionTeamSelector.value = 00;
            selectListTeamSelector.disabled = true;
            selectListTeamSelector.add(optionTeamSelector);
            serviceTeamCell.appendChild(selectListTeamSelector);
*/

            var dateInputSelector = document.createElement("input");
            dateInputSelector.type = "date";
            dateInputSelector.setAttribute("id", "serviceDate"+ rowIndex);
            dateInputSelector.name = "serviceDate["+ rowIndex+"]";
//            dateInputSelector.setAttribute("value", "2021-04-12");
            dateInputSelector.valueAsDate = new Date();
            dateCell.appendChild(dateInputSelector);


            var timeInputSelector = document.createElement("input");
            timeInputSelector.type = "time";
            timeInputSelector.setAttribute("id", "serviceTime"+ rowIndex);
            timeInputSelector.name =  "serviceTime["+rowIndex+"]";
            timeInputSelector.setAttribute("value", "11:00:00");
            
            timeCell.appendChild(timeInputSelector);

            
            var timeEndInputSelector = document.createElement("input");
            timeEndInputSelector.type = "time";
            timeEndInputSelector.setAttribute("id", "serviceEndTime"+ rowIndex);
            timeEndInputSelector.name =  "serviceEndTime["+rowIndex+"]";
            timeEndInputSelector.setAttribute("value", "12:30:00");
            
            timeEndCell.appendChild(timeEndInputSelector);

            var seriesInput = document.createElement("input");
            seriesInput.setAttribute("type", "text");
            seriesInput.setAttribute("id", "serviceSeries"+ rowIndex);
            seriesInput.name = "serviceSeries["+ rowIndex+"]";
            seriesInput.style = "width: 100%";
            //dateInputSelector.setAttribute("value", "2021-04-12");
            serviceSeries.appendChild(seriesInput);

            var titleInput = document.createElement("input");
            titleInput.setAttribute("type", "text");
            titleInput.setAttribute("id", "serviceTitle"+ rowIndex);
            titleInput.name = "serviceTitle["+rowIndex+"]";
            titleInput.style = "width: 100%";
            //dateInputSelector.setAttribute("value", "2021-04-12");
            serviceTitle.appendChild(titleInput);

            fixDateOnNewRow();
            loadServiceTypes();
            rowIndex++;
            //serviceTypeCell.innerHTML = "NEW CELL3";
        }

    </script>
    

