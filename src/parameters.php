<?php

use pallo\application\system\init\ComposerSystemInitializer;
use pallo\application\system\init\DirectorySystemInitializer;

/**
 * Parameters for a Pallo system
 * @var array
 * @see pallo\application\system\System
 */
$parameters = array(
    "cache" => array(
        "config" => false
    ),
    "environment" => "dev",
//     "initializers" => array(
//         new ComposerSystemInitializer(__DIR__ . '/../../composer.lock'),
//         new DirectorySystemInitializer(__DIR__ . '/../../modules'),
//     ),
);