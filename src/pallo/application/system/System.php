<?php

namespace pallo\application\system;

use pallo\application\dependency\argument\CallArgumentParser;
use pallo\application\dependency\argument\ConfigArgumentParser;
use pallo\application\dependency\argument\DependencyArgumentParser;
use pallo\application\dependency\io\CachedDependencyIO;
use pallo\application\dependency\io\XmlDependencyIO;
use pallo\application\system\init\ComposerSystemInitializer;

use pallo\library\config\io\CachedConfigIO;
use pallo\library\config\io\ParserConfigIO;
use pallo\library\config\parser\JsonParser;
use pallo\library\config\GenericConfig;
use pallo\library\config\ConfigHelper;
use pallo\library\dependency\DependencyInjector;
use pallo\library\log\Log;
use pallo\library\reflection\ReflectionHelper;
use pallo\library\system\file\browser\GenericFileBrowser;
use pallo\library\system\file\browser\FileBrowser;
use pallo\library\system\System as LibSystem;
use pallo\library\ErrorHandler;
use pallo\library\String;
use pallo\library\Timer;

/**
 * Factory for Pallo applications
 */
class System extends LibSystem {

    /**
     * Name of the config directory
     * @var string
     */
    const DIRECTORY_CACHE = 'cache';

    /**
     * Name of the config directory
     * @var string
     */
    const DIRECTORY_CONFIG = 'config';

    /**
     * Name of the public directory
     * @var string
     */
    const DIRECTORY_PUBLIC = 'public';

    /**
     * Name of the view directory
     * @var string
     */
    const DIRECTORY_VIEW = 'view';

    /**
     * Source for application log messages
     * @var string
     */
    const LOG_SOURCE = 'app';

    /**
     * Parameter for the default application
     * @var string
     */
    const PARAM_APPLICATION = 'system.application';

    /**
     * Parameter for the name of the system
     * @var string
     */
    const PARAM_NAME = 'system.name';

    /**
     * Parameter for the secret key of the system
     * @var string
     */
    const PARAM_SECRET = "system.secret";

    /**
     * Parameter for the default timezone
     * @var string
     */
    const PARAM_TIME_ZONE = 'system.timezone';

    /**
     * System parameters
     * @var array
     */
    protected $parameters;

    /**
     * Instance of the file browser
     * @var pallo\library\system\file\browser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Instance of the config
     * @var pallo\library\config\Config
     */
    protected $config;

    /**
     * Instance of the dependency injector
     * @var pallo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Constructs a new Pallo system
     * @return null
     */
    public function __construct(array $parameters = null) {
        $errorHandler = new ErrorHandler();
        $errorHandler->registerErrorHandler();

        $this->timer = new Timer();
        $this->parameters = $parameters;

        if (!isset($this->parameters['environment'])) {
            $this->parameters['environment'] = 'dev';
        }
    }

