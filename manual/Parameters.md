The basic configuration of Ride is achieved through a set of key-value pairs, called a parameter. 
The parts of a key are separated with a _._ (dot). 

The parameters are read from the _config_ directory in the module directory structure.  

The reading of the configuration is from bottom up: from lower level modules to higher level modules, up to the application directory. 
This way, a key in application will override the same key in a module on a lower level and as such provide a overrideable system.

## Storage

The parameters are stored in _parameters.json_.

Assume the following configuration:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    mail.sender = no-reply@gmail.com
    system.memory = 8M

This is stored in _config/parameters.json_:

    {
        "mail.recipient.john": "john@gmail.com",
        "mail.recipient.mark": "mark@gmail.com",
        "mail.recipient.sarah": "sarah@gmail.com",
        "mail.sender": "no-reply@gmail.com",
        "system.memory": "8M"
    }

This can also be rewritten like: 

    {
        "mail": {
            "recipient": {
                "john": "john@gmail.com",
                "mark": "mark@gmail.com",
                "sarah": "sarah@gmail.com"
            },
            "sender": "no-reply@gmail.com"
        },
        "system": {
            "memory": "8M"
        }
    }

You can store parameters which are environment specific by creating a subdirectory in your _config_ directory with the environments name:

* application/config/dev/parameters.json
* application/config/prod/parameters.json

Both files can contain a different parameters for each environment.

## Get A Parameter

Some examples, assume the following configuration:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    mail.sender = no-reply@gmail.com
    system.memory = 8M

In PHP, you can retrieve a value with the following code:

    use ride\library\config\Config;

    function foo(Config $config) {
        $value1 = $config->get('system.memory');
        $value2 = $config->get('unexistant.configuration.key');
        
        // $value1 = '8M'
        // $value2 = null
    }

You can pass a default value. 
When the parameter is not set, the provided default value will be returned.

    use ride\library\config\Config;

    function foo(Config $config) {
        $default = 'Default value';
        $value = $config->get('unexistant.configuration.key', $default);
    
        // $value = 'Default value';
    }

The parameters can also act as a configuration tree. 
You can get an array with all the defined recipients:

    use ride\library\config\Config;

    function foo(Config $config) {
        $recipients = $config->get('mail.recipient');
        
        // $recipients = array(
        //     'john' => 'john@gmail.com',
        //     'mark' => 'mark@gmail.com',
        //     'sarah' => 'sarah@gmail.com'
        // )
    
        $mail = $config->get('mail');
        
        // $mail = array(
        //     'sender' => 'no-reply@gmail.com',
        //     'recipients' => array(
        //         'john' => 'john@gmail.com',
        //         'mark' => 'mark@gmail.com',
        //         'sarah' => 'sarah@gmail.com',
        //     ),
        // )
    }

You can flatten fetched hierarchy if needed

    use ride\library\config\Config;

    function foo(Config $config) {
        $mail = $config->get('mail');
        $mail = $config->getConfigHelper()->flattenConfig($mail);
        
        // $mail = array(
        //     'sender' => 'no-reply@gmail.com',
        //     'recipients.john' => 'john@gmail.com',
        //     'recipients.mark' => 'mark@gmail.com',
        //     'recipients.sarah' => 'sarah@gmail.com',
        // )
    }

## Set A Parameter

Assume the following configuration:

    mail.recipient = john@gmail.com
    system.memory = 8M

And the following PHP code:

    use ride\library\config\Config;

    function foo(Config $config) {
        $recipients = array(
            'john' => 'john@gmail.com',
            'mark' => 'mark@gmail.com',
            'sarah' => 'sarah@gmail.com',
        );
        
        $config->set('mail.recipient', $recipients);
        $config->set('system.memory', '16M');
    }

This code will set the configuration to the following:

    mail.recipient.john = john@gmail.com
    mail.recipient.mark = mark@gmail.com
    mail.recipient.sarah = sarah@gmail.com
    system.memory = 16M
        
## Variables    
    
There are 4 variables available: 

* __%application%__   
The path of the application directory
* __%environment%__  
The name of the running environment
* __%path%__  
The path of the module, it can be used to create correct paths for files in your module.
* __%public%__  
The path of the public directory

Assume the file _/var/www/ride/modules/foo/config/parameters.json_ with content:
    
    { "bar.data.counties": "%path%/data/countries.txt" }

The value of _bar.data.countries_ will be
    
    /var/www/ride/modules/foo/data/countries.txt
