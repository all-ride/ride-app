<?php

namespace ride\application\system\init;

use ride\application\system\System;

use ride\library\system\exception\SystemException;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;
use ride\library\Autoloader;

/**
 * Abstract implementation to initialize the Ride system
 */
abstract class AbstractSystemInitializer implements SystemInitializer {

    /**
     * Collected module directories
     * @var array
     */
    protected $modules = array();

    /**
     * Adds a module directory to the system
     * @param \ride\library\system\file\File $directory Directory to add
     * @param \ride\library\Autoloader $autoloader When provided and a
     * subdirectory src exists
     * @param boolean $processModule Set to false to skip looking for ride.json
     * @return null
     */
    protected function addModuleDirectory(File $directory, Autoloader $autoloader = null, $processModule = true) {
        if ($processModule) {
            $module = $this->getModuleFromPath($directory);
            if ($module) {
                $this->modules[$module['level']][] = $module['path'];
            }
        }

        if ($autoloader) {
            $srcDirectory = $directory->getChild(System::DIRECTORY_SOURCE);
            if ($srcDirectory->exists()) {
                $autoloader->addIncludePath($srcDirectory->getAbsolutePath());
            }
        }
    }

    /**
     * Adds the collected include directories of the modules to the file browser
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    protected function addIncludeDirectories(FileBrowser $fileBrowser) {
        ksort($this->modules);
        $modules = array_reverse($this->modules, true);

        foreach ($modules as $level => $includeDirectories) {
            foreach ($includeDirectories as $includeDirectory) {
                $fileBrowser->addIncludeDirectory($includeDirectory);
            }
        }
    }

    /**
     * Gets the module definition from a directory path
     * @param \ride\library\system\file\File $path
     * @throws \ride\library\system\exception\SystemException when the
     * ride.json could not be parsed
     * @return null|array
     */
    protected function getModuleFromPath(File $path) {
        $rideFile = $path->getChild('ride.json');
        if (!$rideFile->exists()) {
            // not a ride module
            return null;
        }

        // module defined in ride.json
        $module = json_decode($rideFile->read(), true);
        if ($module === null) {
            throw new SystemException('Could not parse ' . $rideFile);
        }

        // set a default level of the module
        if (!isset($module['level'])) {
            $module['level'] = 0;
        }

        $module['path'] = $path;

        return $module;
    }

}
