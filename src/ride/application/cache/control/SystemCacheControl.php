<?php

namespace ride\application\cache\control;

use ride\application\system\init\CacheableSystemInitializer;
use ride\application\system\System;

/**
 * Cache control implementation for the system initializer
 */
class SystemCacheControl extends AbstractCacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'system';

    /**
     * Instance of the system
     * @var ride\application\system\System
     */
    private $system;

    /**
     * Cacheable system initializers
     * @var array|boolean
     */
    private $initializers;

    /**
     * Constructs a new system cache control
     * @param \ride\application\system\System $system
     * @return null
     */
    public function __construct(System $system) {
        $this->system = $system;
    }

    /**
     * Gets whether this cache can be enabled/disabled
     * @return boolean
     */
    public function canToggle() {
        return false;
    }

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        $initializers = $this->getSystemInitializers();
        if (!$initializers) {
            return false;
        }

        foreach ($initializers as $initializer) {
            if ($initializer->isCacheEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Warms up the cache
     * @return null
     */
    public function warm() {
        $initializers = $this->getSystemInitializers();
        if (!$initializers) {
            return;
        }

        foreach ($initializers as $initializer) {
            $initializer->warmCache();
        }
    }

    /**
	 * Clears this cache
	 * @return null
     */
    public function clear() {
        $initializers = $this->getSystemInitializers();
        if (!$initializers) {
            return;
        }

        foreach ($initializers as $initializer) {
            $initializer->clearCache();
        }
    }

    /**
     * Gets the cacheable system initializers
     * @return array
     * @see \ride\application\system\init\AbstractCacheableSystemInitializer
     */
    private function getSystemInitializers() {
        if ($this->initializers !== null) {
            return $this->initializers;
        }

        $initializers = $this->system->getParameter('initializers');
        if (!$initializers || !is_array($initializers)) {
            $this->initializers = false;
        } else {
            $this->initializers = array();
            foreach ($initializers as $initializer) {
                if ($initializer instanceof CacheableSystemInitializer) {
                    $this->initializers[] = $initializer;
                }
            }
        }

        return $this->initializers;
    }

}
