<?php
session_start();
require('php/AFK.class.php');

$yoloswag = AFK::getInstance();
$yoloswag->router($_SERVER['QUERY_STRING']);