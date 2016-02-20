<?php

namespace ride\application\system;

use ride\application\dependency\argument\CallArgumentParser;
use ride\application\dependency\argument\ConfigArgumentParser;
use ride\application\dependency\argument\DependencyArgumentParser;
use ride\application\dependency\io\CachedDependencyIO;
use ride\application\dependency\io\ParserDependencyIO;
use ride\application\system\init\ComposerSystemInitializer;
use ride\application\system\init\SystemInitializer;

use ride\library\config\io\CachedConfigIO;
use ride\library\config\io\ParserConfigIO;
use ride\library\config\parser\JsonParser;
use ride\library\config\GenericConfig;
use ride\library\config\ConfigHelper;
use ride\library\dependency\intelligence\DependencyIntelligence;
use ride\library\dependency\DependencyInjector;
use ride\library\reflection\ReflectionHelper;
use ride\library\system\exception\SystemException;
use ride\library\system\file\browser\GenericFileBrowser;
use ride\library\system\System as LibSystem;
use ride\library\Autoloader;
use ride\library\ErrorHandler;
use ride\library\Timer;

/**
 * Factory for Ride applications
 */
class System extends LibSystem {

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
     * Parameter to see if the dependencies should be cached
     * @var string
     */
    const PARAM_CACHE_DEPENDENCIES = 'system.dependencies.cache';

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
    private $parameters;

    /**
     * Instance of the file browser
     * @var \ride\library\system\file\browser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Instance of the config
     * @var \ride\library\config\Config
     */
    protected $config;

    /**
     * Instance of the dependency injector
     * @var \ride\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Custom autoloader
     * @var \ride\library\Autoloader
     */
    protected $autoloader;

    /**
     * Name of the running service
     * @var string
     */
    private $service;

    /**
     * Constructs a new Ride system
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

        if (!isset($this->parameters['initializers'])) {
            $this->parameters['initializers'] = array(
            	new ComposerSystemInitializer(),
            );
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
     * Gets the custom autoloader from Ride
     * @return \ride\library\Autoloader|boolean Instance of the autoloader if
     * enabled, false otherwise
     */
    public function getAutoloader() {
        if ($this->autoloader !== null) {
            return $this->autoloader;
        }

        if (!isset($this->parameters['autoloader']) || (isset($this->parameters['autoloader']['enable']) && $this->parameters['autoloader']['enable'])) {
            if (isset($this->parameters['autoloader']['prepend'])) {
                $prepend = $this->parameters['autoloader']['prepend'];
            } else {
                $prepend = true;
            }

            $this->autoloader = new Autoloader();
            $this->autoloader->registerAutoloader($prepend);

            if (isset($this->parameters['autoloader']['include_path']) && $this->parameters['autoloader']['include_path']) {
                $this->autoloader->addIncludePaths();
            }
        } else {
            $this->autoloader = false;
        }

        return $this->autoloader;
    }

    /**
     * Gets the dependency injector
     * @return \ride\library\dependency\DependencyInjector
     */
    public function getDependencyInjector() {
        if (!$this->dependencyInjector) {
            $this->dependencyInjector = $this->createDependencyInjector();
        }

        return $this->dependencyInjector;
    }

    /**
     * Creates the dependency injector
     * @return \ride\library\dependency\DependencyInjector
     */
    protected function createDependencyInjector() {
        $config = $this->getConfig();

        $callArgumentParser = new CallArgumentParser($config);
        $configArgumentParser = new ConfigArgumentParser($config);
        $dependencyArgumentParser = new DependencyArgumentParser($config);

        $dependencyIO = $this->createDependencyIO();
        $dependencyContainer = $dependencyIO->getDependencyContainer();

        if ($config->get(self::PARAM_CACHE_DEPENDENCIES)) {
            $file = 'data/cache/' . $this->parameters['environment'] . '/dependencies-factory.php';
            $file = $this->fileBrowser->getApplicationDirectory()->getChild($file);
            $file->getParent()->create();

            $dependencyIntelligence = new DependencyIntelligence($file);
        } else {
            $dependencyIntelligence = null;
        }

        $reflectionHelper = new ReflectionHelper();

        $dependencyInjector = new DependencyInjector($dependencyContainer, $reflectionHelper);
        $dependencyInjector->setIntelligence($dependencyIntelligence);
        $dependencyInjector->setArgumentParser(DependencyInjector::TYPE_CALL, $callArgumentParser);
        $dependencyInjector->setArgumentParser(DependencyInjector::TYPE_DEPENDENCY, $dependencyArgumentParser);
        $dependencyInjector->setArgumentParser('parameter', $configArgumentParser);

        $dependencyInjector->setInstance($reflectionHelper);
        $dependencyInjector->setInstance($dependencyInjector, array('ride\\library\\dependency\\DependencyInjector', 'ride\\library\\reflection\\Invoker'));
        $dependencyInjector->setInstance($dependencyIO, 'ride\\application\\dependency\\io\\DependencyIO');
        $dependencyInjector->setInstance($this->jsonParser, array('ride\\library\\config\\parser\\Parser', 'ride\\library\\config\\parser\\JsonParser'), 'json');
        $dependencyInjector->setInstance($config, 'ride\\library\\config\\Config');
        $dependencyInjector->setInstance($this->configHelper, 'ride\\library\\config\\ConfigHelper');
        $dependencyInjector->setInstance($this->configIO, 'ride\\library\\config\\io\\ConfigIO');
        $dependencyInjector->setInstance($this->fileBrowser, 'ride\\library\\system\\file\\browser\\FileBrowser');
        $dependencyInjector->setInstance($this->fs, 'ride\library\system\\file\\FileSystem');
        $dependencyInjector->setInstance($this, array('ride\\library\\system\\System', 'ride\\application\\system\\System'));
        $dependencyInjector->setInstance($this->timer);

        $argumentParsers = $dependencyInjector->getAll('ride\\library\\dependency\\argument\\ArgumentParser');
        foreach ($argumentParsers as $argumentParserId => $argumentParser) {
            $dependencyInjector->setArgumentParser($argumentParserId, $argumentParser);
        }

        unset($this->jsonParser);
        unset($this->configHelper);
        unset($this->configIO);
        unset($this->timer);

        return $dependencyInjector;
    }

