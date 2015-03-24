<?php

namespace ride\application\decorator;

use ride\library\decorator\Decorator;
use ride\library\system\file\File;

/**
 * Decorator which provides the size in bytes for a given file.
 */
class FileSizeDecorator implements Decorator {

    /**
     * Attempts to find the size of the provided file
     * @param mixed $value Value to decorate
     * @return mixed Size in bytes of the file, original value otherwise when no
     * File provided
     */
    public function decorate($value) {
        if ($value instanceof File) {
            return $value->getSize();
        }

        return $value;
    }

}
