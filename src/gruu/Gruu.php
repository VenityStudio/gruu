<?php

namespace gruu;


use gruu\php\GruuModule;
use gruu\plugins\PluginLoader;
use gruu\tasks\TaskManager;
use gruu\utils\ArgsParser;
use gruu\utils\Logger;
use php\lib\fs;
use php\time\Time;

class Gruu
{
    /**
     * @var ArgsParser
     */
    private $args;

    /**
     * @var TaskManager
     */
    private $taskManager;

    /**
     * @return ArgsParser
     */
    public function getArgs(): ArgsParser {
        return $this->args;
    }

    /**
     * @param ArgsParser $args
     */
    public function setArgs(ArgsParser $args) {
        $this->args = $args;
    }

    public function getVersion(): string {
        return "0.0.1-dev";
    }

    /**
     * @throws \php\io\IOException
     */
    public function start() {
        if ($this->args->hasFlag("version")) {
            Logger::printWithColor("   ____ ________  ____  __\n", "blue+bold");
            Logger::printWithColor("  / __ `/ ___/ / / / / / /\n", "blue+bold");
            Logger::printWithColor(" / /_/ / /  / /_/ / /_/ / \n", "blue+bold");
            Logger::printWithColor(" \\__, /_/   \\__,_/\\__,_/  \n", "blue+bold");
            Logger::printWithColor("/____/ ", "blue+bold");
            Logger::printWithColor("{$this->getVersion()} by ", "off");
            Logger::printWithColor("Venity Group\n", "yellow+bold");

            exit(0);
        }

        $time = Time::millis();

        if ($this->args->hasFlag("dump")) {
            if (!fs::exists("./build.gruu")) {
                Logger::printError("Fatal error", "Gruu build file not found!");
                exit(1);
            }

            $module = new GruuModule("./build.gruu");
            $module->dump("./build.gruu.phb");

            $dumpTime = round((Time::millis() - $time) / 1000, 3);
            Logger::printSuccess("Dump successful", "\nTotal time: " . $dumpTime);
        }

        if (!fs::exists("./build.gruu")) {
            Logger::printError("Fatal error", "Gruu build file not found!");
            exit(1);
        }

        $this->taskManager = new TaskManager();
        $this->taskManager->addTask(PluginLoader::createTask());
        $this->taskManager->addHandler("plugins", new PluginLoader());

        try {
            $this->taskManager->addModule(new GruuModule("./build.gruu"));

            $task = $this->args->getCommands()[1];
            if (!$this->taskManager->hasTask($task))
                throw new \Exception("Task `{$task}` not found!");

            $this->taskManager->invokeTask($task);
        } catch (\Throwable $e) {
            Logger::printException($e);
            exit(1);
        }

        $time = round((Time::millis() - $time) / 1000, 3);
        Logger::printSuccess("Build successful", "\nTotal time: " . $time);
    }

    /**
     * @return TaskManager
     */
    public function getTaskManager(): TaskManager
    {
        return $this->taskManager;
    }
}