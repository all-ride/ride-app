<?php

namespace pallo\application\system\init;

use pallo\library\system\exception\SystemException;
use pallo\library\system\file\File;

/**
 * Abstract implementation to initialize the Pallo system
 */
abstract class AbstractSystemInitializer implements SystemInitializer {

    /**
     * Gets the module definition from a path
     * @param pallo\library\system\file\File $path
     * @throws pallo\library\system\exception\SystemException when the
     * pallo.json could not be parsed
     * @return null|array
     */
    protected function getModuleFromPath(File $path) {
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
            return null;
        }

        // get the level of the module
        if (!isset($module['level'])) {
            $module['level'] = 0;
        }

        $module['path'] = $path;

        return $module;
    }

}