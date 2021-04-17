<?php

namespace PCR\model;

use PCR\utils\PCCon;
use PCR\model\Arrangement;

class Song {
    public $songid;
    public $author;
    public $ccli_number;
    public $copyright;
    public $title;
    public $admin;
    //Tekstforfatter | komponist | oversetter | arrangør
    public $composer;
    public $lyrics;
    public $arrangement;
    public $translator;
    public $arrangements;
    private $pccon;


    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct'.$numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }
    
    function __construct6($songid, $author, $ccli_number, $copyright, $title, $admin) {
        $this->songid = $songid;
        $this->ccli_number = $ccli_number;
        $this->copyright = $copyright;
        $this->title = $title;
        $this->admin = $admin;
        $this->arrangements = array();
        $split = explode('|', $author);
        //var_dump($split);

        if(isset($split[0])) { $this->lyrics = $split[0];} 
        if(isset($split[1])) { $this->composer = $split[1];}
        if(isset($split[2])) { $this->translator = $split[2];} 
        if(isset($split[3])) { $this->arrangement = $split[3];} 

    
    }
    function __construct2($songid, $arrid) {
        $this->pccon = new PCCon();
        $s = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$songid);
        $this->songid = $songid;
        $this->ccli_number = $s['attributes']['ccli_number'];
        $this->copyright = $s['attributes']['copyright'];
        $this->title = $s['attributes']['title'];
        $this->admin = $s['attributes']['admin'];
        $this->author = $s['attributes']['author'];
        $this->arrangements = array();
        $split = explode('|', $this->author);
        //var_dump($split);

        if(isset($split[0])) { $this->lyrics = $split[0];} 
        if(isset($split[1])) { $this->composer = $split[1];}
        if(isset($split[2])) { $this->translator = $split[2];} 
        if(isset($split[3])) { $this->arrangement = $split[3];}
        
