<?php

namespace pallo\application\cache\control;

use pallo\application\dependency\io\CachedDependencyIO;
use pallo\application\dependency\io\DependencyIO;
use pallo\application\system\System;

use pallo\library\config\Config;

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
     * @var pallo\application\dependency\io\DependencyIO
     */
    private $io;

    /**
     * Instance of the configuration
     * @var pallo\library\config\Config
     */
    private $config;

    /**
     * Constructs a new dependency cache control
     * @param pallo\application\dependency\io\DependencyIO $io
     * @param pallo\library\config\Config $config
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
	 * Clears this cache
	 * @return null
     */
    public function clear() {
        if (!$this->isEnabled()) {
            return;
        }

        $file = $this->io->getFile();
        if ($file->exists()) {
            $file->delete();
        }
    }

}