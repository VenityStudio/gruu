<?php

namespace plugins\jphp\vendor;

use compress\ArchiveEntry;
use compress\GzipInputStream;
use compress\TarArchive;
use gruu\utils\FileSystem;
use php\io\File;
use php\io\Stream;
use php\lib\fs;
use php\lib\str;

class Vendor
{
    /**
     * @var array
     */
    private $classPath;

    /**
     * @var string
     */
    private $vendorDir;

    /**
     * Vendor constructor.
     * @param string $vendorDir
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function __construct(string $vendorDir) {
        $this->vendorDir = $vendorDir;
        $this->addVendorDirectory($vendorDir);
    }

    public function findJars(string $directory): array {
        return fs::scan(new File($directory, "jars"), function ($file) {
            if (fs::ext($file) == "jar") return $file;
        });
    }

    /**
     * @param string $path
     */
    public function addToClassPath(string $path) {
        $this->classPath[] = $path;
    }

    /**
     * @param string $directory
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function addVendorDirectory(string $directory) {
        if (!($jsonFile = new File($directory, "paths.json"))->exists()) return;

        $json = fs::parseAs($jsonFile, "json");

        foreach ($json["classPaths"][""] as $file) {
            $this->classPath[] = (new File($directory, $file))->getCanonicalFile()->getAbsolutePath();
        }
    }

    /**
     * @return array
     */
    public function getClassPath(): array
    {
        return $this->classPath;
    }

    /**
     * Install tar.gz archive
     *
     * @param string $name
     * @param File $gzFile
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function install(string $name, File $gzFile) {
        $file = new File($this->vendorDir, $name);
        FileSystem::clean($file);
        $file->mkdirs();

        $tar = new TarArchive(new GzipInputStream($gzFile));
        $tar->readAll(function (ArchiveEntry $entry, Stream $stream) use ($file) {
            if ($entry->isDirectory()) return;

            $newFile = new File($file, $entry->name);
            $newFile->createNewFile(true);
            fs::copy($stream, $newFile);
        });

        $jsonFile = new File($this->vendorDir, "paths.json");
        if (!$jsonFile->exists()) $jsonFile->createNewFile(true);

        $json = fs::parseAs($jsonFile, "json");
        $classPath = [];

        foreach ($this->findJars($file->getCanonicalFile()->getAbsolutePath()) as $file)
            $classPath[] = str::replace($file, fs::abs($this->vendorDir), "");


        $json["classPaths"][""] = array_unique(flow($json["classPaths"][""], $classPath)->toArray());
        Stream::putContents($jsonFile, json_encode($json, JSON_PRETTY_PRINT));
    }
}