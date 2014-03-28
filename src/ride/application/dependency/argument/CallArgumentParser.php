<?php

namespace ride\application\dependency\argument;

use ride\library\config\Config;
use ride\library\dependency\argument\CallArgumentParser as LibCallArgumentParser;
use ride\library\dependency\DependencyCallArgument;

/**
 * Parser to get a value through a call; with config support.
 */
class CallArgumentParser extends LibCallArgumentParser {

    /**
     * Instance of the configuration
     * @var \ride\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new call argument parser
     * @param \ride\library\config\Config $config
     * @return null
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Gets the id of the dependency
     * @param \ride\library\dependency\DependencyCallArgument $argument
     * @return string|null
     */
    protected function getDependencyId(DependencyCallArgument $argument) {
        $id = $argument->getProperty(self::PROPERTY_ID);
        $id = DependencyArgumentParser::processDependencyId($id, $this->config);

        return $id;
    }

}