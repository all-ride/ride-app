<?php

namespace pallo\application\event\loader\io;

use pallo\library\config\io\AbstractIO;
use pallo\library\config\Config;
use pallo\library\event\exception\EventException;
use pallo\library\event\loader\io\EventListenerIO;
use pallo\library\event\EventListener;
use pallo\library\system\file\browser\FileBrowser;
use pallo\library\system\file\File;

/**
 * Interface to read event definitions from the data source
 */
class ConfigEventListenerIO extends AbstractIO implements EventListenerIO {

    /**
     * File name
     * @var string
     */
    const FILE = 'events.conf';

    /**
     * Instance of the configuration
     * @var pallo\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new XML dependency IO
     * @param pallo\core\environment\filebrowser\FileBrowser $fileBrowser
     * @param string $environment
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, Config $config, $path = null) {
        parent::__construct($fileBrowser, self::FILE, $path);

        $this->config = $config;
    }

    /**
     * Reads all the event listeners from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with EventListener instances as value
     */
    public function readEventListeners() {
        $path = null;
        if ($this->path) {
            $path = $this->path . File::DIRECTORY_SEPARATOR;
        }

        $files = array_reverse($this->fileBrowser->getFiles($path . $this->file));

        if ($this->environment) {
            $path .= $this->environment . File::DIRECTORY_SEPARATOR;

            $files += array_reverse($this->fileBrowser->getFiles($path . $this->file));
        }

        $eventListeners = array();
        foreach ($files as $file) {
            $fileEventListeners = $this->readEventListenersFromFile($file);
            foreach ($fileEventListeners as $eventListener) {
                $event = $eventListener->getEvent();

                if (!isset($eventListeners[$event])) {
                    $eventListeners[$event] = array($eventListener);
                } else {
                    $eventListeners[$event][] = $eventListener;
                }
            }
        }

        return $eventListeners;
    }

    /**
     * Reads the events file
     * @param zibo\library\filesystem\File $file File to read
     * @return array Array with Event objects
     * @throws Exception when a event line is invalid
     */
    public function readEventListenersFromFile(File $file) {
        $eventListeners = array();

        if ($file->isDirectory()) {
            throw new EventException('Provided file is a directory: ' . $file);
        }

        $content = $file->read();

        $lines = explode("\n", $content);
        foreach ($lines as $index => $originalLine) {
            $line = trim($originalLine);
            if (!$line) {
                continue;
            }

            $start = substr($line, 0, 1);
            if ($start == ';' || $start == '#') {
                continue;
            }

            $positionSpace = strpos($line, ' ');
            if ($positionSpace === false) {
                throw new EventException('Invalid event line in ' . $file . '(' . ($index+1) . '): no class set - ' . $originalLine);
            }

            $event = substr($line, 0, $positionSpace);

            $line = trim(substr($line, $positionSpace));

            $positionSpace = strpos($line, ' ');
            if ($positionSpace === false) {
                $callback = $line;
                $weight = null;
            } else {
                $callback = substr($line, 0, $positionSpace);
                $weight = trim(substr($line, $positionSpace));
            }

            $callback = $this->processCallback($callback);
            $weight = $this->processParameter($weight);

            $eventListeners[] = new EventListener($event, $callback, $weight);
        }

        return $eventListeners;
    }

    /**
     * Processes the parameters in the callback string
     * @param string $callback Callback string
     * @return string Provided callback with the parameters resolved
     */
    protected function processCallback($callback) {
        $callback = $this->processParameter($callback);

        if (strpos($callback, '->') !== false) {
            list($class, $method) = explode('->', $callback, 2);
            if (strpos($class, '#') === false) {
                $id = null;
            } else {
                list($class, $id) = explode('#', $class, 2);
            }

            $class = $this->processParameter($class);
            $id = $this->processParameter($id);
            $method = $this->processParameter($method);

            $callback = $class;
            if ($id) {
                $callback .= '#' . $id;
            }
            $callback .= '->' . $method;
        } elseif (strpos($callback, '::') !== false) {
            list($class, $method) = explode('::', $callback, 2);

            $class = $this->processParameter($class);
            $method = $this->processParameter($method);

            $callback = $class . '::' . $method;
        } else {
            $callback = $this->processParameter($callback);
        }

        return $callback;
    }

    /**
     * Gets a parameter value if applicable (delimited by %)
     * @param string $parameter Parameter string
     * @return string Provided parameter if not a parameter string, the
     * parameter value otherwise
     */
    protected function processParameter($parameter) {
        if (substr($parameter, 0, 1) != '%' && substr($parameter, -1) != '%') {
            return $parameter;
        }

        $parameter = substr($parameter, 1, -1);

        if (strpos($parameter, '|') !== false) {
            list($key, $default) = explode('|', $parameter, 2);
        } else {
            $key = $parameter;
            $default = null;
        }

        return $this->config->get($key, $default);
    }

}