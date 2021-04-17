
<?php
    require_once 'utils/ClassLoader.php';
    //require_once 'utils/OAuthUtils.php';
    use PCR\utils\OAuthUtils;
    $vendor = "PC";
    error_log( print_r( $_GET, true));
    $code = $_GET["code"];
    $oauthutils = OAuthUtils::getInstance($vendor);
 
    $oauthutils->handleCallback($vendor, $code);
