<?php

namespace gruu\utils;


use php\io\File;
use php\lib\fs;

class FileSystem
{
    /**
     * @param string $path
     * @param string $intoDir
     * @param bool $ignoreErrs
     */
    public static function copy(string $path, string $intoDir, bool $ignoreErrs = false) {
        if (fs::isFile($path)) {
            if (fs::copy($path, "$intoDir/" . fs::name($path), 1024 * 128) < 0)
                if (!$ignoreErrs)
                    fail();
        } else if (fs::isDir($path)) {
            fs::scan($path, function ($file) use ($path, $intoDir, $ignoreErrs) {
                $name = fs::relativize($file, $path);
                if (fs::isDir($file)) {
                    fs::makeDir("$intoDir/$name");
                    return;
                }

                fs::ensureParent("$intoDir/$name");
                if (fs::copy($file, "$intoDir/$name", 1024 * 128) < 0)
                    if (!$ignoreErrs)
                        fail();
            });
        }
    }

    /**
     * @param string $path
     * @param array $filter
     * @param bool $ignoreErrs
     * @return bool
     */
    public static function clean(string $path, array $filter = [], bool $ignoreErrs = false): bool
    {
        if (fs::isDir($path)) {
            $result = fs::clean($path, $filter);
            if (!$result['error']) {
                return true;
            } else {
                if (!$ignoreErrs)
                    fail();

                return false;
            }
        } else fs::delete($path);

        return true;
    }

    /**
     * @param string $path
     * @return File
     * @throws \php\io\IOException
     */
    public static function getFile(string $path): File {
        if (!$_ENV["APP_HOME"]) {
            Logger::printError("FileSystem", "Variable `APP_HOME` not set");
            fail();
        }

        return new File(fs::abs($_ENV["APP_HOME"] . "/" . $path));
    }
}