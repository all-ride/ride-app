<?php

namespace ride\application\dependency\io;

use ride\library\config\io\AbstractIO;
use ride\library\dependency\exception\DependencyException;
use ride\library\dependency\Dependency;
use ride\library\dependency\DependencyCall;
use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyContainer;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

use \DOMDocument;
use \DOMElement;

/**
 * Implementation to get a dependency container based on XML files
 */
class XmlDependencyIO extends AbstractIO implements DependencyIO {

    /**
     * The file name
     * @var string
     */
    const FILE = 'dependencies.xml';

    /**
     * Name of the dependency tag
     * @var string
     */
    const TAG_DEPENDENCY = 'dependency';

    /**
     * Name of the call tag
     * @var string
     */
    const TAG_CALL = 'call';

    /**
     * Name of the argument tag
     * @var string
     */
    const TAG_ARGUMENT = 'argument';

    /**
     * Name of the property tag
     * @var string
     */
    const TAG_PROPERTY = 'property';

    /**
     * Name of the interface attribute
     * @var string
     */
    const ATTRIBUTE_INTERFACE = 'interface';

    /**
     * Name of the class attribute
     * @var string
     */
    const ATTRIBUTE_CLASS = 'class';

    /**
     * Name of the extends attribute
     * @var string
     */
    const ATTRIBUTE_EXTENDS = 'extends';

    /**
     * Name of the id attribute
     * @var string
     */
    const ATTRIBUTE_ID = 'id';

    /**
     * Name of the method attribute
     * @var string
     */
    const ATTRIBUTE_METHOD = 'method';

    /**
     * Name of the name attribute
     * @var string
     */
    const ATTRIBUTE_NAME = 'name';

    /**
     * Name of the type attribute
     * @var string
     */
    const ATTRIBUTE_TYPE = 'type';

    /**
     * Name of the value attribute
     * @var string
     */
    const ATTRIBUTE_VALUE = 'value';

    /**
     * Constructs a new XML dependency IO
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @param string $environment
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, $path = null) {
        parent::__construct($fileBrowser, self::FILE, $path);
    }

    /**
     * Gets the dependency container
     * @param \ride\library\system\System $ride Instance of ride
     * @return \ride\library\dependency\DependencyContainer
     */
    public function getDependencyContainer() {
        $container = new DependencyContainer();

        $path = null;
        if ($this->path) {
            $path = $this->path . File::DIRECTORY_SEPARATOR;
        }

        $files = array_reverse($this->fileBrowser->getFiles($path . $this->file));
        foreach ($files as $file) {
            $this->readDependencies($container, $file);
        }

        if ($this->environment) {
            $path .= $this->environment . File::DIRECTORY_SEPARATOR;

            $files = array_reverse($this->fileBrowser->getFiles($path . $this->file));
            foreach ($files as $file) {
                $this->readDependencies($container, $file);
            }
        }

        return $container;
    }

    /**
     * Reads the dependencies from the provided file and adds them to the
     * provided container
     * @param \ride\library\dependency\DependencyContainer $container
     * @param \ride\library\system\file\File $file
     * @return null
     */
    private function readDependencies(DependencyContainer $container, File $file) {
        $dom = new DOMDocument();
        $dom->load($file);

        $dependencyElements = $dom->getElementsByTagName(self::TAG_DEPENDENCY);
        foreach ($dependencyElements as $dependencyElement) {
            $interface = $dependencyElement->getAttribute(self::ATTRIBUTE_INTERFACE);
            $className = $dependencyElement->getAttribute(self::ATTRIBUTE_CLASS);
            $id = $dependencyElement->getAttribute(self::ATTRIBUTE_ID);
            if (!$id) {
                $id = null;
            }

            $extends = $dependencyElement->getAttribute(self::ATTRIBUTE_EXTENDS);
            if ($extends) {
                if (!$interface) {
                    $interface = $className;
                }

                $dependencies = $container->getDependencies($interface);
                if (isset($dependencies[$extends])) {
                    $dependency = clone $dependencies[$extends];
                    $dependency->setId($id);
                    if ($className) {
                        $dependency->setClassName($className);
                    }
                } else {
                    throw new DependencyException('No dependency set to extend interface ' . $interface . ' with id ' . $extends);
                }
            } else {
                $dependency = new Dependency($className, $id);
            }

            $this->readCalls($dependencyElement, $dependency);
            $this->readInterfaces($dependencyElement, $dependency, $interface, $className);

            $container->addDependency($dependency);
        }
    }

    /**
     * Reads the calls from the provided dependency element and adds them to
     * the dependency instance
     * @param DOMElement $dependencyElement
     * @param \ride\library\dependency\Dependency $dependency
     * @return null
     */
    private function readCalls(DOMElement $dependencyElement, Dependency $dependency) {
        $calls = array();

        $callElements = $dependencyElement->getElementsByTagName(self::TAG_CALL);
        foreach ($callElements as $callElement) {
            $methodName = $callElement->getAttribute(self::ATTRIBUTE_METHOD);

            $call = new DependencyCall($methodName);

            $argumentElements = $callElement->getElementsByTagName(self::TAG_ARGUMENT);
            foreach ($argumentElements as $argumentElement) {
                $name = $argumentElement->getAttribute(self::ATTRIBUTE_NAME);
                $type = $argumentElement->getAttribute(self::ATTRIBUTE_TYPE);
                $properties = array();

                $propertyElements = $argumentElement->getElementsByTagName(self::TAG_PROPERTY);
                foreach ($propertyElements as $propertyElement) {
                    $propertyName = $propertyElement->getAttribute(self::ATTRIBUTE_NAME);
                    $propertyValue = $propertyElement->getAttribute(self::ATTRIBUTE_VALUE);

                    $properties[$propertyName] = $propertyValue;
                }

                $call->addArgument(new DependencyCallArgument($name, $type, $properties));
            }

            $dependency->addCall($call);
        }
    }

    /**
     * Reads the interfaces from the provided dependency element and adds them
     * to the dependency instance
     * @param DOMElement $dependencyElement
     * @param \ride\library\dependency\Dependency $dependency
     * @param string $interface Class name of the interface
     * @param string $className Class name of the instance
     * @return null
     */
    private function readInterfaces(DOMElement $dependencyElement, Dependency $dependency, $interface, $className) {
        $interfaces = array();

        $interfaceElements = $dependencyElement->getElementsByTagName(self::ATTRIBUTE_INTERFACE);
        foreach ($interfaceElements as $interfaceElement) {
            $interfaceName = $interfaceElement->getAttribute(self::ATTRIBUTE_NAME);

            $interfaces[$interfaceName] = true;
        }

        if ($interface) {
            $interfaces[$interface] = true;
        }

        if (!$interfaces) {
            $interfaces[$className] = true;
        }

        $dependency->setInterfaces($interfaces);
    }

}