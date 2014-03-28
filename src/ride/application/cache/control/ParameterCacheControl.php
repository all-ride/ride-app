<?php

namespace ride\application\cache\control;

use ride\library\config\io\CachedConfigIO;
use ride\library\config\io\ConfigIO;

/**
 * Cache control implementation for the configuration
 */
class ParameterCacheControl extends AbstractCacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'parameters';

    /**
     * Instance of configuration I/O
     * @var \ride\library\config\io\ConfigIO
     */
    private $io;

    /**
     * Constructs a new cache control
     * @param \ride\library\config\io\ConfigIO $io
     * @return null
     */
    public function __construct(ConfigIO $io) {
        $this->io = $io;
    }

    /**
     * Gets whether this cache is enabled
     * @param \ride\core\Ride $ride Instance of Ride
     * @return boolean
     */
    public function isEnabled() {
        return $this->io instanceof CachedConfigIO;
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