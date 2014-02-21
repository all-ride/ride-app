Log messages can help you track and debug the flow of your application.

There are 4 levels of log messages:

* __error (1)__:    
Messages of events which cause the system to crash (exceptions, ...)
* __warning (2)__:   
Messages of events which will probably cause the system to crash (required file missing, ...)
* __information (4)__:  
Messages of normal significant events of the system (mail sent, user added, ...)
* __debug (8)__:  
Messages of normal insignificant events of the application (template rendered, event triggered, ...)

## Logging A Message

    <?php
    
    use ride\library\log\Log;

    function foo(Log $log) {
        $log->logInformation('Log message', 'an optional description', 'name of the log source');
    }
    
You can also easily log exceptions:

    <?php

    use ride\library\log\Log;

    function foo(Log $log) {
        try {
            // some buggy code
        } catch (Exception $exception) {
            $log->logException($exception);
            
            // handle exception
        }
    }

For a full overview of the log methods, check the API of the [ride\library\log\Log](docs/api/class/ride/library/log/Log) class.

## Log Listeners

By default, the log messages are written to file in the _application/data/log_ directory.
The log file is the name of your environment with the _.log_ extension.

Other log listeners can be implemented and registered through the dependencies.

First, create your listener:

    <?php

    namespace foo/log/listener;

    use ride\library\log\listener\LogListener;
    use ride\library\log\LogMessage;

    class FooLogListener implements LogListener {
        
        public function logMessage(LogMessage $message) {
            // your implementation
        }
        
    }
    
Then you add it to the log using the dependencies:

    {
        "dependencies": [
            {
                "class": "foo\\log\\listener\\FooLogListener",
                "interfaces": "ride\\library\\log\\listener\\LogListener",
                "id": "foo"
            },
            {
                "interfaces": "ride\\library\\log\\Log",
                "extends": "app",
                "id": "app",
                "calls": [
                    {
                        "method": "addLogListener",
                        "arguments": [
                            {
                                "name": "listener",
                                "type": "dependency",
                                "properties": {
                                    "interface": "ride\\library\\log\\listener\\LogListener",
                                    "id": "foo"
                                }
                            }
                        ]
                    }
                ]
            }
        ]
    }
    
You can use the [ride\library\log\listener\AbstractLogListener](/docs/api/class/ride/library/log/listener/AbstractLogListener) class as a base for your listener.    