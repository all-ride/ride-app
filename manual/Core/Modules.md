## What Is A Module

A module is a directory containing the defined [directory structure](/admin/documentation/manual/page/Core/Directory+Structure).
It adds a certain functionallity to your system.

Everything in Ride is a module, even the core itself.
Modules are a great way to keep your code structured and flexible.

### ride.json

By adding a _ride.json_ file into the root of your module directory, you tell Ride to use this directory as a module.

The following properties are recognized in _ride.json_:

* __level__: Level of your module.
It specifies the order in which your module is accessed in comparisson to other modules.
The order of inheritance, which is used inside different components of Ride, is set through this property.

## Load Modules

Modules are loaded by the system initializers.
One of the responsibility of system initializers is making modules known to Ride.

By default, all Ride modules installed through Composer are automatically detected.


### Add A Module Directory

In _application/config/parameters.php_, you can override the default system initializer.

* Create a directory _modules_ in your project root directory.
* Uncomment the second initializer in _application/config/parameters.php_
* Your _modules_ directory can now contain extra custom modules

Example of the configuration in _application/config/parameters.php_ for an installation with a custom modules directory:

```php
<?php

use ride\application\system\init\ComposerSystemInitializer;

// ...

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
    "initializers" => array(
        new ComposerSystemInitializer(__DIR__ . '/../../composer.lock', __DIR__ . '/../../modules'),
    ),
);
```