    /**
     * Creates the dependency IO
     * @return \ride\application\dependency\io\XmlDependencyIO
     */
    protected function createDependencyIO() {
        $fileBrowser = $this->getFileBrowser();
        $config = $this->getConfig();
        $parser = new JsonParser();

        $dependencyIO = new ParserDependencyIO($fileBrowser, $parser, 'dependencies.json', self::DIRECTORY_CONFIG);
        $dependencyIO->setEnvironment($this->parameters['environment']);
        $dependencyIO->setConfig($config);

        if ($config->get(self::PARAM_CACHE_DEPENDENCIES)) {
            $file = 'data/cache/' . $this->parameters['environment'] . '/dependencies.php';
            $file = $fileBrowser->getApplicationDirectory()->getChild($file);

            $dependencyIO = new CachedDependencyIO($dependencyIO, $file);
        }

        return $dependencyIO;
    }

    /**
     * Gets the configuration
     * @return \ride\library\config\Config
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
        $this->configIO = $this->createConfigIO();

        return new GenericConfig($this->configIO, $this->configHelper);
    }

    /**
     * Creates the configuration IO
     * @return null
     */
    protected function createConfigIO() {
        $this->configHelper = new ConfigHelper();
        $this->jsonParser = new JsonParser();

        $fileBrowser = $this->getFileBrowser();

        $io = new ParserConfigIO($fileBrowser, $this->configHelper, $this->jsonParser, 'parameters.json', self::DIRECTORY_CONFIG);
        $io->setEnvironment($this->parameters['environment']);

        if (isset($this->parameters['cache']['config']) && $this->parameters['cache']['config']) {
            $file = 'data/cache/' . $this->parameters['environment'] . '/config.php';
            $file = $fileBrowser->getApplicationDirectory()->getChild($file);

            $io = new CachedConfigIO($io, $file);
        }

        return $io;
    }

    /**
     * Gets the file browser
     * @return \ride\library\system\file\browser\FileBrowser
     */
    public function getFileBrowser() {
        if ($this->fileBrowser) {
            return $this->fileBrowser;
        }

        $this->fileBrowser = $this->createFileBrowser();

        // whenever you request the file browser, you need the system to
        // be ready and initialized, let's do just that
        $this->initializeSystem();

        return $this->fileBrowser;
    }

    /**
     * Creates the file browser
     * @return \ride\library\system\file\browser\FileBrowser
     */
    protected function createFileBrowser() {
        $this->getFileSystem();

        $fileBrowser = new GenericFileBrowser();
        $fileBrowser->setPublicPath(self::DIRECTORY_PUBLIC);

        return $fileBrowser;
    }

    /**
     * Gets the log
     * @return \ride\library\log\Log|null
     */
    public function getLog() {
        try {
            return $this->getDependencyInjector()->get('ride\\library\\log\\Log');
        } catch (DependencyNotFoundException $exception) {
            return null;
        }
    }

    /**
     * Invoke the system initializers
     * @return null
     * @throws \ride\library\system\exception\SystemException when a non
     * SystemInitializer instance is detected
     */
    protected function initializeSystem() {
        foreach ($this->parameters['initializers'] as $initializer) {
            if (!$initializer instanceof SystemInitializer) {
                throw new SystemException('Could not initialize the system: no instance of SystemInitializer detected');
            }

            $initializer->initializeSystem($this);
        }

        unset($this->parameters['initializers']);
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
        return $this->getConfig()->get(self::PARAM_NAME, 'Ride');
    }

    /**
     * Gets the secret key of the system, usuable for encryption
     * @return string
     */
    public function getSecretKey() {
        $secret = $this->getConfig()->get(self::PARAM_SECRET);

        if (!$secret) {
            $secret = substr(hash('sha512', md5(time())), 0, 21);

            $this->getConfig()->set(self::PARAM_SECRET, $secret);
        }

        return $secret;
    }

    /**
     * Services a application
     * @param string $id Id of the application
     * @return null
     */
    public function service($id = null) {
        if ($this->service) {
            throw new SystemException('Could not start service: already running ' . $this->service);
        }

        if ($id == null) {
            $id = $this->getConfig()->get(self::PARAM_APPLICATION);
        }

        $app = $this->getDependencyInjector()->get('ride\\application\\Application', $id);

        $this->service = $id;

        $app->service();

        $this->service = null;
    }

    /**
     * Executes a single command on the system
     * @param string $command Command string
     * @param integer $code Return code of the command
     * @return array Output of the command
     * @throws \ride\library\system\exception\SystemException when the command
     * could not be executed
     */
    protected function executeCommand($command, &$code = null) {
        $log = $this->getLog();
        if ($log) {
            $log->logDebug('Executing command', $command);
        }

        $executeException = null;

        try {
            $output = parent::executeCommand($command, $code);
        } catch (SystemException $exception) {
            $executeException = $exception;
        }

        if ($log) {
            $log->logDebug('Executed command', $command . ' returned ' . $code);
        }

        if ($executeException) {
            throw $executeException;
        }

        return $output;
    }

}
