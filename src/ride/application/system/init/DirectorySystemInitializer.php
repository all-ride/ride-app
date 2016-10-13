<?php

namespace ride\application\system\init;

use ride\application\system\System;

use ride\library\Autoloader;

/**
 * Implementation to initialize the Ride system with modules from a provided
 * directory
 */
class DirectorySystemInitializer extends AbstractSystemInitializer {

    /**
     * Path to the modules directory
     * @var string
     */
    private $directory;

    /**
     * Constructs a new system initializer
     * @param string $directory Path to the modules directory
     * @return null
     */
    public function __construct($directory) {
        $this->directory = $directory;
    }

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system) {
        $fileSystem = $system->getFileSystem();

        $directory = $fileSystem->getFile($this->directory);
        if (!$directory->exists() || !$directory->isDirectory()) {
            return;
        }

        $directory = $fileSystem->getFile($directory->getAbsolutePath());
        $fileBrowser = $system->getFileBrowser();

        $autoloader = $system->getAutoloader();
        if (!$autoloader) {
            $autoloader = null;
        }

        $directories = $directory->read();
        foreach ($directories as $directory) {
            $this->addModuleDirectory($directory, $autoloader);
        }

        $this->addIncludeDirectories($fileBrowser);
    }

}
