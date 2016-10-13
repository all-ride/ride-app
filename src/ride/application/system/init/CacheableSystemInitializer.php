<?php

namespace ride\application\system\init;

/**
 * Interface to initialize the Ride system in a cacheable way
 */
interface CacheableSystemInitializer extends SystemInitializer {

    /**
     * Enable the cache by setting a cache file
     * @param string $cacheFile Path to the cache file
     * @return null
     */
    public function setCacheFile($cacheFile);

    /**
     * Gets whether the cache is enabled
     * @return boolean
     */
    public function isCacheEnabled();

    /**
     * Clears the cache
     * @return boolean
     */
    public function clearCache();

    /**
     * Warms the cache
     * @return boolean
     */
    public function warmCache();

}
