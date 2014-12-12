<?php
class Helpers {
    public static function processFilename($filename) {
    $server = $_SERVER["SERVER_NAME"];
    $file = dirname($_SERVER['PHP_SELF']);
    
    // rediction locahost/nerdbox.fr/ => nerdbox.fr/ (pour le dev en local)
    if($server == 'localhost') {
        $server = 'nerdbox.fr';
        $file = substr($file, 11);
    }

    $url = 'http://'.$server.$file;
    return preg_replace("/\.\/(.*)/i", "$url/$1", $filename);
    }

    public static function processDBText($text) {
        $text = utf8_encode($text);
        $text = htmlentities($text, ENT_NOQUOTES, "UTF-8");
        $text = htmlspecialchars_decode($text);
        return $text;
    }

    public static function nameMonth($date) {
        $m = date('m', $date);
        $mois = array('janvier', 'f&eacute;vrier', 'mars', 'avril', 
            'mai', 'juin', 'juillet', 'ao&ucirc;t',
            'septembre', 'octobre', 'novembre', 'd&eacute;cembre');
        return $mois[$m - 1];
    }

    public static function nameDay($date) {
        $j = date('w', $date);
        $jour = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
        return $jour[$j];
    }

    public static function detectPodcastMimetype($filename) {

        $mime_types = array(
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/x-wav',
            'wma' => 'audio/x-ms-wma',
            'ogg' => 'audio/ogg',
            'oga' => 'audio/ogg',
            'spx' => 'audio/ogg',
            'flac' => 'audio/flac',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        
        return 'application/octet-stream';
    }
}
