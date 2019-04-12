<?php

namespace plugins\jphp;

use gruu\plugins\Plugin;
use plugins\jphp\tasks\BuildTask;
use plugins\jphp\tasks\RunTask;
use plugins\jphp\tasks\UpdateTask;

class JPHPPlugin extends Plugin
{
    /**
     * @var array
     */
    private static $configuration;

    /**
     * @var array
     */
    private static $dependencies;

    /**
     * @var array
     */
    private static $repositories;

    /**
     * @return array
     */
    public static function getConfiguration() {
        return static::$configuration;
    }

    /**
     * @return array
     */
    public static function getDependencies() {
        return static::$dependencies;
    }

    /**
     * @param array $arr
     */
    public static function addDependencies(array $arr) {
        foreach ($arr as $key => $value) {
            if (isset(static::$dependencies[$key])) continue;
            static::$dependencies[$key] = $value;
        }
    }

    /**
     * @return array
     */
    public static function getRepositories(): array
    {
        return self::$repositories;
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

        gruu()->getTaskManager()->addHandler("repositories", function (array $data, $res) {
            static::$repositories[] = $res["jppm"];
        });

        gruu()->getTaskManager()->addTask(new BuildTask());
        gruu()->getTaskManager()->addTask(new RunTask());
        gruu()->getTaskManager()->addTask(new UpdateTask());
    }
}