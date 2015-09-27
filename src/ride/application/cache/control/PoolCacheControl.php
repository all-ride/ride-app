<?php

namespace ride\application\cache\control;

use ride\library\cache\pool\CachePool;

/**
 * Cache control implementation for cache pool instances
 */
class PoolCacheControl extends AbstractCacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'pool';

    /**
     * Cache pools
     * @var array
     * @see \ride\library\cache\pool\CachePool
     */
    protected $cachePools = array();

    /**
     * Adds a cache pool to the control
     * @param \ride\library\cache\pool\CachePool $cachePool
     * @return null
     */
    public function addCachePool(CachePool $cachePool) {
        $this->cachePools[] = $cachePool;
    }
    
    /**
     * Removes a cache pool from the control
     * @param \ride\library\cache\pool\CachePool $cachePool
     * @return null
     */
    public function removeCachePool(CachePool $cachePool) {
        foreach ($this->cachePools as $index => $cp) {
            if ($cp === $cachePool) {
                unset($this->cachePools[$index]);
            }
        }
    }
    
    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        return true;
    }

    /**
	 * Clears this cache
	 * @return null
     */
    public function clear() {
        foreach ($this->cachePools as $cachePool) {
            $cachePool->flush();
        }
    }

}