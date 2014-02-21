<?php

namespace ride\application\system\init;

use ride\library\system\exception\SystemException;
use ride\library\system\file\File;

/**
 * Abstract implementation to initialize the Ride system
 */
abstract class AbstractSystemInitializer implements SystemInitializer {

    /**
     * Gets the module definition from a path
     * @param ride\library\system\file\File $path
     * @throws ride\library\system\exception\SystemException when the
     * ride.json could not be parsed
     * @return null|array
     */
    protected function getModuleFromPath(File $path) {
        $rideFile = $path->getChild('ride.json');
        if ($rideFile->exists()) {
            // package defined in ride.json
            $module = json_decode($rideFile->read(), true);
            if ($module === null) {
                throw new SystemException('Could not parse ' . $rideFile);
            }
        } elseif (isset($package['extra']['ride'])) {
            // package defined in composer.json
            $module = $package['extra']['ride'];
        } else {
            // not a ride package
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