        if(isset($arrid)) {
            $this->loadarrangement($arrid);
            
        } else {
            $this->loadarrangements();
        }
    }

    function loadarrangements() {
        $ars = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$this->songid."/arrangements");
        //var_dump($ars);
        foreach($ars as $ar) {
            //var_dump($ar);
            $this->arrangements[$ar['id']] = new Arrangement($ar);
            //var_dump($arrangements[$ar['id']]);
            $this->loadarrattachments($ar['id']);
        }

    }

    function loadarrangement($arrid) {
        $ar = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$this->songid."/arrangements/".$arrid);
        $this->arrangements[$ar['id']] = new Arrangement($ar);
        $this->loadarrattachments($ar['id']);
    }

    // load attachments assosiated with this arrangement to find spotify link
    function loadarrattachments($arrid) {
        //Spotify link can be in either the attachment or the keys section. Need to look in both and save all spotify links

        $attachments = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$this->songid."/arrangements/".$arrid."/attachments");
        $this->searchSpotifyLink($attachments, $arrid);

        $keysid = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$this->songid."/arrangements/".$arrid."/keys");
        //some keyids will come as "id":"chord_chart-30064384--"
        // these might need to be filtered out???
        if ($keysid) {
            foreach ($keysid as $keyid) {
                $attachments = $this->pccon->getData("https://api.planningcenteronline.com/services/v2/songs/".$this->songid."/arrangements/".$arrid."/keys/".$keyid['id']."/attachments");
                $this->searchSpotifyLink($attachments, $arrid);
            }
        }

        //https://api.planningcenteronline.com/services/v2/songs/19097293/arrangements/21865185/keys/30064384/attachments

    }

    function searchSpotifyLink($attachments, $arrid) {
        foreach ($attachments as $att) {
            if ( $att['attributes']['filename'] == "Spotify") {
                //$this->arrangements[$arrid]->spotifylink[$att['id']] = $att['attributes']['url'];
                
                $jsonData = [
                    'data' => [
                        'id' => $att['id']
                    ]
                ];
                $payload = json_encode($jsonData);
                $newUrl = "https://api.planningcenteronline.com/services/v2/attachments/".$att['id']."/open";
                $postresponse =$this->pccon->POST($newUrl, $payload);
                //var_dump($postresponse);

                $this->arrangements[$arrid]->spotifylink[$att['id']] = $postresponse['ResponseData']['data']['attributes']['attachment_url'];

            }
        }
    }


    function checkSong() {
        foreach($this->arrangements as $ar) {
            if($ar->attributes['bpm']) $ar->songconfig['bpm'] = $ar->attributes['bpm'];
            if($ar->attributes['length']) $ar->songconfig['length'] = $ar->attributes['length'];
            if($ar->attributes['has_chord_chart']) $ar->songconfig['has_chord_chart'] = $ar->attributes['has_chord_chart'];
            $ar->songconfig['chord_chart_key'] = $ar->attributes['chord_chart_key'];
            if($ar->attributes['lyrics_enabled']) $ar->songconfig['lyrics_enabled'] = $ar->attributes['lyrics_enabled'];
            if($ar->attributes['sequence_short']) $ar->songconfig['sequence_short'] = implode(" ", $ar->attributes['sequence_short']);

            if($ar->attributes['chord_chart_font_size']) $ar->songconfig['chord_chart_font_size'] = $ar->attributes['chord_chart_font_size'];
            $this->checklyrics($ar);
        }
        return $this->arrangements;
    }
    function checklyrics($ar) {
        $lyrics = $ar->attributes['lyrics'];
        $lyrsplit = explode("\n\n", $lyrics);
        //$sequence = $ar->songconfig['sequence_short'];
        $sequence = $ar->attributes['sequence_short'];
        //var_dump($sequence);
        $sequenceUnique = array_unique($sequence);
        //check if there are more types of elements (verse, chorus etc) than the actuall segments in $lyrsplit
        if(sizeof($sequenceUnique) < sizeof($lyrsplit)) {
            //$ar->songconfig['sequenceAndVerse'] = "Check of sequence and elements do not match. Verify song setup.<br>Number of sequence elemenets: ".sizeof($sequenceUnique)." number of verse elements: ".sizeof($lyrsplit);
            $ar->songconfig['sequenceAndVerse'] = "Sjekk av antall sekvenselementer(sequence) og antall sangelementer (vers, ref, intro osv) stemmer ikke. Sjekk sangoppsettet.<br>Antall unike sekvenselement: ".sizeof($sequenceUnique)." Antall unike verselement: ".sizeof($lyrsplit);
        } else { 
            $ar->songconfig['sequenceAndVerse'] = " OK. Antall sekvenselement: ".sizeof($sequenceUnique)." Antall verselement: ".sizeof($lyrsplit);
        }
        $ar->songconfig['lyricsOK'] = "OK";
        $ar->songconfig['lyricsLinesOK'] = "OK";
        $lyricscheck=array();
        $i=0;
        foreach($lyrsplit as $section) {
            $lines = explode("\n", $section);

            if(isset($lines[0]) && preg_match("/^(verse?|v|chorus|c|refreng|bridge|bro|prechorus|instrumental|intro|outro|vamp|breakdown|ending|interlude|tag|misc)\s*(\d*):?$/i", $lines[0])) {
                //if(preg_match("/^(verse?|v|chorus|c|refreng|bridge|bro|prechorus|instrumental|intro|outro|vamp|breakdown|ending|interlude|tag|misc)\s*(\d*):?$/i", $lines[0])) {
                    //echo "section ok :".$lines[0].PHP_EOL;
                    $lyricscheck[$i++] = "Section ok: ".$lines[0];

                //}

            } else {
                //echo "error in lyrics\n";
                $lyricscheck[$i++] = "!!Error: ".$lines[0];
                $ar->songconfig['lyricsOK'] = FALSE;
            }
            foreach($lines as $line) {
                if (strlen($line) > 50) {
                    //echo "Error - line to long\n";
                    $ar->songconfig['lyricsLinesOK'] = "Lengste linje er: ".strlen($line). "<br> Maks linjelengde er 50 tegn";
                }
            }
            #echo "\n###############\n";
            #echo $section.PHP_EOL;
            #echo "###############\n";

        }
        if(!($ar->songconfig['lyricsOK'])) {
            $ar->songconfig['lyricsOK'] = implode("<br>",$lyricscheck);
            //var_dump($lyricscheck);
        }

        //verse_marker_pattern = re.compile('^(verse?|v|chorus|c|refreng|bridge|bro|prechorus|instrumental|intro|outro|vamp|breakdown|ending|interlude|tag|misc)\s*(\d*):?$',re.IGNORECASE)
        //# create regex for an END marker.  content after this marker will be ignored
        /*end_marker_pattern = re.compile('^{(<.*?>)?END(<.*?>)?}$') */

    }

}

