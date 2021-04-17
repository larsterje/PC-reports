<?php

namespace PCR\utils;

class Environment {

    private $environment;
    private $pc_client;
    private $pc_secret;
    private $spotify_client;
    private $spotify_secret;

    private static $instance;

    public function __construct() {
        self::loadData();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Environment();
        }

        return self::$instance;
    }

    private function loadData( ) {

        $contents = file_get_contents(__DIR__ . '/../credentials.json');
        $credentials = json_decode($contents, true);
        
        $this->environment = $credentials['environment'];
    
        if ($this->environment=="PROD") {
            $this->pc_client = $credentials['clientid'];
            $this->pc_secret = $credentials['secret'];
            $this->spotify_client = $credentials['clientid_spotify'];
            $this->spotify_secret = $credentials['secret_spotify'];
    
        }else if ($this->environment=="TEST"){
            $this->pc_client = $credentials['clientid_test'];
            $this->pc_secret = $credentials['secret_test'];
            $this->spotify_client = $credentials['clientid_test_spotify'];
            $this->spotify_secret = $credentials['secret_test_spotify'];
        }
    

    }

    public function isTest() {
        return $this->getEnvironment() == "TEST";
    }

    public function getEnvironment() {
        return $this->environment;
    }

    public function getPcClientId() {
        return $this->pc_client;
    }

    public function getPcSecret() {
        return $this->pc_secret;
    }
    public function getSpotifyClientId() {
        return $this->spotify_client;
    }

    public function getSpotifySecret() {
        return $this->spotify_secret;
    }


}