<?php

namespace plugins\jphp\tasks;


use compress\ZipArchive;
use gruu\tasks\Task;
use gruu\utils\FileSystem;
use gruu\utils\Logger;
use php\io\File;
use php\io\Stream;
use php\lib\fs;
use php\lib\str;
use plugins\jphp\JPHPPlugin;
use plugins\jphp\vendor\Vendor;

class BuildTask extends Task
{
    /**
     * BuildTask constructor.
     */
    public function __construct() {
        $this->setName("jphp:build");
        $this->setData([
            "task" => $this->getName(),
            "description" => "Build jPHP application with dependencies to jar file",
            "extends" => "plugins, repositories, dependencies, configure"
        ]);

        gruu()->getTaskManager()->addHandler($this->getName(), [$this, "build"]);
    }

    /**
     * Task
     *
     * @param array $data
     * @param $res
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function build(array $data, $res) {
        $vendor = new Vendor(JPHPPlugin::getConfiguration()["vendor"] ?: "./vendor");
        $vendor->buildClassPath();

        $outDirectory = new File(JPHPPlugin::getConfiguration()["build-dir"] ?: "./build");
        $outDirectory->exists() ? FileSystem::clean($outDirectory) : $outDirectory->mkdirs();

        $tmpDirectory = new File($outDirectory, ".tmp");
        $tmpDirectory->mkdirs();

        foreach (flow($vendor->getClassPath(), JPHPPlugin::getConfiguration()["sources"])->toArray() as $file) {
            fs::isDir($file) ? FileSystem::copy($file, $tmpDirectory) :
                FileSystem::unpack($file, $tmpDirectory);
        }

        Logger::printWithColor("Create manifest file", "off");

        $manifestFile = new File($tmpDirectory, "META-INF/MANIFEST.MF");
        $manifestFile->createNewFile(true);

        $jphpManifestFile = new File($tmpDirectory, "JPHP-INF/launcher.conf");
        $jphpManifestFile->createNewFile(true);

        Stream::putContents($jphpManifestFile, "bootstrap.file=res://" . JPHPPlugin::getConfiguration()["bootstrap"]);

        $manifest = [
            "Manifest-Version" => "1.0",
            "Created-By" => "Gruu (" . gruu()->getVersion() . ")",
            "Main-Class" => JPHPPlugin::getConfiguration()["main-class"] ?: "php.runtime.launcher.Launcher"
        ];

        $manifestString = "";

        foreach (flow($manifest, JPHPPlugin::getConfiguration()["manifest"])->toArray(true) as $key => $item)
            if ($item != null)
                $manifestString .= $key . ": " . $item . "\n";



        Stream::putContents($manifestFile, $manifestString);

        Logger::printWithColor(" done.\n", "bold+green");
        Logger::printWithColor("Create FatJar archive ... ", "off");

        $fatJarFile = new File($outDirectory, (JPHPPlugin::getConfiguration()["file-name"] ?: "target") . ".jar");
        $fatJarFile->createNewFile();

        $archive = new ZipArchive($fatJarFile);
        $archive->open();

        fs::scan($tmpDirectory, function (File $file) use ($archive, $tmpDirectory) {
            if (fs::isFile($file)) {
                if (gruu()->getArgs()->hasFlag("verbose"))
                    Logger::printWithColor(" -> add " . fs::relativize($file, $tmpDirectory) . "\n", "off");
                $archive->addFile($file, fs::relativize($file, $tmpDirectory));
            }
        });

        $archive->close();
        FileSystem::clean($tmpDirectory);
        fs::delete($tmpDirectory);
        Logger::printWithColor("done.\n", "bold+green");
    }
}