    /**
     * Gets a system parameter
     * @param string $key Key of the parameter
     * @param mixed $default Default value to return when the parameter is not
     * set
     * @return mixed Value of the parameter if set, provided default value
     * otherwise
     */
    public function getParameter($key, $default = null) {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * Gets the name of the environment
     * @return string
     */
    public function getEnvironment() {
        return $this->parameters['environment'];
    }

    /**
     * Gets the dependency injector
     * @return pallo\library\dependency\DependencyInjector
     */
    public function getDependencyInjector() {
        if (!$this->dependencyInjector) {
            $this->dependencyInjector = $this->createDependencyInjector();
        }

        return $this->dependencyInjector;
    }

    /**
     * Creates the dependency injector
     * @return pallo\library\dependency\DependencyInjector
     */
    protected function createDependencyInjector() {
        $config = $this->getConfig();

        $callArgumentParser = new CallArgumentParser($config);
        $configArgumentParser = new ConfigArgumentParser($config);
        $dependencyArgumentParser = new DependencyArgumentParser($config);

        $dependencyContainer = $this->createDependencyIO()->getDependencyContainer();
        $reflectionHelper = new ReflectionHelper();

        $dependencyInjector = new DependencyInjector($dependencyContainer, $reflectionHelper);
        $dependencyInjector->setArgumentParser(DependencyInjector::TYPE_CALL, $callArgumentParser);
        $dependencyInjector->setArgumentParser(DependencyInjector::TYPE_DEPENDENCY, $dependencyArgumentParser);
        $dependencyInjector->setArgumentParser('parameter', $configArgumentParser);

        $dependencyInjector->setInstance($reflectionHelper);
        $dependencyInjector->setInstance($dependencyInjector);
        $dependencyInjector->setInstance($dependencyInjector, 'pallo\\library\\reflection\\Invoker');
        $dependencyInjector->setInstance($config, 'pallo\\library\\config\\Config');
        $dependencyInjector->setInstance($this->fileBrowser, 'pallo\\library\\system\\file\\browser\\FileBrowser');
        $dependencyInjector->setInstance($this->fs, 'pallo\library\system\\file\\FileSystem');
        $dependencyInjector->setInstance($this, 'pallo\\app\\system\\System');
        $dependencyInjector->setInstance($this, 'pallo\\library\\system\\System');
        $dependencyInjector->setInstance($this->timer);

        unset($this->timer);

        return $dependencyInjector;
    }

    /**
     * Creates the dependency IO
     * @return pallo\application\dependency\io\XmlDependencyIO
     */
    protected function createDependencyIO() {
        $fileBrowser = $this->getFileBrowser();

        $dependencyIO = new XmlDependencyIO($fileBrowser, self::DIRECTORY_CONFIG);
        $dependencyIO->setEnvironment($this->parameters['environment']);

        if (isset($this->parameters['cache']['dependencies']) && $this->parameters['cache']['dependencies']) {
            $file = self::DIRECTORY_CACHE . '/' . $this->parameters['environment'] . '/dependencies.php';
            $file = $fileBrowser->getApplicationDirectory()->getChild($file);

            $dependencyIO = new CachedDependencyIO($dependencyIO, $file);
        }

        return $dependencyIO;
    }

    /**
     * Gets the configuration
     * @return pallo\library\config\Config
     */
    public function getConfig() {
        if (!$this->config) {
            $this->config = $this->createConfig();
        }

        return $this->config;
    }

    /**
     * Creates the configuration
     * @return null
     */
    protected function createConfig() {
        $fileBrowser = $this->getFileBrowser();
        $helper = new ConfigHelper();
        $parser = new JsonParser();

        $io = new ParserConfigIO($fileBrowser, $helper, $parser, 'json', self::DIRECTORY_CONFIG);
        $io->setEnvironment($this->parameters['environment']);

        if (isset($this->parameters['cache']['config']) && $this->parameters['cache']['config']) {
            $file = self::DIRECTORY_CACHE . '/' . $this->parameters['environment'] . '/config.php';
            $file = $fileBrowser->getApplicationDirectory()->getChild($file);

            $io = new CachedConfigIO($io, $file);
        }

        return new GenericConfig($io, $helper);
    }

    /**
     * Gets the file browser
     * @return pallo\library\system\file\browser\FileBrowser
     */
    public function getFileBrowser() {
        if ($this->fileBrowser) {
            return $this->fileBrowser;
        }

        $this->fileBrowser = $this->createFileBrowser();

        // whenever you request the file browser, you need the system to
        // be ready and initialized, let's do just that
        $initializer = $this->createSystemInitializer();
        $initializer->initializeSystem($this);

        return $this->fileBrowser;
    }

    /**
     * Creates the file browser
     * @return pallo\library\system\file\browser\FileBrowser
     */
    protected function createFileBrowser() {
        $this->getFileSystem();

        $fileBrowser = new GenericFileBrowser();
        $fileBrowser->setPublicPath(self::DIRECTORY_PUBLIC);

        return $fileBrowser;
    }

    /**
     * Gets the system initializer
     * @return pallo\application\system\init\SystemInitializer
     */
    protected function createSystemInitializer() {
        return new ComposerSystemInitializer();
    }

    /**
     * Sets the default timezone
     * @param string $timezone Timezone identifier (eg. Europe/Brussels). If
     * omitted, the time zone will be retrieved from the parameters with
     * Europe/Brussels as fallback
     * @return null
     */
    public function setTimeZone($timeZone = null) {
        if (!$timeZone) {
            $timeZone = $this->getConfig()->get(self::PARAM_TIME_ZONE, 'Europe/Brussels');
        }

        date_default_timezone_set($timeZone);
    }

    /**
     * Gets the name of the system
     * @return string
     */
    public function getName() {
        $config = $this->getConfig();

        return $config->get(self::PARAM_NAME, 'Pallo');
    }

    /**
     * Gets the secret key of the system, usuable for encryption
     * @return string
     */
    public function getSecretKey() {
        $secret = $this->getConfig()->get(self::PARAM_SECRET);

        if (!$secret) {
            $secret = substr(hash('sha512', md5(time())), 0, 21);

            $this->getConfig->set(self::PARAM_SECRET, $secret);
        }

        return $secret;
    }

    /**
     * Services a application
     * @param string $id Id of the application
     * @return null
     */
    public function service($id = null) {
        if ($id == null) {
            $id = $this->getConfig()->get(self::PARAM_APPLICATION);
        }

        $app = $this->getDependencyInjector()->get('pallo\\application\\Application', $id);
        $app->service();
    }

}