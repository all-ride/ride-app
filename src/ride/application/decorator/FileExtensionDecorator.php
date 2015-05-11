<?php

namespace ride\application\decorator;

use ride\library\decorator\Decorator;
use ride\library\system\file\File;

/**
 * Decorator which provides the file extension of a given file
 */
class FileExtensionDecorator implements Decorator {

    /**
     * Attempts to find the extension of a provided file
     * @param mixed $value Value to decorate
     * @return mixed File extension if applicable, original value otherwise when
     * no File provided
     */
    public function decorate($value) {
        if ($value instanceof File) {
            return $value->getExtension();
        }

        return $value;
    }

}
