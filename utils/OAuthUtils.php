<?php

namespace PCR\utils;

use PCR\utils\Environment;
use Exception;

class OAuthUtils {

    private static $instances = [];

    private $access_token;
    private $callback_uri;
    private $home_location;
    private $client_id;
    private $client_secret;
    private $authorize_url;
    private $token_url;
    private $session_access_token_name;
    private $scope;

    //private $api_url_me = "https://api.planningcenteronline.com/people/v2/me";
    
    //private static $instance = null;
    
    public function __construct($vendor) {
        $this->setEnvironment($vendor);
        //get the AccessToken from PC
        //$this->checkForAccessToken();

    }

    
    public static function getInstance($vendor) {
        if (array_key_exists($vendor, self::$instances) == false) {
            //error_log("Self instance is null, creating new OAuthUtil()");
            self::$instances[$vendor] = new OAuthUtils($vendor);
        }

        return self::$instances[$vendor];
    }


    public function authenticate() {
         
        if(session_status() != 2){
            session_start();
        }

        if ($this->access_token==null && !array_key_exists($this->session_access_token_name, $_SESSION)) {
            error_log("Accesstoken is null - getting new accesstoken");
            $this->getAuthorizationCode();
            
            exit();
            //return false;
        } else if (array_key_exists($this->session_access_token_name, $_SESSION)) {
            $this->access_token = $_SESSION[$this->session_access_token_name];
            //error_log("Authenticate - accessToken retrievd from Session: " . $this->access_token);
        }
        return $this->access_token;
    }
    /**
     * get the accesstoken or kicks of creating the accesstoken
     */
    public function getAccessToken() {
        return $this->authenticate();
    }

        /**
     * Step A 
     */
    private function getAuthorizationCode() {
        $authorization_redirect_url = $this->authorize_url . "?response_type=code&client_id=" . $this->client_id . "&redirect_uri=" . $this->callback_uri . "&scope=" . $this->scope;
        header("Location: " . $authorization_redirect_url);
    }


    public function handleCallback($vendor, $authorization_code) {
        session_start();
        $this->access_token = $this->createAccessToken($authorization_code);
        $_SESSION[$this->session_access_token_name] = $this->access_token;
        
        
        //$_SESSION['access_token'] = $this->access_token;

        error_log("AccessToken from GET authoriation code: ".$this->access_token."<br>");
//        var_dump($access_token);
        //$resource = getResource($access_token, $api_url_me);
        
        header($this->home_location);
        exit();

    }


   //	step I, J - turn the authorization code into an access token, etc.
   private function createAccessToken($authorization_code) {

        error_log("Authorization: " . "$this->client_id:$this->client_secret");

        $authorization = base64_encode("$this->client_id:$this->client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$this->callback_uri";


        //var_export("Content: ".$content);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        ));
        $response = curl_exec($curl);

        if ($response === false || curl_errno($curl)) {
            echo "Failed";
            echo curl_error($curl);
            echo "Failed";

            curl_close($curl);

            throw new Exception('curl failed');
        }
        
        curl_close($curl);

        $json = json_decode($response, true);

        if ($json === false) {
            throw new Exception('json_decode returned false');
        } elseif (array_key_exists('error', $json)) {
            echo "Error:<br />";
            echo $authorization_code;
            echo $response;
        }

        $access_token = $json['access_token'];

        error_log("Access token recevied by createToken(): " . $access_token);
        return $access_token;
    }


    /**
     * Sets the Oauth environment varialbles based on which environment we are in.
     */
    private function setEnvironment($vendor) {
    
        $env = new Environment();
        $env->getInstance();
        if ($env->getEnvironment() =="PROD") {
            if ($vendor == "PC") {
                ///var_export("IS production");
                $this->callback_uri = "https://bogafjellkirke.ddns.net/plc-report/authcallbackpc.php";
                $this->home_location = "Location: /plc-report/";
                $this->client_id = $env->getPcClientId();
                $this->client_secret = $env->getPcSecret();

                $this->authorize_url = "https://api.planningcenteronline.com/oauth/authorize";
                $this->token_url = "https://api.planningcenteronline.com/oauth/token";
                $this->session_access_token_name = "pc_access_token";
                $this->scope = "people%20services";
            } else if ($vendor == "Spotify") {
                //var_export("Test environment");
                $this->callback_uri = "https://bogafjellkirke.ddns.net/plc-report/authcallbackspotify.php";
                $this->home_location = "Location: /plc-report/playlistgenerator.php";
                $this->client_id = $env->getSpotifyClientId();
                $this->client_secret = $env->getSpotifySecret();

                $this->authorize_url = "https://accounts.spotify.com/authorize";
                $this->token_url = "https://accounts.spotify.com/api/token";
                $this->session_access_token_name = "spotify_access_token";
                $this->scope = "user-read-private%20playlist-modify-public%20playlist-modify-private";
            }
        
        
        } elseif ($env->getEnvironment() =="TEST")  {
            if ($vendor == "PC") {
                //var_export("Test environment");
                $this->callback_uri = "https://bogafjellkirke.ddns.net/plc-report-test/authcallbackpc.php";
                $this->home_location = "Location: /plc-report-test/";
                $this->client_id = $env->getPcClientId();
                $this->client_secret = $env->getPcSecret();

                $this->authorize_url = "https://api.planningcenteronline.com/oauth/authorize";
                $this->token_url = "https://api.planningcenteronline.com/oauth/token";
                $this->session_access_token_name = "pc_access_token";
                $this->scope = "people%20services";
            } else if ($vendor == "Spotify") {
                //var_export("Test environment");
                $this->callback_uri = "https://bogafjellkirke.ddns.net/plc-report-test/authcallbackspotify.php";
                $this->home_location = "Location: /plc-report-test/playlistgenerator.php";
                $this->client_id = $env->getSpotifyClientId();
                $this->client_secret = $env->getSpotifySecret();

                $this->authorize_url = "https://accounts.spotify.com/authorize";
                $this->token_url = "https://accounts.spotify.com/api/token";
                $this->session_access_token_name = "spotify_access_token";
                $this->scope = "user-read-private%20playlist-modify-public%20playlist-modify-private";
            }


        }

    }
    




}