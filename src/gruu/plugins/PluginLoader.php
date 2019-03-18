<?php

namespace gruu\plugins;


use gruu\php\GruuModule;
use gruu\tasks\Task;
use gruu\utils\Logger;

class PluginLoader
{
    /**
     * @var Plugin[]
     */
    private static $plugins = [];

    /**
     * Handler for task `plugins`
     *
     * @param array $data
     * @param $res
     * @throws \php\io\IOException
     */
    public function __invoke(array $data, $res) {
        foreach ($res as $name) {
            if (array_key_exists($name, static::$plugins))
                static::$plugins[$name]->load();
            else Logger::printWarning("Plugin `{$name}` not found in runtime!");
        }
    }

    /**
     * @return Task
     */
    public static function createTask(): Task {
        $task = new Task();
        $task->setName("plugins");
        $task->setData([]);

        return $task;
    }

    /**
     * @param GruuModule $module
     */
    public static function registerPlugins(GruuModule $module) {
        foreach ($module->getClasses() as $class) {
            if ($class->isSubclassOf(Plugin::class)) {
                $pluginClass = $class->newInstanceWithoutConstructor();
                static::$plugins[$pluginClass->getId()] = $pluginClass;
            }
        }
    }
}