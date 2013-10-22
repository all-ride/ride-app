<?php

namespace pallo\application\event;

use pallo\library\decorator\Decorator;
use pallo\library\event\GenericEventManager;
use pallo\library\event\Event;
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
    public function setLog(Log $log) {
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
                $this->log->logDebug('Triggering ' . $eventListener, $logArguments, self::LOG_SOURCE);
            }

            $this->invoker->invoke($eventListener->getCallback(), array('event' => $event));

            if ($event->isPreventDefault()) {
                break;
            }
        }

        return true;
    }

}