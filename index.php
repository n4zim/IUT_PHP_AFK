<?php
session_start();
require('app/AFK.class.php');

$yoloswag = AFK::getInstance();
$yoloswag->router($_SERVER['QUERY_STRING']);