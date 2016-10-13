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

/**
 * Path to the system initialization cache file
 * @var string
 */
$systemCacheFile = null;

//// detect environment based on path
//switch (__DIR__) {
//    case "/path/to/production/application/config":
//        $environment = "prod";
//        $willCacheConfig = true;
//        $systemCacheFile = __DIR__ . '/../data/cache/' . $environment . '/system.php';
//
//        break;
//}

/**
 * Extra directory for custom modules. The autoloader needs to be enabled for
 * this.
 * @var string
 */
$modulesDirectory = null;
// $modulesDirectory =  __DIR__ . '/../../modules';

/**
 * System initializer through composer
 * @var ride\application\system\init\ComposerSystemInitializer
 */
$composerInitializer = new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', $modulesDirectory);
$composerInitializer->setCacheFile($systemCacheFile);

/**
 * System parameters for Ride
 * @var array
 * @see ride\application\system\System
 */
$parameters = array(
    "autoloader" => array(
        // enable the autoloader of Ride
        "enable" => true,
        // add the directories of the include_path php.ini setting
        "include_path" => false,
        // prepend the autoloader before other registered autoloaders
        "prepend" => true,
    ),
    "cache" => array(
        // cache the configuration parameters
        "config" => $willCacheConfig,
    ),
    // name of the running environment
    "environment" => $environment,
    // system initializers to run
    "initializers" => array(
        $composerInitializer,
    ),
);
