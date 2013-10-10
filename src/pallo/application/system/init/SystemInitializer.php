<?php

namespace pallo\application\system\init;

use pallo\application\system\System;

/**
 * Interface to initialize the Pallo system
 */
interface SystemInitializer {

    /**
     * Initializes the system eg. by setting the file browser paths
     * @param pallo\application\system\System $system Instance of the system
     * @return null
     */
    public function initializeSystem(System $system);

}