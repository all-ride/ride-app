<?php

namespace ride\application\dependency\io;

use ride\library\config\io\AbstractIO;
use ride\library\config\parser\Parser;
use ride\library\dependency\exception\DependencyException;
use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyCall;
use ride\library\dependency\DependencyConstructCall;
use ride\library\dependency\DependencyContainer;
use ride\library\dependency\Dependency;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

use \Exception;

/**
 * Implementation to get a dependency container based on the provided parser
 */
class ParserDependencyIO extends AbstractIO implements DependencyIO {

    /**
     * Parser for the configuration files
     * @var \ride\library\config\parser\Parser
     */
    protected $parser;

    /**
     * Constructs a new XML dependency IO
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @param \ride\library\config\parser\Parser $parser
     * @param string $file
     * @param string $path
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, Parser $parser, $file, $path = null) {
        parent::__construct($fileBrowser, $file, $path);

        $this->parser = $parser;
    }

    /**
     * Gets the dependency container
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
    protected function readDependencies(DependencyContainer $container, File $file) {
        try {
            $content = $file->read();
            $content = $this->parser->parseToPhp($content);

            if (!isset($content['dependencies'])) {
                return;
            }

            foreach ($content['dependencies'] as $dependencyStruct) {
                $constructCall = null;
                $className = null;

                if (isset($dependencyStruct['factory'])) {
                    $constructCallStruct = $dependencyStruct['factory'];

                    unset($dependencyStruct['factory']);

                    // construct call
                    if (!isset($constructCallStruct['interface'])) {
                        throw new DependencyException('Could not read factory: interface is not set');
                    }
                    if (!isset($constructCallStruct['method'])) {
                        throw new DependencyException('Could not read factory: method is not set');
                    }
                    if (!isset($constructCallStruct['id'])) {
                        $constructCallStruct['id'] = null;
                    }

                    $constructCall = new DependencyConstructCall($constructCallStruct['interface'], $constructCallStruct['method'], $constructCallStruct['id']);

                    $this->readArguments($constructCallStruct, $constructCall);
                } elseif (isset($dependencyStruct['class'])) {
                    $className = $dependencyStruct['class'];

                    unset($dependencyStruct['class']);
                }

                if (isset($dependencyStruct['id'])) {
                    $id = $dependencyStruct['id'];

                    unset($dependencyStruct['id']);
                } else {
                    $id = null;
                }

                if (isset($dependencyStruct['extends'])) {
                    if (isset($dependencyStruct['interfaces'])) {
                        if (is_array($dependencyStruct['interfaces'])) {
                            $interface = reset($dependencyStruct['interfaces']);
                        } else {
                            $interface = $dependencyStruct['interfaces'];
                        }
                    } elseif ($className) {
                        $interface = $className;
                    } else {
                        throw new DependencyException('Could not extend an interface from call ' . $constructCall->getInterface() . '->' . $constructCall->getMethodName() . '() with id ' . $dependencyStruct['extends'] . ': no interfaces set');
                    }

                    $dependencies = $container->getDependencies($interface);
                    if (isset($dependencies[$dependencyStruct['extends']])) {
                        $dependency = clone $dependencies[$dependencyStruct['extends']];
                        $dependency->setId($id);

                        if ($constructCall) {
                            $dependency->setConstructCall($constructCall);
                        } elseif ($className) {
                            $dependency->setClassName($className);
                        }
                    } else {
                        throw new DependencyException('Could not extend interface ' . $interface . ' with id ' . $dependencyStruct['extends'] . ': extended dependency not set');
                    }

                    unset($dependencyStruct['extends']);
                } elseif ($constructCall) {
                    $dependency = new Dependency($constructCall, $id);
                } else {
                    $dependency = new Dependency($className, $id);
                }

                $this->readCalls($dependencyStruct, $dependency);
                $this->readInterfaces($dependencyStruct, $dependency);
                $this->readTags($dependencyStruct, $dependency);

                if ($dependencyStruct) {
                    throw new DependencyException('Could not add dependency for ' . $className . ': provided properties are invalid (' . implode(', ', array_keys($dependencyStruct)) . ')');
                }

                $container->addDependency($dependency);
            }
        } catch (Exception $exception) {
            throw new DependencyException('Could not read dependencies from ' . $file, 0, $exception);
        }
    }

    /**
     * Reads the calls from the provided dependency structure and adds them to
     * the dependency instance
     * @param array $dependencyStruct
     * @param \ride\library\dependency\Dependency $dependency
     * @return null
     */
    protected function readCalls(array &$dependencyStruct, Dependency $dependency) {
        if (!isset($dependencyStruct['calls'])) {
            return;
        }

        if (!is_array($dependencyStruct['calls'])) {
            throw new DependencyException('Could not read calls for ' . $dependency->getClassName() . ' with id ' . $dependency->getId() . ': calls is not an array');
        }

        foreach ($dependencyStruct['calls'] as $callStruct) {
            if (is_string($callStruct)) {
                // plain string call
                $call = new DependencyCall($callStruct);
            } else {
                // detailed call
                if (!isset($callStruct['method'])) {
                    throw new DependencyException('Could not read call for ' . $dependency->getClassName() . ' with id ' . $dependency->getId() . ': method is not set');
                }

                $call = new DependencyCall($callStruct['method']);

                $this->readArguments($callStruct, $call);
            }

            $dependency->addCall($call);
        }

        unset($dependencyStruct['calls']);
    }

