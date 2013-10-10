<?php

namespace pallo\app\system\init;

use pallo\app\system\System;

use pallo\library\system\exception\SystemException;

/**
 * Composer implementation to initialize the Pallo system
 */
class ComposerSystemInitializer implements SystemInitializer {

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param pallo\app\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system) {
        $composerFile = $system->getFileSystem()->getFile(__DIR__ . '/../../../../../../../../composer.lock');
        if (!$composerFile->exists()) {
            // not in a composer installation
            return;
        }

        $rootFile = $composerFile->getParent();

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

            $palloFile = $path->getChild('pallo.json');
            if ($palloFile->exists()) {
                // package defined in pallo.json
                $module = json_decode($palloFile->read(), true);
                if ($module === null) {
                    throw new SystemException('Could not parse ' . $palloFile);
                }
            } elseif (isset($package['extra']['pallo'])) {
                // package defined in composer.json
                $module = $package['extra']['pallo'];
            } else {
                // not a pallo package
                continue;
            }

            // get the level of the module
            if (isset($module['level'])) {
                $level = $module['level'];
            } else {
                $level = 0;
            }

            $includePaths[$level][] = $path;
        }

        // add paths of the modules to the file browser
        ksort($includePaths);

        foreach ($includePaths as $level => $includeDirectories) {
            foreach ($includeDirectories as $includeDirectory) {
                $fileBrowser->addIncludeDirectory($includeDirectory);
            }
        }
    }

}