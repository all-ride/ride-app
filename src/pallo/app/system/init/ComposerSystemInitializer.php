<?php

namespace pallo\app\system\init;

use pallo\app\system\System;

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
        $moduleDirectories = array();

        $composerFile = $system->getFileSystem()->getFile(__DIR__ . '/../../../../../../../composer.lock');
        if (!$composerFile->exists()) {
            return;
        }

        $composer = json_decode($composerFile->read(), true);
        print_r($composer);
    }

}