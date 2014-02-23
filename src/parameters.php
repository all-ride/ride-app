<?php

use ride\application\system\init\ComposerSystemInitializer;
use ride\application\system\init\DirectorySystemInitializer;

/**
 * Name of the environment
 * @var string
 */
$environment = "dev";

/**
 * Flag to see if the configuration parameters should be cached
 * @var boolean
 */
$willCacheConfig = false;

//// read environment from ini
//$ini = @parse_ini_file(__DIR__ . '/environment.ini');
//if (isset($ini["environment"])) {
//    $environment = $ini["environment"];
//
//    unset($ini);
//}

//// detect environment based on path
//switch (__DIR__) {
//    case "/path/to/production":
//        $environment = "prod";
//        $willCacheConfig = true;
//
//        break;
//}

/**
 * Parameters for a Ride system
 * @var array
 * @see ride\application\system\System
 */
$parameters = array(
    "cache" => array(
        "config" => $willCacheConfig,
    ),
    "environment" => $environment,
//     "initializers" => array(
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock'),
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
//         new DirectorySystemInitializer(__DIR__ . '/../../modules'),
//     ),
);