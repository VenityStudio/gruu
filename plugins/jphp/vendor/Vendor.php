<?php

namespace plugins\jphp\vendor;


use php\io\File;
use php\lib\fs;

class Vendor
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @param string $directory
     */
    public function addDirectory(string $directory) {
        fs::scan($directory, function (File $file) {
            if (fs::ext($file) == "jar") $this->files[] = $file->getCanonicalFile()->getAbsolutePath();
        });
    }

    /**
     * @return array
     */
    public function getFiles(): array {
        return $this->files;
    }
}