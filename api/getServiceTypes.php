<?php

    //this is not in use yet. Testing for new functionality

    namespace PCR\api;
    require_once '../utils/ClassLoader.php';

    use PCR\model\ServiceTypes;
    $serviceTypes = (new ServiceTypes())->getServiceTypesAsJson();
    echo $serviceTypes;
   
