<?php

/*
 * Bootstrap of the Ride system. 
 * File should be placed in application/src
 */

$autoloader = __DIR__ . '/../../vendor/autoload.php';
$parameters = __DIR__ . '/../config/parameters.php';

// include the Composer autoloader
if (file_exists($autoloader)) {
    include_once $autoloader;
}

// read the parameters
if (file_exists($parameters)) {
    include_once $parameters;
}

if (!isset($parameters) || !is_array($parameters)) {
    $parameters = array();
}