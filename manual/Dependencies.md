The dependency injector is a very important subsystem of the Pallo framework.
It gives the possibility to define your objects so they can be initiated when they are needed.
Since every main object is initiated through the dependency injector, you can change those implementations to customize the system to your needs. 

## Defining Dependencies

### Dependency

A dependency is a definition of a class instance.
It contains the following attributes:

* __class name__: 
Full class name of the object. This is the only required attribute.
* __interface(s)__: 
Define the interfaces this class implements. Defaults to the class name.
* __id__: 
Id of the instance. 
Id's on itself are not unique, the actual id is the combination of interface and id.
This means different interfaces can hold the same id but it still is a different instance.
* __calls__: 
Constructor and additional method call definitions

### Dependency Call

In most cases, you will have to pass arguments to the constructors or invoke some methods before the instance is ready to use.
You can obtain this by adding method calls to your definition.

A dependency call consists of the __method name__ and optionally some __argument definitions__.

You have different type of arguments:

* __null__  
Force a null value, this argument has no properties
* __scalar__  
A scalar value which can be set using _value_ as property name.
* __array__  
A array value which consists of all the set properties.
* __parameter__  
A parameter from the configuration. The _key_ property name is used to define the parameter. 
You can set a default value for the parameter by setting the _default_ property;
* __dependency__  
A dependency can again be inserted into another definition.
Set the _interface_ property to define the dependency.
You can optionally set the _id_ property to specify the instance.  
* __call__  
With this type, you can call a function, a static method or a method on a defined dependency.
    * Set the property _function_ with the name to invoke a function.
    * To invoke a static method, set the property _class_ with the name of the class and the property _method_ with the name of the method.
    * To invoke a method on a dependency, you can set the _interface_ property with a optional _id_ property to define the dependency. Set the _method_ property to define the method.  

When using a dependency in your argument (dependency or call), you can define the id as a configuration parameter.
To do so, prefix and suffix your id with _%_. 
To fallback to a default value, pipe the default value after the configuration key

    %my.config.key|default%
    
To define the constructor of a dependency, simply add the _\_\_construct_ method to the definition.

### dependencies.json

You can easily define your own dependencies in _dependencies.json_.
This file goes into the _config_ directory of the module directory structure.

The most simple definition of a dependency is a class definition.

    {
        "dependencies": [
            {
                "class": "vendor\\namespace\\Class"
            }
        ]
    }

To define a implementation of a interface, you can use the following dependency definition: 
    
    {
        "dependencies": [
            {
                "class": "vendor\\namespace\\Class",
                "interfaces": "vendor\\namespace\\Interface",
                "id": "myid"
            }
        ]
    }

When your dependency implements more then one interface, you can set an array in the _interfaces_ property:
    
    {
        "dependencies": [
            {
                "class": "vendor\\namespace\\Class",
                "interfaces": ["vendor\\namespace\\InterfaceA", "vendor\\namespace\\InterfaceB"],
                "id": "myid"
            }
        ]
    }
    
You can tag your dependencies:
    
    {
        "dependencies": [
            {
                "class": "vendor\\namespace\\Class",
                "interfaces": ["vendor\\namespace\\InterfaceA", "vendor\\namespace\\InterfaceB"],
                "id": "myid",
                "tags": ["private", "my tag"]
            }
        ]
    }

_Note: The id attribute is optional but advised._

#### Calls

You can define calls to your instance to make sure it's ready to work:

    {
        "dependencies": [
            {
                "class": "vendor\\namespace\\Class",
                "interfaces": "vendor\\namespace\\InterfaceA",
                "id": "myid",
                "calls": [
                    {
                        "method": "__construct",
                        "arguments": [
                            {
                                "name": "argument",
                                "type": "dependency",
                                "properties": {
                                    "interface": "vendor\\namespace\\InterfaceB"
                                }
                            }
                        ]
                    },
                    {
                        "method": "setValueA",
                        "arguments": [
                            {
                                "name": "argument",
                                "type": "parameter",
                                "properties": {
                                    "key": "my.config.parameter",
                                    "default": "value"
                                }
                            }
                        ]
                    },
                    {
                        "method": "setC",
                        "arguments": [
                            {
                                "name": "argument",
                                "type": "dependency",
                                "properties": {
                                    "interface": "vendor\\namespace\\InterfaceC",
                                    "id": "%my.config.id|defaultId%"
                                }
                            }
                        ]
                    },
                    "performAction"
                ]
            }
        ]
    }

#### Extending Dependencies

Assume the following configuration in a low level module:

    {
        "dependencies": [
            {
                "class": "vendorA\\namespace\\SomeAuthenticator",
                "interfaces": "vendorC\\namespace\\Authenticator",
                "id": "vendorA"
            },
            {
                "class": "vendorC\\namespace\\ChainedAuthenticator",
                "interfaces": "vendorC\\namespace\\Authenticator",
                "id": "chain",
                "calls": [
                    {
                        "method": "addAuthenticator",
                        "arguments": [
                            {
                                "interface": "vendorC\\namespace\\Authenticator",
                                "id": "vendorA"
                            }
                        ]
                    }
                ]
            }
        ]
    }
    
The configuration of a a higher level module:    
    
    {
        "dependencies": [
            {
                "class": "vendorB\\namespace\\SomeAuthenticator",
                "interfaces": "vendorC\\namespace\\Authenticator",
                "id": "vendorB"
            },
            {
                "interfaces": "vendorC\\namespace\\Authenticator",
                "extends": "chain",
                "id": "chain",
                "calls": [
                    {
                        "method": "addAuthenticator",
                        "arguments": [
                            {
                                "interface": "vendorC\\namespace\\Authenticator",
                                "id": "vendorB"
                            }
                        ]
                    }
                ]
            }
        ]
    }
    
Your Authenticator with id chain will now contain the authenticators of vendorA and vendorB.

_Note: The id is reassigned in order to actually extend it, if you omit it, you will create a new dependency based on chain._

## Obtaining Dependencies

You should avoid using the dependency injector directly in your code.
Instead, use your dependency configuration to inject the needed instances in your code. 
This makes your code independant and more portable to other systems.

There are situations where you want to program to the dependency injector. (eg factory implementation that will implement the dependency injector in your subsystem, ...)

### Get A Dependency

The most generic way to get a dependency is by providing only the interface. 
The last defined implementation of the interface will be loaded:
    
    $router = $dependencyInjector->get('pallo\\library\\router\\Router');

To obtain a specific implementation, you can pass an id when retrieving a dependency:

    $input = $dependencyInjector->get('pallo\\library\\cli\\input\\Input', 'readline');
    
This will get the input implementation for a interactive shell.

Loaded instances are kept in memory.
When the same dependency is requested multiple times, only a single instance is created and it will be used as result for all requests to that dependency.

### Get All Dependencies

To get all implementation of a interface, you can call:

    $commands = $dependencyInjector->getAll('pallo\\library\\cli\\command\\Command');

### Using DependencyInjector As A Factory

By passing construct arguments, you can let the dependency injector act as a factory.

The dependency injector will use the provided arguments for the constructor of the instance. 
The additional defined calls of the dependency are skipped and the instance will not be kept in the memory of the dependency injector.

    $validator = $dependencyInjector->get('pallo\\library\\validation\\validator\\Validator', 'minmax', array('options' => array('minimum' => 5)));
