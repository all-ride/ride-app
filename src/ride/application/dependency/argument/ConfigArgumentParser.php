<?php

namespace ride\application\dependency\argument;

use ride\library\config\Config;
use ride\library\dependency\argument\AbstractArgumentParser;
use ride\library\dependency\exception\DependencyException;
use ride\library\dependency\DependencyCallArgument;

/**
 * Parser for defined configuration values
 */
class ConfigArgumentParser extends AbstractArgumentParser {

    /**
     * Name of the property for the key of a parameter
     * @var string
     */
    const PROPERTY_KEY = 'key';

    /**
     * Name of the property for the default value of a parameter
     * @var string
     */
    const PROPERTY_DEFAULT = 'default';

    /**
     * Instance of the configuration
     * @var \ride\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new config argument parser
     * @param \ride\library\config\Config $config
     * @return null
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Gets the actual value of the argument
     * @param \ride\library\dependency\DependencyCallArgument $argument
     * @return mixed Value from the configuration
     */
    public function getValue(DependencyCallArgument $argument) {
        $key = $argument->getProperty(self::PROPERTY_KEY);
        $default = $argument->getProperty(self::PROPERTY_DEFAULT);

        if (!$key) {
            throw new DependencyException('No key property set for argument $' . $argument->getName());
        }

        return $this->config->get($key, $default);
    }

}
