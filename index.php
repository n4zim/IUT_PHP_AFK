<?php
session_start();
require('app/AFK.class.php');
AFK::getInstance()->router($_SERVER['QUERY_STRING']);