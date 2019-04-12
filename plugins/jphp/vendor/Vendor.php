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



    /**
     * @param string $directory
     * @return array
     */
    public function findJars(string $directory): array {
        return fs::scan(new File($directory, "jars"), function ($file) {
            if (fs::ext($file) == "jar") return $file;
        });
    }

    /**
     * @param string $directory
     * @return array
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function findSources(string $directory): array {
        $file = new File($directory, "package.php.yml");
        if (!$file->exists()) return [];

        $pkg = fs::parseAs($file, "yaml");
        $arr = [];

        if ($pkg["sources"])
            /** @var File $source */
            foreach ($pkg["sources"] as $source)
                $arr[] = new File($directory, $source);

        return $arr;
    }

    /**
     * @param string $directory
     * @return array
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function findDependencies(string $directory): array {
        $file = new File($directory, "package.php.yml");
        if (!$file->exists()) return [];
        $deps = [];
        $yaml = fs::parseAs($file, "yaml");

        if ($yaml["deps"])
            foreach ($yaml["deps"] as $name => $dep) {
                if (str::startsWith($dep, ">=") ||
                    str::startsWith($dep, "<=") ||
                    str::startsWith($dep, "=<") ||
                    str::startsWith($dep, "=>"))
                    $deps[$name] = str::sub($dep, 2);
                elseif (str::startsWith($dep, "~"))
                    $deps[$name] = str::sub($dep, 1);
                else $deps[$name] = $dep;
            }

        return $deps;
    }

    /**
     * @param string $path
     */
    public function addToClassPath(string $path) {
        $this->classPath[] = $path;
        $this->classPath = array_unique($this->classPath);
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
    public function getClassPath(): ?array {
        return $this->classPath;
    }

    /**
     * @throws \php\io\IOException
     * @throws \php\format\ProcessorException
     */
    public function buildClassPath() {
        foreach ((new File($this->vendorDir))->findFiles() as $file) {
            if ($file->isFile()) continue;

            /** @var File $jar */
            foreach ($this->findJars($file) as $jar)
                $this->addToClassPath($jar->getCanonicalFile()->getAbsolutePath());

            /** @var File $source */
            foreach ($this->findSources($file) as $source)
                $this->addToClassPath($source->getCanonicalFile()->getAbsolutePath());
        }
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

        FileSystem::unpack($gzFile, $file);

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