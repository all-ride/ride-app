<?php

namespace ride\application\system\init;

use ride\application\system\System;

/**
 * Composer implementation to initialize the Ride system
 */
class ComposerSystemInitializer extends AbstractCacheableSystemInitializer {

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
     * Performs the initialization of the system eg. by setting the file browser
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    protected function performInitializeSystem(System $system) {
        $fileSystem = $system->getFileSystem();

        $composerFile = $fileSystem->getFile($this->lockFile);
        if (!$composerFile->exists()) {
            // not in a composer installation
            return;
        }

        // get the normalized root path
        $root = $fileSystem->getFile($composerFile->getParent()->getAbsolutePath());

        // set the application and public directory
        $applicationDirectory = $root->getChild('application');
        $publicDirectory = $root->getChild('public');

        $fileBrowser = $system->getFileBrowser();
        $fileBrowser->setApplicationDirectory($applicationDirectory);
        $fileBrowser->setPublicDirectory($publicDirectory);

        // retrieve autoloader for application and custom modules
        $autoloader = $system->getAutoloader();
        if (!$autoloader) {
            $autoloader = null;
        }
        $this->addModuleDirectory($applicationDirectory, $autoloader, false);

        // read installed packages from composer
        $composer = json_decode($composerFile->read(), true);
        foreach ($composer['packages'] as $package) {
            $directory = $root->getChild('vendor/' . $package['name']);

            $this->addModuleDirectory($directory);
        }

        // read modules from module directory
        if ($this->modulesDirectory) {
            $modulesDirectory = $fileSystem->getFile($this->modulesDirectory);
            if ($modulesDirectory->isDirectory()) {
                $moduleDirectories = $modulesDirectory->read();
                foreach ($moduleDirectories as $moduleDirectory) {
                    $this->addModuleDirectory($moduleDirectory, $autoloader);
                }
            }
        }

        $this->addIncludeDirectories($fileBrowser);
    }

}
