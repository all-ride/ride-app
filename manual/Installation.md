You can use [Composer](http://getcomposer.org) for a automatic installation. 

To install composer, run the following command in your terminal:

    curl -sS https://getcomposer.org/installer | php
    
This will download composer as _composer.phar_ in your current directory. 
 
Create a _composer.json_ file in your installation directory with the following contents:

    {
        "minimum-stability": "dev",
    }
    
You can do this with the following command:

    echo '{"minimum-stability": "dev"}' > composer.json    

Now you can run the following command to install the web interface: 

    php composer.phar require pallo/setup-web:dev-master
    
If you want the CLI:

    php composer.phar require pallo/setup-cli:dev-master
    
All in one:

    curl -sS https://getcomposer.org/installer | php && echo '{"minimum-stability": "dev"}' > composer.json && php composer.phar require pallo/setup-web:dev-master && php composer.phar require pallo/setup-cli:dev-master