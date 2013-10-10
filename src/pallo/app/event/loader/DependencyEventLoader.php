<?php

namespace pallo\app\event\loader;

use pallo\library\dependency\DependencyInjector;
use pallo\library\event\loader\io\EventListenerIO;
use pallo\library\event\loader\GenericEventLoader;
use pallo\library\event\EventManager;

/**
 * Dependency implementation of a event loader
 */
class DependencyEventLoader extends GenericEventLoader {

    /**
     * Instance of the dependency injector
     * @var zibo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Constructs a new event loader
     * @param pallo\library\dependency\DependencyInjector $dependencyInjector
     * @param pallo\app\event\io\EventIO $io
     * @return null
     */
    public function __construct(EventListenerIO $io, DependencyInjector $dependencyInjector) {
        parent::__construct($io);

        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * Processes the callback and creates the necessairy instances
     * @param string $callback Callback string
     * @return array|string
     */
    protected function processCallback($callback) {
        if (!is_string($callback)) {
            return $callback;
        }

        if (strpos($callback, '->') !== false) {
            // instance method
            list($class, $method) = explode('->', $callback, 2);
            if (strpos($class, '#') === false) {
                $id = null;
            } else {
                list($class, $id) = explode('#', $class, 2);
            }

            $instance = $this->dependencyInjector->get($class, $id);

            return array($instance, $method);
        } elseif (strpos($callback, '::') !== false) {
            // static method
            return explode('::', $callback, 2);
        } else {
            // function
            return $callback;
        }
    }

}