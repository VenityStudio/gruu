<?php

namespace plugins\jphp;

use gruu\plugins\Plugin;
use plugins\jphp\tasks\BuildTask;
use plugins\jphp\tasks\RunTask;

class JPHPPlugin extends Plugin
{
    private static $configuration;

    private static $dependencies;

    /**
     * @return mixed
     */
    public static function getConfiguration() {
        return static::$configuration;
    }

    /**
     * @return mixed
     */
    public static function getDependencies() {
        return static::$dependencies;
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
        gruu()->getTaskManager()->addTask(new RunTask());
    }
}