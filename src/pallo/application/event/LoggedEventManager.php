<?php

namespace pallo\application\event;

use pallo\library\decorator\Decorator;
use pallo\library\event\GenericEventManager;
use pallo\library\log\Log;

/**
 * Logged manager of dynamic events
 */
class LoggedEventManager extends GenericEventManager {

    /**
     * Source of the log messages
     * @var string
     */
    const LOG_SOURCE = 'event';

    /**
     * Instance of the log
     * @var pallo\library\log\Log
     */
    protected $log;

    /**
     * Decorator for logged values
     * @var pallo\library\decorator\Decorator
     */
    protected $valueDecorator;

    /**
     * Sets the instance of the log
     * @param pallo\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log = null) {
        $this->log = $log;
    }

    /**
     * Sets the value decorator for logged values
     * @param pallo\library\decorator\Decorator
     * @return null
     */
    public function setValueDecorator(Decorator $valueDecorator = null) {
    	$this->valueDecorator = $valueDecorator;
    }

    /**
     * Registers a new event listener
     * @param string $event Name of the event
     * @param string|array|pallo\library\reflection\Callback $callback Callback
     * of the event listener
     * @param string $weight Weight in the listener list
     * @return EventListener
     * @throws pallo\library\event\exception\EventException when a invalid
     * argument has been provided
     * @throws pallo\library\event\exception\EventException when the weight of
     * the event listener is invalid or already set
     */
    public function addEventListener($event, $callback, $weight = null) {
        $eventListener = parent::addEventListener($event, $callback, $weight);

        if ($this->log) {
            $this->log->logDebug('Added event listener', $eventListener, self::LOG_SOURCE);
        }

        return $eventListener;
    }

    /**
     * Unregisters a event listener
     * @param mixed $eventListener A integer to unregister by weight, a
     * callback or a instance of EventListener
     * @return boolean True when a event has been removed, false otherwise
     */
    public function removeEventListener($event = null, $eventListener = null) {
        $result = parent::unregisterEventListener($eventListener, $event);

        if ($this->log && $result) {
            if (!$event && !$eventListener) {
                $this->log->logDebug('Removed all event listeners', null, self::LOG_SOURCE);
            } else {
                if (!$event) {
                    $event = $eventListener->getEvent();
                }

                $this->log->logDebug('Removed event listener', $eventListener, self::LOG_SOURCE);
            }
        }

        return $result;
    }

    /**
     * Triggers the listeners of the provided event with the provided arguments
     * @param string $event Name of the event
     * @param array $arguments Array with the arguments for the event listener
     * @return boolean True when event listeners have been triggered, false
     * otherwise
     * @throws Exception when the provided event name is empty or invalid
     */
    public function triggerEvent($event, array $arguments = null) {
        if (!$this->hasEventListeners($event)) {
            if ($this->log) {
                $this->log->logDebug('Triggering ' . $event, 'no listeners for this event', self::LOG_SOURCE);
            }

            return false;
        }

        if ($arguments === null) {
            $arguments = array();
        }

        if ($this->log) {
	        if ($this->valueDecorator) {
	            $logArguments = (string) $this->valueDecorator->decorate($arguments);
	        } else {
	            $logArguments = '[...]';
	        }
        }

        $event = new Event($event, $arguments);

        foreach ($this->events[$event->getName()] as $weight => $eventListener) {
            if ($this->log) {
                $this->log->logDebug('Triggering ' . $eventListener, $logArgument, self::LOG_SOURCE);
            }

            $this->invoker->invoke($eventListener->getCallback(), $callbackArguments);

            if ($event->isPreventDefault()) {
                break;
            }
        }

        return true;
    }

}