    /**
     * Reads the arguments from the provided call structure and adds them to
     * the call instance
     * @param array $callStruct
     * @param \ride\library\dependency\DependencyCall $dependencyCall
     * @return null
     */
    protected function readArguments(array $callStruct, DependencyCall $dependencyCall) {
        if (!isset($callStruct['arguments'])) {
            return;
        }

        if (!is_array($callStruct['arguments'])) {
            throw new DependencyException('Could not read arguments for ' . $dependencyCall->getMethodName() . ': arguments is not an array');
        }

        foreach ($callStruct['arguments'] as $argumentStruct) {
            if (!isset($argumentStruct['name'])) {
                throw new DependencyException('Could not read arguments for ' . $dependencyCall->getMethodName() . ': name not set');
            }

            if (!isset($argumentStruct['type'])) {
                throw new DependencyException('Could not read arguments for ' . $dependencyCall->getMethodName() . ': type not set');
            }

            $properties = array();
            if (isset($argumentStruct['properties'])) {
                if (!is_array($argumentStruct['properties'])) {
                    throw new DependencyException('Could not read properties for argument ' . $argumentStruct['name'] . ' in method ' . $dependencyCall->getMethodName() . ': properties is not an array');
                }

                $properties = $argumentStruct['properties'];
            }

            $dependencyCall->addArgument(new DependencyCallArgument($argumentStruct['name'], $argumentStruct['type'], $properties));
        }
    }

    /**
     * Reads the interfaces from the provided dependency structure and adds
     * them to the dependency instance
     * @param array $dependencyStruct
     * @param \ride\library\dependency\Dependency $dependency
     * @return null
     */
    protected function readInterfaces(array &$dependencyStruct, Dependency $dependency) {
        $interfaces = array();

        if (isset($dependencyStruct['interfaces'])) {
            if (is_string($dependencyStruct['interfaces'])) {
                $interfaces[$dependencyStruct['interfaces']] = true;
            } elseif (!is_array($dependencyStruct['interfaces'])) {
                throw new DependencyException('Could not read interfaces for ' . $dependency->getClassName() . ' with id ' . $dependency->getId() . ': interfaces is not a string or an array');
            } else {
                foreach ($dependencyStruct['interfaces'] as $interface) {
                    $interfaces[$interface] = true;
                }
            }

            unset($dependencyStruct['interfaces']);
        }

        $className = $dependency->getClassName();
        if (!$interfaces && !$className) {
            $constructCall = $dependency->getConstructCall();

            throw new DependencyException('Could not read interfaces for ' . $constructCall->getInterface() . '->' . $constructCall->getMethodName() . '() with id ' . $dependency->getId() . ': interfaces is required when using a factory');
        } elseif (!$interfaces) {
            $interfaces[$className] = true;
        }

        $dependency->setInterfaces($interfaces);
    }

    /**
     * Reads the tags from the provided dependency structure and adds
     * them to the dependency instance
     * @param array $dependencyStruct
     * @param \ride\library\dependency\Dependency $dependency
     * @return null
     */
    protected function readTags(array &$dependencyStruct, Dependency $dependency) {
        if (!isset($dependencyStruct['tags'])) {
            return;
        }

        if (is_string($dependencyStruct['tags'])) {
            $dependency->addTag($dependencyStruct['tags']);
        } elseif (!is_array($dependencyStruct['tags'])) {
            throw new DependencyException('Could not read tags for ' . $dependency->getClassName() . ' with id ' . $dependency->getId() . ': tags is not a string or an array');
        } else {
            foreach ($dependencyStruct['tags'] as $tag) {
                $dependency->addTag($tag);
            }
        }

        unset($dependencyStruct['tags']);
    }

}
