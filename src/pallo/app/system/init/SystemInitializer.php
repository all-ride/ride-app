<?php

namespace pallo\app\system\init;

use pallo\app\system\System;

/**
 * Interface to initialize the Pallo system
 */
interface SystemInitializer {

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param pallo\app\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system);

}