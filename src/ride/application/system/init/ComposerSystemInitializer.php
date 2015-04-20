<?php

namespace ride\application\system\init;

use ride\application\system\System;

use ride\library\Autoloader;

/**
 * Composer implementation to initialize the Ride system
 */
class ComposerSystemInitializer extends AbstractSystemInitializer {

    /**
     * Path to composer.lock
     * @var string
     */
    private $lockFile;

    /**
     * Path to the modules directory
     * @var string
     */
    private $modulesDirectory;

    /**
     * Constructs a new composer system initializer
     * @param string $lockFile Path to composer.lock
     * @return null
     */
    public function __construct($lockFile = null, $modulesDirectory = null) {
        if ($lockFile === null) {
            $this->lockFile = __DIR__ . '/../../../../../../../../composer.lock';
        } else {
            $this->lockFile = $lockFile;
        }

        $this->modulesDirectory = $modulesDirectory;
    }

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system) {
        $fileSystem = $system->getFileSystem();

        $composerFile = $fileSystem->getFile($this->lockFile);
        if (!$composerFile->exists()) {
            // not in a composer installation
            return;
        }

        // get the normalized root path
        $rootFile = $fileSystem->getFile($composerFile->getParent()->getAbsolutePath());

        // set the application and public directory
        $applicationDirectory = $rootFile->getChild('application');
        $publicDirectory = $rootFile->getChild('public');

        $fileBrowser = $system->getFileBrowser();
        $fileBrowser->setApplicationDirectory($applicationDirectory);
        $fileBrowser->setPublicDirectory($publicDirectory);

        // create autoloader for application and custom modules
        $autoloader = new Autoloader();
        $autoloader->registerAutoloader(true);

        $applicationSrcDirectory = $applicationDirectory->getChild('src');
        if ($applicationSrcDirectory->exists()) {
            $autoloader->addIncludePath($applicationSrcDirectory->getAbsolutePath());
        }

        // set the include directories
        $includePaths = array();

        // read installed packages from composer
        $composer = json_decode($composerFile->read(), true);
        foreach ($composer['packages'] as $package) {
            $path = $rootFile->getChild('vendor/' . $package['name']);

            $module = $this->getModuleFromPath($path);
            if ($module) {
                $includePaths[$module['level']][] = $module['path'];
            }
        }

        // read modules from module directory
        if ($this->modulesDirectory) {
            $modulesDirectory = $fileSystem->getFile($this->modulesDirectory);
            if ($modulesDirectory->isDirectory()) {
                $moduleDirectories = $modulesDirectory->read();
                foreach ($moduleDirectories as $moduleDirectory) {
                    $module = $this->getModuleFromPath($moduleDirectory);
                    if ($module) {
                        $includePaths[$module['level']][] = $module['path'];
                    }

                    $moduleSrcDirectory = $moduleDirectory->getChild('src');
                    if ($moduleSrcDirectory->exists()) {
                        $autoloader->addIncludePath($moduleSrcDirectory->getAbsolutePath());
                    }
                }
            }
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
