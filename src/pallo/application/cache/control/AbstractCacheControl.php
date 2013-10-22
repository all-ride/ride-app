<?php

namespace pallo\application\cache\control;

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

}