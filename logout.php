<?php
require_once __DIR__ . "/src/config.php";

$config = new AppConfig();

if ($config->isDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$config->logout();
