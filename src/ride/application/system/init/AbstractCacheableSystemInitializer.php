<?php

namespace ride\application\system\init;

use ride\application\system\System;

use ride\library\system\exception\SystemException;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;
use ride\library\Autoloader;

/**
 * Abstract cacheable implementation to initialize the Ride system
 */
abstract class AbstractCacheableSystemInitializer extends AbstractSystemInitializer implements CacheableSystemInitializer {

    /**
     * Path to the cache file
     * @var string|\ride\library\system\file\File
     */
    private $cacheFile = null;

    /**
     * Flag to see if the cache is enabled
     * @var boolean
     */
    private $isCacheEnabled = false;

    /**
     * Collected autoload directories
     * @var array
     */
    private $autoload = array();

    /**
     * Collected application directory
     * @var string
     */
    private $application = null;

    /**
     * Collected application directory
     * @var string
     */
    private $public = null;

    /**
     * Enable the cache by setting a cache file
     * @param string $cacheFile Path to the cache file
     * @return null
     */
    public function setCacheFile($cacheFile) {
        if ($cacheFile === null) {
            $this->isCacheEnabled = false;
        } else {
            $this->isCacheEnabled = true;
        }

        $this->cacheFile = $cacheFile;
    }

    /**
     * Enables or disables the cache of the system initialization process
     * @param boolean $isCacheEnabled
     * @return null
     */
    public function setIsCacheEnabled($isCacheEnabled) {
        $this->isCacheEnabled = $isCacheEnabled;
    }

    /**
     * Gets whether the cache is enabled
     * @return boolean
     */
    public function isCacheEnabled() {
        return $this->isCacheEnabled;
    }

    /**
     * Clears the cache
     * @return boolean
     */
    public function clearCache() {
        if ($this->cacheFile && $this->cacheFile->exists()) {
            $this->cacheFile->delete();

            return true;
        }

        return false;
    }

    /**
     * Warms the cache
     * @return boolean
     */
    public function warmCache() {
        if (!$this->cacheFile) {
            return false;
        }

        // generate the PHP code for the obtained container
        $php = $this->generatePhp();

        // make sure the parent directory of the script exists
        $parent = $this->cacheFile->getParent();
        $parent->create();

        // write the PHP code to file
        $this->cacheFile->write($php);

        return true;
    }

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system) {
        if ($this->cacheFile) {
            $this->cacheFile = $system->getFileSystem()->getFile($this->cacheFile);

            if ($this->isCacheEnabled && $this->performInitializeSystemFromCache($system, $this->cacheFile)) {
                return;
            }
        }

        $this->performInitializeSystem($system);
    }

    /**
     * Performs the initialization of the system eg. by setting the file browser
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    abstract protected function performInitializeSystem(System $system);

    /**
     * Performs the initialization of the system eg. from the cache
     * @param \ride\application\system\System $system Instance of the system
     * @param \ride\library\system\file\File $cacheFile Cache file
     * @return null
     */
    private function performInitializeSystemFromCache(System $system, File $cacheFile = null) {
        if (!$cacheFile || !$cacheFile->exists()) {
            return false;
        }

        include $cacheFile->getPath();

        $hasModules = isset($modules);
        $hasAutoload = isset($autoload);
        $hasApplication = isset($application);
        $hasPublic = isset($public);

        if (!$hasModules && !$hasAutoload && !$hasApplication && !$hasPublic) {
            return false;
        }

        $fileSystem = $system->getFileSystem();
        $fileBrowser = $system->getFileBrowser();

        if ($hasApplication) {
            $applicationDirectory = $fileSystem->getFile($application);

            $fileBrowser->setApplicationDirectory($applicationDirectory);
        }

        if ($hasPublic) {
            $publicDirectory = $fileSystem->getFile($public);

            $fileBrowser->setPublicDirectory($publicDirectory);
        }

        if ($hasModules) {
            foreach ($modules as $level => $includeDirectories) {
                foreach ($includeDirectories as $index => $includeDirectory) {
                    $modules[$level][$index] = $fileSystem->getFile($includeDirectory);
                }
            }

            $this->modules = $modules;

            $this->addIncludeDirectories($fileBrowser);
        }

        $autoloader = $system->getAutoloader();
        if ($hasAutoload && $autoloader) {
            foreach ($autoload as $includePath => $null) {
                $autoloader->addIncludePath($includePath);
            }

            $this->autoload = $autoload;
        }

        return true;
    }

    /**
     * Generates a PHP source file for the collected data
     * @return string
     */
    protected function generatePhp() {
        $modules = $this->modules;

        foreach ($modules as $level => $includeDirectories) {
            foreach ($includeDirectories as $index => $includeDirectory) {
                $modules[$level][$index] = $includeDirectory->getAbsolutePath();
            }
        }

        $output = "<?php\n\n";
        $output .= "/*\n";
        $output .= " * This file is generated by ride\\application\\system\\init\\AbstractCacheableSystemInitializer.\n";
        $output .= " */\n";
        $output .= "\n";
        $output .= '$modules = ' . var_export($modules, true) . ";\n";
        $output .= '$autoload = ' . var_export($this->autoload, true) . ";\n";
        $output .= '$application = ' . var_export($this->application, true) . ";\n";
        $output .= '$public = ' . var_export($this->public, true) . ";\n";

        return $output;
    }

    /**
     * Adds a module directory to the system
     * @param \ride\library\system\file\File $directory Directory to add
     * @param \ride\library\Autoloader $autoloader When provided and a
     * subdirectory src exists, it's added
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

        if (!$autoloader) {
            return;
        }

        $srcDirectory = $directory->getChild(System::DIRECTORY_SOURCE);
        if (!$srcDirectory->exists()) {
            return;
        }

        $includePath = $srcDirectory->getAbsolutePath();

        $autoloader->addIncludePath($includePath);

        $this->autoload[$includePath] = true;
    }

    /**
     * Adds the collected include directories of the modules to the file browser
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    protected function addIncludeDirectories(FileBrowser $fileBrowser) {
        parent::addIncludeDirectories($fileBrowser);

        $applicationDirectory = $fileBrowser->getApplicationDirectory();
        if ($applicationDirectory) {
            $this->application = $applicationDirectory->getAbsolutePath();
        }

        $publicDirectory = $fileBrowser->getPublicDirectory();
        if ($publicDirectory) {
            $this->public = $publicDirectory->getAbsolutePath();
        }
    }

}
