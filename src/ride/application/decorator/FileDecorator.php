<?php

namespace ride\application\decorator;

use ride\library\decorator\Decorator;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

/**
 * Decorator which provides File instance for a path
 */
class FileDecorator implements Decorator {

    /**
     * Instance of the file browser
     * @var \ride\library\system\file\browser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Constructs a new file size decorator
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser) {
        $this->fileBrowser = $fileBrowser;
    }

    /**
     * Attempts to find the File instance of the provided file
     * @param mixed $value Value to decorate
     * @return mixed File instance if resolved, original value otherwise
     */
    public function decorate($value) {
        if ($value instanceof File) {
            return $value;
        } elseif (!is_string($value)) {
            return $value;
        }

        $file = $this->fileBrowser->getFile($value);
        if (!$file) {
            $file = $this->fileBrowser->getPublicFile($value);
        }

        if ($file) {
            return $file;
        }

        return $value;
    }

}
