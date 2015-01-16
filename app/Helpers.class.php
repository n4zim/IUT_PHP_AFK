<?php
class Helpers {
    public static function toFullGender($gender) {
        switch(strtoupper($gender)) {
            case 'M':
                return 'homme';
                break;
            case 'F':
                return 'femme';
                break;
            default:
                return 'autre';
                break; 
        }
    }

    public static function makeUrl($action, $method = null, $args = null, $htmlencode = true) {
        $sep = ($htmlencode) ? '&amp;' : '&';
        $url = Config::$app['baseurl'].'index.php?';
        $url .= 'action='.$action;

        if($method != null) {
            $url .= $sep;
            $url .= 'method='.$method;
        }

        if($args != null) {
            if(is_array($args)) {
                foreach ($args as $key => $value) {
                    $url .= $sep;
                    if(!is_int($key)) {
                        $url .= rawurlencode($key).'=';
                    }
                    
                    $url .= rawurlencode($value);
                }

            } else {
                $url .= $sep.$args;
            }
        }

        return $url;
    }

    public static function notify($title, $message, $type = 'info') {
        $_SESSION['n.title'] = $title;
        $_SESSION['n.message'] = $message;
        $_SESSION['n.type'] = $type;
    }

    public static function unsetNotification() {
        unset($_SESSION['n.title']);
        unset($_SESSION['n.message']);
        unset($_SESSION['n.type']);
    }

    public static function redirect($action, $method = null, $args = null, $message = '') {
        header('Location: '.Helpers::makeUrl($action, $method, $args, false));
        exit($message);
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

    public static function slugify($str) {
        $str = strtr($str, 
          'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        return strtolower(preg_replace('/([^.a-z0-9]+)/i', '-', $str));
    }

    public static function formatSQLDate($date) {
        return date('Y-m-d H:i:s', $date);
    }

    public static function formatDateTime($timestamp) {
        $timestamp = intval($timestamp);
        $str  = Helpers::nameDay($timestamp).' '.date('d', $timestamp).' '.Helpers::nameMonth($timestamp).' '.date('Y', $timestamp);
        $str .= ' à ';
        $str .= date('H:i:s', $timestamp);
        return $str;
    }
}
