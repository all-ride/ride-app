<?php

namespace pallo\application\dependency\argument;

use pallo\library\config\Config;
use pallo\library\dependency\argument\CallArgumentParser as LibCallArgumentParser;
use pallo\library\dependency\DependencyCallArgument;

/**
 * Parser to get a value through a call; with config support.
 */
class CallArgumentParser extends LibCallArgumentParser {

    /**
     * Instance of the configuration
     * @var pallo\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new call argument parser
     * @param pallo\library\config\Config $config
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
        $id = DependencyArgumentParser::processDependencyId($id, $this->config);

        return $id;
    }

}