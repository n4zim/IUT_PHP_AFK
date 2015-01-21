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

    public static function formatPM($text) {
        // todo : parse markdown
        return nl2br($text);
    }

    public static function sendMail($to, $title, $html, $textcontent = null) {
        $from = Config::$app['mailFrom'];

        $limite = "_----------=_parties_".md5(uniqid (rand()));

        $header  = "Reply-to: ".$from."\n";
        $header .= "From: ".$from."\n";
        $header .= "X-Sender: <".Config::$app['hostname'].">\n";
        $header .= "X-Mailer: PHP\n";
        $header .= "X-auth-smtp-user: ".$from." \n";
        $header .= "X-abuse-contact: ".$from." \n";
        $header .= "Date: ".date("D, j M Y G:i:s O")."\n";
        $header .= "MIME-Version: 1.0\n";
        $header .= "Content-Type: multipart/alternative; boundary=\"".$limite."\"";

        $message = "";

        $message .= "--".$limite."\n";
        $message .= 'Content-Type: text/plain; charset=utf-8' . "\n\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= (isset($textcontent) ? $textcontent : strip_tags($html));

        $message .= "\n\n--".$limite."\n";
        $message .= 'Content-Type: text/html; charset=utf-8' . "\n\n";
        $message .= "Content-Transfer-Encoding: 8bit;\n\n";
        $message .= <<<EOT
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="http://afk.nerdbox.fr/assets/css/style.css"/>
(( head ))
</head>
<body>
EOT;
        $message .= $html.'<body></html>';

        $message .= "\n--".$limite."--";

        mail($to, $title, $message, $header);
    }
}
