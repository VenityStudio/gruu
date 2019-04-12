<?php

namespace gruu\utils;


use compress\ArchiveEntry;
use compress\GzipInputStream;
use compress\TarArchive;
use compress\ZipArchive;
use php\io\File;
use php\io\Stream;
use php\lib\fs;
use php\lib\str;

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

    /**
     * @param string $archiveFile
     * @param string $output
     * @throws \php\io\IOException
     */
    public static function unpack(string $archiveFile, string $output) {
        switch (str::lower(fs::ext($archiveFile))) {
            case "gz":
            case "tar.gz":
                $archive = new TarArchive(new GzipInputStream($archiveFile));
                break;
            case "zip":
            case "jar":
                $archive = new ZipArchive(Stream::of($archiveFile));
                break;
        }

        if (!$archive) {
            Logger::printError("FileSystem", "Unsupported archive format for `{$archive}`");
            fail();
        }

        Logger::printWithColor("Unpack {$archiveFile} ", "off");
        $archive->readAll(function (ArchiveEntry $entry, ?Stream $stream) use ($output) {
            if ($entry->isDirectory()) return;

            $newFile = new File($output, $entry->name);
            if (!$newFile->exists())
                $newFile->createNewFile(true);

            fs::copy($stream, $newFile);
        });

        Logger::printWithColor("done.\n", "bold+green");
    }
}