<?php

namespace ride\application\dependency\io;

/**
 * Interface to get a dependency container
 */
interface DependencyIO {

    /**
     * Gets a dependency container
     * @return ride\library\dependency\DependencyContainer
     */
    public function getDependencyContainer();

}