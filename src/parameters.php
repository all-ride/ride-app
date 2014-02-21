<?php

use ride\application\system\init\ComposerSystemInitializer;
use ride\application\system\init\DirectorySystemInitializer;

/**
 * Parameters for a Ride system
 * @var array
 * @see ride\application\system\System
 */
$parameters = array(
    "cache" => array(
        "config" => false
    ),
    "environment" => "dev",
//     "initializers" => array(
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock'),
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
//         new DirectorySystemInitializer(__DIR__ . '/../../modules'),
//     ),
);