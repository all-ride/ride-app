<?php

namespace ride\application\cache\control;

use ride\library\cache\control\CacheControl;

/**
* Interface to control a cache
*/
abstract class AbstractCacheControl implements CacheControl {

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Gets whether this cache can be enabled/disabled
     * @return boolean
     */
    public function canToggle() {
        return false;
    }

    /**
     * Enables this cache
     * @return null
     */
    public function enable() {

    }

    /**
     * Disables this cache
     * @return null
     */
    public function disable() {

    }

    /**
     * Warms up the cache
     * @return null
     */
    public function warm() {

    }

}
