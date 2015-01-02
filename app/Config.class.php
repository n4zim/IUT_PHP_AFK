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
        'hostname' => 'localhost'
    );

    public static $listing = array(
        'usersPerPage' => 25
    );

    public static $path = array(
        'views' => 'app/view/',
        'controller' => 'app/controller/',
        'model' => 'app/model/'
    );
}