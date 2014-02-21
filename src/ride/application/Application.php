<?php

namespace ride\application;

/**
 * Interface for a Ride application
 */
interface Application {

    /**
     * Service the application
     * @return null
     */
    public function service();

}