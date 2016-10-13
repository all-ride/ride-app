# Ride: Application

Base integration of the Ride framework libraries.

This module glues the needed libraries together to get a system with the following features:

* [Flexible configuration](manual/Core/Parameters.md)
* [Dependency injection](manual/Core/Dependencies.md)
* [Events](manual/Core/Events.md)
* [Logging](manual/Core/Logging.md)
* [Modules](manual/Core/Modules.md)
* Host system abstraction 

This module is the starting point of the Ride framework. 
Below this module are libraries, above this module are Ride implementations.

## What's In This Application

### Libraries

- [ride/lib-cache](https://github.com/all-ride/ride-lib-cache)
- [ride/lib-common](https://github.com/all-ride/ride-lib-common)
- [ride/lib-dependency](https://github.com/all-ride/ride-lib-dependency)
- [ride/lib-event](https://github.com/all-ride/ride-lib-event)
- [ride/lib-log](https://github.com/all-ride/ride-lib-log)
- [ride/lib-reflection](https://github.com/all-ride/ride-lib-reflection)
- [ride/lib-system](https://github.com/all-ride/ride-lib-system)

### Application

The _Application_ interface is to run a service in the system.
It's only method is _service_ and is used by the CLI and web modules.

### System

The _System_ class is an extension of the same class in the system library.
It adds access to the Ride framework and makes the following components available:

* System and [configuration parameters](manual/Core/Parameters.md)
* [Dependency injector](manual/Core/Dependencies.md)
* [File browser](manual/Core/File+System.md)
* [Log](manual/Core/Log.mp)
* Autoloader

### SystemInitializer

The _SystemInitializer_ interface is used to initialize (or boot) the system.

One of the tasks of the system initializer is to add all modules to the file browser and, optionally, the autoloader.
Read more about this in [manual/Core/Modules.md](manual/Core/Modules.md).

You can add multiple system initializers to your system parameters located in _application/config/parameters.php_.
If none is provided, the _ComposerSystemInitializer_ is used.

#### DirectorySystemInitializer

The _DirectorySystemInitializer_ class is used to add a custom module directory to the system.
All modules inside the provided directory will be added to the file browser and all sources to the autoloader.

#### ComposerSystemInitializer

The _ComposerSystemInitializer_ class is used to add all modules installed through Composer to the system.
You can set a custom modules directory to add modules which are outside of the _vendor_ directory. 

## Parameters

* __log.action__: Action level of the log. 0 to disable, 1 to log requests where an error has occured, 2 for warnings, 4 for information messages and 8 for debug messages.
* __log.file__: Path to the log file.
* __log.level__: Level of messages to log. 0 for everything, 1 for errors, 2 for warnings, ...
* __log.truncate__: Maximum size for the log file in kb.
* __system.application__: Dependency id of the default application
* __system.binary.%command%__: Full path to a binary command
* __system.cache.dependencies__: Flag to see if the dependencies should be cached
* __system.cache.directory__: Path to the directory of the application file cache pool
* __system.cache.event__: Path to the file name of the event cache
* __system.cache.file__: Path to the file of the application memory cache pool
* __system.directory.user__: Path to the directory of user content/uploads
* __system.event.loader__: Dependency id of the event loader
* __system.event.listener.default__: Dependency id of the event listener IO in use
* __system.event.listener.cache__: Dependency id of the cached event listener IO
* __system.name__: Name of the system, defaults to Ride
* __system.secret__: Secret key of the system for encryption and security
* __system.timezone__: Timezone for this application

## Related Modules 

- [ride/app-database](https://github.com/all-ride/ride-app-database)
- [ride/app-i18n](https://github.com/all-ride/ride-app-i18n)
- [ride/app-image](https://github.com/all-ride/ride-app-image)
- [ride/app-mail](https://github.com/all-ride/ride-app-mail)
- [ride/app-media](https://github.com/all-ride/ride-app-media)
- [ride/app-orm](https://github.com/all-ride/ride-app-orm)
- [ride/app-template](https://github.com/all-ride/ride-app-template)
- [ride/app-validation](https://github.com/all-ride/ride-app-validation)
- [ride/cli](https://github.com/all-ride/ride-cli)
- [ride/cli-app](https://github.com/all-ride/ride-cli-app)
- [ride/web](https://github.com/all-ride/ride-web)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/setup-app
```

or for manual install:

```
composer require ride/app
```
