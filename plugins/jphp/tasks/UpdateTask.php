<?php

namespace plugins\jphp\tasks;


use gruu\Gruu;
use gruu\tasks\Task;
use gruu\utils\FileSystem;
use gruu\utils\Logger;
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

            foreach (JPHPPlugin::getDependencies() as $dependency) {
                $versions = arr::keys($repo->find($dependency));
                $version = arr::pop($versions);

                if ($version == null) {
                    Logger::printWarning("Package {$dependency} not found in {$repository}");
                    continue;
                }

                $file = FileSystem::getFile("/repo/jppm/{$dependency}-{$version}");
                $file->createNewFile(true);
                $repo->download($dependency, $version, $file);

                $vendorDir = JPHPPlugin::getConfiguration()["vendor"] ?: "./vendor";
                (new Vendor($vendorDir))->install($dependency, $file);
            }
        }
    }
}