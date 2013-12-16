<?php

namespace pallo\application\cache\control;

use pallo\application\event\loader\io\CachedEventListenerIO;

use pallo\library\config\Config;
use pallo\library\event\loader\io\EventListenerIO;

/**
 * Cache control implementation for the events
 */
class EventCacheControl extends AbstractCacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'events';

    /**
     * Instance of the event listener I/O
     * @var pallo\library\event\loader\io\EventListenerIO
     */
    private $io;

    /**
     * Instance of the configuration
     * @var pallo\library\config\Config
     */
    private $config;

    /**
     * Constructs a new event cache control
     * @param pallo\library\event\loader\io\EventListenerIO $io
     * @param pallo\library\config\Config $config
     * @return null
     */
    public function __construct(EventListenerIO $io, Config $config) {
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
        $io = $this->config->get('system.event.listener.default');
        if ($io == 'cache') {
            return;
        }

        $this->config->set('system.event.listener.cache', $io);
        $this->config->set('system.event.listener.default', 'cache');
    }

    /**
     * Disables this cache
     * @return null
     */
    public function disable() {
        $io = $this->config->get('system.event.listener.default');
        if ($io != 'cache') {
            return;
        }

        $io = $this->config->get('system.event.listener.cache');

        $this->config->set('system.event.listener.default', $io);
        $this->config->set('system.event.listener.cache', null);
    }

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        return $this->io instanceof CachedEventListenerIO;
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