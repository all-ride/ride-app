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

    use pallo\library\log\Log;

    $log->logInformation('Log message', 'an optional description', 'name of the log source');
    
You can also easily log exceptions:

    try {
        // some buggy code
    } catch (Exception $exception) {
        $log = $zibo->getLog();
        if ($log) {
            $log->logException($exception);
        }
        
        // handle exception
    }

For a full overview of the log methods, check the API of the [pallo\library\log\Log](docs/api/class/pallo/library/log/Log) class.

## Log Listeners

By default, the log messages are written to file in the _application/data/log_ directory.
The log file is the name of your environment with the _.log_ extension.

Other log listeners can be implemented and registered through the dependencies.

First, create your listener:

    namespace foo/log/listener;

    use pallo\library\log\listener\LogListener;
    use pallo\library\log\LogMessage;

    class FooLogListener implements LogListener {
        
        public function logMessage(LogMessage $message) {
            // your implementation
        }
        
    }
    
Then you add it to the core logger using the dependencies:

    {
        "dependencies": [
            {
                "class": "foo\\log\\listener\\FooLogListener",
                "interfaces": "pallo\\library\\log\\listener\\LogListener",
                "id": "foo"
            },
            {
                "interfaces": "pallo\\library\\log\\Log",
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
                                    "interface": "pallo\\library\\log\\listener\\LogListener",
                                    "id": "foo"
                                }
                            }
                        ]
                    }
                ]
            }
        ]
    }
    
You can use the [pallo\library\log\listener\AbstractLogListener](/docs/api/class/pallo/library/log/listener/AbstractLogListener) class as a base for your listener.    