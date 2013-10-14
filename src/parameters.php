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
        "config" => false,
        "dependencies" => false,
    ),
	"environment" => "dev",
//     "initializers" => array(
//         new ComposerSystemInitializer(),
//         new ComposerSystemInitializer('/path/to/composer.lock'),
//         new DirectorySystemInitializer('/path/to/modules/directory'),
//     ),
);