<?php

namespace plugins\jphp\tasks;


use gruu\tasks\Task;
use gruu\utils\FileSystem;
use gruu\utils\Logger;
use php\io\File;
use php\lib\arr;
use plugins\jphp\JPHPPlugin;
use plugins\jphp\jppm\RemoteRepository;
use plugins\jphp\vendor\Vendor;

class UpdateTask extends Task {

    /**
     * UpdateTask constructor.
     */
    public function __construct() {
        $this->setName("jphp:update");
        $this->setData([
            "extends" => "plugins, repositories, dependencies, configure",
            "description" => "Update all project dependencies"
        ]);

        gruu()->getTaskManager()->addHandler($this->getName(), [$this, "update"]);
    }

    /**
     * @param array $data
     * @param $res
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     */
    public function update(array $data, $res) {
        foreach (JPHPPlugin::getRepositories() as $repository) {
            $repo = new RemoteRepository();
            $repo->setSource($repository);

            foreach (JPHPPlugin::getDependencies() as $dependency => $pkgVersion) {
                $versions = $repo->find($dependency);
                $versionsKeys = arr::keys($versions);
                $vendorDir = JPHPPlugin::getConfiguration()["vendor"] ?: "./vendor";

                if ($pkgVersion == "*" || $pkgVersion == "last")
                    $version = arr::pop($versionsKeys);
                elseif (isset(JPHPPlugin::getDependencies()[$dependency])) $version = JPHPPlugin::getDependencies()[$dependency];
                elseif (isset($versionsKeys[$pkgVersion])) $version = $pkgVersion;

                $file = FileSystem::getFile("/repo/jppm/{$dependency}-{$version}");
                if ($file->exists()) {
                    continue;
                }

                if ($version == null) {
                    Logger::printWarning("Package {$dependency}@{$version} not found in {$repository}");
                    continue;
                }

                $file->createNewFile(true);
                if (!$repo->download($dependency, $version, $file)) {
                    Logger::printError("jPHP:Update", "Can`t download {$dependency}@{$version}");
                    fail();
                }

                $vendor = new Vendor($vendorDir);
                $vendor->install($dependency, $file);
                JPHPPlugin::addDependencies($vendor->findDependencies(new File($vendorDir, $dependency)));
                $this->update($data, $res);
                return;
            }
        }
    }
}