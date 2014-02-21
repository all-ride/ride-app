<?php

namespace ride\application\cache\control;

/**
* Interface to control a cache
*/
interface CacheControl {

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName();

    /**
     * Gets whether this cache can be enabled/disabled
     * @return boolean
     */
    public function canToggle();

    /**
     * Enables this cache
     * @return null
     */
    public function enable();

    /**
     * Disables this cache
     * @return null
     */
    public function disable();

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled();

    /**
	 * Clears this cache
	 * @return null
     */
    public function clear();

}