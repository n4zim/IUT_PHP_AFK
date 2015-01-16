<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Library loading
require_once('app/lib/div/div.php');

// System loading
$classes = array('Config', 'Model', 'Controller', 'Form', 'Helpers', 'Route', 'Layout', 'AFK');
foreach ($classes as $class) {
    require_once 'app/'.$class.'.class.php';
}

// Starting everything up
AFK::getInstance()->router($_SERVER['QUERY_STRING']);