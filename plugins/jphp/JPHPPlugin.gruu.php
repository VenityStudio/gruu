<?php

namespace plugins\jphp;

use gruu\plugins\Plugin;
use php\lib\fs;
use plugins\jphp\tasks\BuildTask;

class JPHPPlugin extends Plugin
{
    private static $configuration;

    private static $dependencies;

    /**
     * @return mixed
     */
    public static function getConfiguration()
    {
        return self::$configuration;
    }

    /**
     * @return mixed
     */
    public static function getDependencies()
    {
        return self::$dependencies;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return "jphp";
    }

    public function load() {
        gruu()->getTaskManager()->addHandler("configure", function (array $data, $res) {
            static::$configuration = $res["jphp"];
        });

        gruu()->getTaskManager()->addHandler("dependencies", function (array $data, $res) {
            static::$dependencies = $res["jppm"];
        });

        gruu()->getTaskManager()->addTask(new BuildTask());
    }
}