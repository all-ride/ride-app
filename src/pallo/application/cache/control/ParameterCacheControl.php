<?php

namespace pallo\application\cache\control;

use pallo\library\config\io\CachedConfigIO;
use pallo\library\config\io\ConfigIO;

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
     * @var pallo\library\config\io\ConfigIO
     */
    private $io;

    /**
     * Constructs a new cache control
     * @param pallo\library\config\io\ConfigIO $io
     * @return null
     */
    public function __construct(ConfigIO $io) {
        $this->io = $io;
    }

    /**
     * Gets whether this cache is enabled
     * @param zibo\core\Zibo $zibo Instance of Zibo
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