# Geeni

Geeni is a server which loads javascripts and styles that are defined in your HTML as attributes.

## Installation

To install Geeni, checkout the repository and run [Composer](http://getcomposer.org) to get the dependencies. 

    cd /path/for/your/geeni
    git clone https://github.com/statikbe/geeni.git
    curl -sS https://getcomposer.org/installer | php
    composer.phar install

That's it, you can now browse to the public directory of the installed Geeni to get you started.

## Maintain Available Modules

Your modules are stored in:

* public/js/modules
* public/css/modules

If you want to add a new module to your service, place the needed files in the module type directory.

You should tell Geeni about the new module in the _application/data/geeni/modules.json_ file.

This file is a simple json data structure with the available module versions:

    {
        "js": {
            "bootstrap": [
                "3.0.2"
            ],
            "jquery": [
                "2.0.3",
                "1.10.2"
            ]
        },
        "css": {
            "bootstrap": [
                "3.0.2"
            ],
            "bootstrap-theme": [
                "3.0.2"
            ]
        }
    }

_Note: module versions are sorted from new to old._

## Secure Your Service

You can secure your Geeni server to host assets only to predefined hosts. To do so, set the _geeni.secured_ parameter in _application/config/parameters.json_ to true. 

Your server will now host only to websites whose hostname has a file in _application/data/geeni/hosts_. The file has the same structure as _modules.json_ and holds the required versions of the website.

Don't forget to clear the cache when you change configuration or existing modules. The application cache is cleared by emptying the _application/data/cache_ directory, the public cache is cleared by emptying the _public/cache_ directory.
