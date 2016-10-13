<?php

namespace ride\application\system\init;

use ride\application\system\System;

/**
 * Interface to initialize the Ride system
 */
interface SystemInitializer {

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param \ride\application\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system);

}
