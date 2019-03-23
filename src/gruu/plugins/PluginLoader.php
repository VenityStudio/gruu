<?php

namespace gruu\plugins;


use gruu\Gruu;
use gruu\php\GruuModule;
use gruu\tasks\Task;
use gruu\utils\FileSystem;
use gruu\utils\Logger;
use php\io\File;
use php\io\Stream;
use php\lib\arr;
use php\lib\fs;

class PluginLoader
{
    /**
     * @var Plugin[]
     */
    private static $plugins = [];

    /**
     * PluginLoader constructor.
     * @throws \php\io\IOException
     * @throws \php\format\ProcessorException
     */
    public function __construct() {
        foreach (FileSystem::getFile("plugins")->findFiles() as $file) {
            if (!$file->isDirectory()) continue;
            if (!($pluginConfFile = new File($file, "plugin.ini"))->exists()) continue;

            $conf = fs::parseAs($pluginConfFile, "ini");
            addModule((new File($file, $conf["file"])));
        }
    }

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
                fs::scan(fs::parent($module->getFile()), function (File $file) use ($module) {
                    if (arr::has([ "php", "gruu" ], fs::ext($file)))
                        if ($file->getCanonicalFile()->getAbsolutePath() != fs::abs($module->getFile()))
                            addModule($file);
                }); // Plugin class loading

                $pluginClass = $class->newInstanceWithoutConstructor();
                static::$plugins[$pluginClass->getId()] = $pluginClass;
            }
        }
    }
}