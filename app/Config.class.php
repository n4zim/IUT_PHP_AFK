<?php
/**
 * Configuration class for the website
 **/
class Config {
    public static $debug = true;

    public static  $dbInfo = array(
        'driver' => 'mysql:host=sereth.nerdbox.fr;dbname=afk',
        'username' => 'afk',
        'password' => 'antrhofurryswag2014'
    );

    public static $app = array(
        'hostname' => 'afk.nerdbox.fr',
        'mailFrom' => 'CookieCatch AFK <josh@sereth.nerdbox.fr>',
        'baseurl' => '',
        'activityTimeout' => 120
    );

    public static $listing = array(
        'usersPerPage' => 10,
        'eventsPerPage' => 10,
        'eventsOnHomePage' => 3
    );

    public static $path = array(
        'views' => 'app/view/',
        'controller' => 'app/controller/',
        'model' => 'app/model/',
        'pm' => 'user/pm'
    );
}