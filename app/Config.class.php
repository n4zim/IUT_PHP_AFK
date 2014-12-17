<?php
/**
 * Configuration class for the website
 **/
class Config {
    public static  $dbInfo = array(
        'driver' => 'mysql:host=db.nerdbox.fr;dbname=afk',
        'username' => 'afk',
        'password' => 'antrhofurryswag2014'
    );

    public static $app = array(
        'hostname' => 'localhost'
    );

    public static $paths = array(
        'views' => 'app/view/'
    );
}