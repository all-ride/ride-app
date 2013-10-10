<?php

namespace pallo\app;

/**
 * Interface for a Pallo application
 */
interface App {

    /**
     * Service the application
     * @return null
     */
    public function service();

}