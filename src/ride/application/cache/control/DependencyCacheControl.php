<?php

namespace ride\application\cache\control;

use ride\application\dependency\io\CachedDependencyIO;
use ride\application\dependency\io\DependencyIO;
use ride\application\system\System;

use ride\library\config\Config;

/**
 * Cache control implementation for the events
 */
class DependencyCacheControl extends AbstractCacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'dependencies';

    /**
     * Instance of the event listener I/O
     * @var ride\application\dependency\io\DependencyIO
     */
    private $io;

    /**
     * Instance of the configuration
     * @var ride\library\config\Config
     */
    private $config;

    /**
     * Constructs a new dependency cache control
     * @param \ride\application\dependency\io\DependencyIO $io
     * @param \ride\library\config\Config $config
     * @return null
     */
    public function __construct(DependencyIO $io, Config $config) {
        $this->io = $io;
        $this->config = $config;
    }

    /**
     * Gets whether this cache can be enabled/disabled
     * @return boolean
     */
    public function canToggle() {
        return true;
    }

    /**
     * Enables this cache
     * @return null
     */
    public function enable() {
        $this->config->set(System::PARAM_CACHE_DEPENDENCIES, true);
    }

    /**
     * Disables this cache
     * @return null
     */
    public function disable() {
        $this->config->set(System::PARAM_CACHE_DEPENDENCIES, null);
    }

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        return $this->io instanceof CachedDependencyIO;
    }

    /**
     * Warms up the cache
     * @return null
     */
    public function warm() {
        if ($this->isEnabled()) {
            $this->io->warmCache();
        }
    }

    /**
     * Clears this cache
     * @return null
     */
    public function clear() {
        if ($this->isEnabled()) {
            $this->io->clearCache();
        }
    }

}
