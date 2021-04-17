<?php

namespace PCR\utils;

use PCR\utils\Environment;
use PCR\utils\OAuthUtils;

class PCCon {

    private $access_token;

    public function __construct() {
        $oauth = OAuthUtils::getInstance("PC");
        $this->access_token = $oauth->getAccessToken();
        //$this->access_token = $access_token;
    }


    private function getResource($access_token, $api_url) {

        $header = array("Authorization: Bearer {$access_token}", "Content-Type: application/json");
        $dns_server = "192.168.0.1";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_DNS_SERVERS => $dns_server,
            CURLOPT_RETURNTRANSFER => true
        ));
        $return = curl_exec($curl);

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($responseCode == "429") {
            var_export("#### Request limit reached ");
            error_log("#### Request limit reached for call: ". $api_url);
        }
        //var_dump($responseCode);
        curl_close($curl);

        //return json_decode($response, true);
        $response = json_decode($return, true);
        //$data = $response['data'];
        //var_dump($data);
        return $response;
    }

      public function getRaw($url) {
        $d = $this->getResource($this->access_token , $url);
        return $d;
    }

    public function getData($url) {

        $d = $this->getResource($this->access_token , $url);
        return $d['data'];          

    }
    //	we can now use the access_token as much as we want to access protected resources


    function getIncluded($url) {
        $d = $this->getResource($this->access_token , $url);
        return $d['included'];
    }

    function patch($url, $data) {
        
        $header = array("Authorization: Bearer {$this->access_token}", "Content-Type: application/json");
        $dns_server = "192.168.0.1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //        curl_setopt($ch, CURLOPT_DNS_SERVERS, "192.168.0.1");
    //    curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $return = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode == "429") {
            var_export("#### Request limit reached #####");
        }
        curl_close($ch);
        $response = json_decode($return, true);

        return $responseCode;

    }
    function POST($url, $data) {
        #global $username, $password;
       
        $header = array("Authorization: Bearer {$this->access_token}", "Content-Type: application/json");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        #curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        #curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //    curl_setopt($ch, CURLOPT_DNS_SERVERS, "192.168.0.1");
    //    curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $return = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode == "429") {
            var_export("#### Request limit reached #####");
        }

    //    var_dump($responseCode);
        curl_close($ch);
        $response = json_decode($return, true);

    //        $data = $response['data'];
    //   $data = $response;
        //return $data;
        $postresponse = array();
        $postresponse['ResponseData'] = $response;
        $postresponse['ResponseCode'] = $responseCode;
        return $postresponse;
    }

    function DELETE($url, $data) {
    #       global $username, $password;
       
        $header = array("Authorization: Bearer {$this->access_token}", "Content-Type: application/json");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        #curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    #        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //    curl_setopt($ch, CURLOPT_DNS_SERVERS, "192.168.0.1");
    //    curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $return = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode == "429") {
            var_export("#### Request limit reached #####");
        }

    //    var_dump($responseCode);
        curl_close($ch);
        $response = json_decode($return, true);

    //        $data = $response['data'];
    //   $data = $response;
        //return $data;
        return $responseCode;

    }
}