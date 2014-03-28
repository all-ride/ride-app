<?php

namespace ride\application\event\loader;

use ride\library\dependency\DependencyInjector;
use ride\library\event\loader\io\EventListenerIO;
use ride\library\event\loader\GenericEventLoader;

/**
 * Dependency implementation of a event loader
 */
class DependencyEventLoader extends GenericEventLoader {

    /**
     * Instance of the dependency injector
     * @var \ride\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Constructs a new event loader
     * @param \ride\library\dependency\DependencyInjector $dependencyInjector
     * @param \ride\library\event\loader\io\EventListenerIO $io
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
        if (!is_array($callback) || !isset($callback[0]) || !isset($callback[1])) {
            return $callback;
        }

        if (strpos($callback[0], '#') === false) {
            $class = $callback[0];
            $id = null;
        } else {
            list($class, $id) = explode('#', $callback[0], 2);
        }

        $instance = $this->dependencyInjector->get($class, $id);

        return array($instance, $callback[1]);
    }

}