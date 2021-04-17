<?php
    //this is not in use yet. Testing for new functionality

    namespace PCR\api;
    require_once '../utils/ClassLoader.php';

    use PCR\model\ServiceTypes;

    error_log( print_r( $_GET, true));
    var_dump($_GET);
    if(array_key_exists("service_types", $_GET)) {
        
        var_dump((new ServiceTypes())->getServiceTypesAsJson());
        echo "service_types";

    }
    echo "hello";
    
