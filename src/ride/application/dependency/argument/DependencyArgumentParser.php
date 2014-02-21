<?php

namespace ride\application\dependency\argument;

use ride\library\config\Config;
use ride\library\dependency\argument\DependencyArgumentParser as LibDependencyArgumentParser;
use ride\library\dependency\DependencyCallArgument;

/**
 * Parser for defined dependency values with config support.
 */
class DependencyArgumentParser extends LibDependencyArgumentParser {

    /**
     * Delimiter for a parameter value
     * @var string
     */
    const DELIMITER = '%';

    /**
     * Instance of the configuration
     * @var ride\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new dependency argument parser
     * @param ride\library\config\Config $config
     * @return null
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Gets the id of the dependency
     * @param zibo\library\dependency\DependencyCallArgument $argument
     * @return string|null
     */
    protected function getDependencyId(DependencyCallArgument $argument) {
        $id = $argument->getProperty(self::PROPERTY_ID);
        $id = self::processDependencyId($id, $this->config);

        return $id;
    }

    /**
     * Processes the id as a Zibo parameter if it's delimited by the parameter
     * delimiter
     * @param string|null $id A dependency id
     * @param zibo\library\config\Config $config Instance of the parameter
     * configuration
     * @return string|null
     * @todo get rid of the static state of this method
     */
    public static function processDependencyId($id, Config $config) {
        if (!$id) {
            return null;
        }

        if (substr($id, 0, 1) != '%' || substr($id, -1) != '%') {
            return $id;
        }

        $parameter = substr($id, 1, -1);

        if (strpos($parameter, '|') !== false) {
            list($key, $default) = explode('|', $parameter, 2);
        } else {
            $key = $parameter;
            $default = null;
        }

        return $config->get($key, $default);
    }

}