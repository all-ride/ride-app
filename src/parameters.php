<?php

use ride\application\system\init\ComposerSystemInitializer;

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
    "autoloader" => array(
        "enable" => true,
        "prepend" => true,
    ),
    "cache" => array(
        "config" => $willCacheConfig,
    ),
    "environment" => $environment,
//     "initializers" => array(
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
//     ),
);
