<?php

namespace pallo\application\system\init;

use pallo\application\system\System;

use pallo\library\system\exception\SystemException;

/**
 * Composer implementation to initialize the Pallo system
 */
class ComposerSystemInitializer extends AbstractSystemInitializer {

    /**
     * Path to composer.lock
     * @var string
     */
    private $lockFile;

    /**
     * Constructs a new composer system initializer
     * @param string $lockFile Path to composer.lock
     * @return null
     */
    public function __construct($lockFile = null) {
        if ($lockFile === null) {
            $this->lockFile = __DIR__ . '/../../../../../../../../composer.lock';
        } else {
            $this->lockFile = $lockFile;
        }
    }

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param pallo\application\system\System $system Instance of the system
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