Pallo has a simple but powerful event manager for inserting dynamic logic or changing system behaviour.

Events are triggered by name and can be dispatched to functions and/or method calls. 
These are called the event listeners. Any valid PHP callback can work as a event listener.

Event names are _._ (dot) separated strings, preferably a more general token first (eg. _app_, _database_) and defining further down the line (eg _app.dispatch.pre_).

## Trigger A Event

A event can be triggered with a simple call to the event manager:

    <?php 
    
    use pallo\library\event\EventManager;

    function foo(EventManager $eventManager) {    
        $arguments = array(
            'argument1' => 'value1',
            'argument2' => 'value2,
        );
    
        $eventManager->triggerEvent('event.name', $arguments);
    }
    
All listeners to the event _event.name_ are now triggered. 
The arguments for the event are contained in the event argument. 

A listener for this sample event could look like:

    <?php
     
    use pallo\library\event\Event;

    function foo(Event $event) {
        $argument1 = $event->getArgument('argument1');
        $argument2 = $event->getArgument('argument2');
        $argument3 = $event->getArgument('argument3', 'default');
        ...
    }
    
You can define any dependency in the method signature of your event listener.
The instance will be injected by the dependency injector:

    <?php

    use pallo\library\event\Event;    
    use pallo\library\system\System;
    
    funtion bar(Event $event, System $system) {
        ...
    }
    
## Stop The Event Flow

You can stop the event by calling _setPreventDefault_:

    <?php
    
    use pallo\library\event\Event;

    function foo(Event $event) {
        $event->setPreventDefault();
    }
    
## Register A Event Listener

### Through Code

You can register a event listener to the event manager using the call:

    <?php
    
    use pallo\library\event\EventManager;

    function foo(EventManager $eventManager) {
        $eventManager->addEventListener('event.name', 'callback');
    }

Event listeners are executed in the order they are registered. 
It's best to have listeners which are independant of each other.

However, sometimes it's interesting to influence the order of the listeners.
To achieve this, you can pass a index to the registration of your listener. 
Indexes range from 0 to 100. 
New listeners without a index will be added from 50 onwards.
This gives enough room before and after the default index.

In the following example, _$bar->method()_ would be triggered before _$foo->method()_ when the event _event.name_ is triggered:

    <?php
    
    use pallo\library\event\EventManager;

    function foo(EventManager $eventManager, $foo, $bar) {
        $eventManager->addEventListener('event.name', array($foo, 'method'));
        $eventManager->addEventListener('event.name', array($bar, 'method'), 10);
        
        $eventManager->triggerEvent('event.name');
    }
    
In the following example, _$foo->methodC()_ will be triggered first, then _$foo->methodA()_, _$bar->methodD()_ and finally _$bar->methodB()_:

    <?php
    
    use pallo\library\event\EventManager;

    function foo(EventManager $eventManager, $foo, $bar) {
        $eventManager->addEventListener('event.name', array($foo, 'methodA'));
        $eventManager->addEventListener('event.name', array($bar, 'methodB'), 70);
        $eventManager->addEventListener('event.name', array($foo, 'methodC'), 10);
        $eventManager->addEventListener('event.name', array($bar, 'methodD'));
    
        $eventManager->triggerEvent('event.name');
    }
    
### Through Configuration

You can create a _config/events.json_ file to define your event listeners.

Check the following sample to know the properties:

    {
        "event.name": [
            {
                "interface": "vendor\\MyClass",
                "method": "myListenerMethod"
            },
            {
                "interface": "vendor\\MyClass",
                "method": "myListenerMethod"
                "weight": "%template.event.weight|90%"
            }
        ]
    }
    
The instance is created by the dependency injector which gives you the chance to prepare your instance before the event is triggered.

The weight will be loaded from the configuration parameters if it's delimited by _%_.
You can set a default value by piping it, see above.