<?php

namespace plugins\jphp\tasks;


use gruu\tasks\Task;
use php\lib\fs;
use php\lib\str;
use plugins\jphp\JPHPPlugin;
use plugins\jphp\utils\JavaExec;
use plugins\jphp\vendor\Vendor;

class RunTask extends Task
{
    public function __construct() {
        $this->setName("jphp:run");
        $this->setData([
            "task" => $this->getName(),
            "description" => "Run jPHP application",
            "extends" => "plugins, repositories, dependencies, configure"
        ]);

        gruu()->getTaskManager()->addHandler($this->getName(), [$this, "run"]);
    }

    /**
     * @param array $data
     * @param $res
     * @throws \php\format\ProcessorException
     * @throws \php\io\IOException
     * @throws \php\lang\IllegalArgumentException
     * @throws \php\lang\IllegalStateException
     */
    public function run(array $data, $res) {
        $vendor = new Vendor(JPHPPlugin::getConfiguration()["vendor"] ?: "./vendor");

        $javaExec = new JavaExec();
        $javaExec->addFromVendor($vendor);
        $javaExec->setEnvironment(JPHPPlugin::getConfiguration()["environment"] ?: []);
        $javaExec->setJvmArgs(JPHPPlugin::getConfiguration()["jvm-args"] ?: []);
        $javaExec->setSystemProperties([
            "bootstrap.file" => "res://" . JPHPPlugin::getConfiguration()["bootstrap"]
        ]);

        foreach (JPHPPlugin::getConfiguration()["sources"] as $source) $javaExec->addClassPath(fs::abs($source));

        $javaExec->run(str::split(JPHPPlugin::getConfiguration()["args"], " ") ?: [])->inheritIO()->startAndWait();
    }
}