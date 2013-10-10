<?php

namespace pallo\application;

/**
 * Interface for a Pallo application
 */
interface Application {

    /**
     * Service the application
     * @return null
     */
    public function service();

}