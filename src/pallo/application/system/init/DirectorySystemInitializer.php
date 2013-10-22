<?php

namespace pallo\application\system\init;

use pallo\application\system\System;

use pallo\library\system\exception\SystemException;
use pallo\library\Autoloader;

/**
 * Implementation to initialize the Pallo system with modules from a provided
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
     * @param pallo\application\system\System $system Instance of the system
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

        $autoloader = new Autoloader();
        $autoloader->registerAutoloader();

        // set the include directories
        $includePaths = array();

        // read installed packages from composer
        $directories = $directory->read();
        foreach ($directories as $directory) {
            $module = $this->getModuleFromPath($directory);
            if ($module) {
                $includePaths[$module['level']][] = $module['path'];
            }

            $autoloader->addIncludePath($directory->getChild('src')->getAbsolutePath());
        }

        // add paths of the modules to the file browser
        ksort($includePaths);
        $includePaths = array_reverse($includePaths, true);

        foreach ($includePaths as $level => $includeDirectories) {
            foreach ($includeDirectories as $includeDirectory) {
                $fileBrowser->addIncludeDirectory($includeDirectory);
            }
        }
    }

}