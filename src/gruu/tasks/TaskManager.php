<?php

namespace gruu\tasks;


use gruu\php\GruuModule;
use gruu\php\PhpDocParser;
use gruu\plugins\PluginLoader;
use gruu\utils\Logger;

class TaskManager
{
    /**
     * @var Task[]
     */
    private $tasks = [];

    /**
     * @var array[]
     */
    private $handlers;

    public function __construct() {
        $tasks = new Task();
        $tasks->setName("tasks");
        $tasks->setData([
            "description" => "Print all tasks"
        ]);

        $this->addTask($tasks);
        $this->addHandler("tasks", function (array $data, $res) {
            $this->printTasks();
        });
    }

    /**
     * @param GruuModule $module
     */
    public function addModule(GruuModule $module) {
        PluginLoader::registerPlugins($module);

        foreach ($module->getFunctions() as $function) {
            $data = new PhpDocParser($function->getDocComment());
            if (!$data->getData()["task"]) continue;

            $task = new Task();
            $task->setName($data->getData()["task"]);
            $task->setData($data->getData());
            $task->setFunction($function);

            $this->addTask($task);
        }
    }

    /**
     * @param Task $task
     */
    public function addTask(Task $task) {
        $this->tasks[$task->getName()] = $task;
    }

    /**
     * @param string $name
     * @param bool $force
     * @throws \php\io\IOException
     */
    public function invokeTask(string $name, bool $force = false) {
        if (!$this->tasks[$name]) {
            Logger::printError("TaskManager", "Task `$name` not found");

            gruu()->fail();
        }

        $task = $this->tasks[$name];

        if (!$force)
            if ($task->isCalled()) {
                Logger::printWithColor("> {$name} [", "bold");
                Logger::printWithColor("UP-TO-DATE", "yellow+bold");
                Logger::printWithColor("]\n", "bold");

                return;
            }

        if ($parent = $task->getData()["extends"]) {
            $this->invokeTask($parent);
        }

        if ($task->getData()["alias"]) {
            Logger::printWithColor("> {$name}", "bold");
            Logger::printWithColor(" [alias to ", "bold");
            Logger::printWithColor($task->getData()["alias"], "bold+blue");
            Logger::printWithColor("]\n", "bold");
            $this->invokeTask($task->getData()["alias"]);

            return;
        } else {
            Logger::printWithColor("> {$name}\n", "bold");
        }

        if ($function = $task->getFunction()) // The task may be empty
            $res = $function->invoke();

        if (isset($this->handlers[$task->getName()]))
            foreach ($this->handlers[$task->getName()] as $handler)
                $handler($task->getData(), $res);

        $task->setCalled(true); // Block this task for future calls
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTask(string $name): bool {
        return isset($this->tasks[$name]);
    }

    /**
     * @return Task[]
     */
    public function getTasks(): array {
        return $this->tasks;
    }

    /**
     * @throws \php\io\IOException
     */
    public function printTasks() {
        foreach ($this->tasks as $task) {
            Logger::printWithColor("  -> ", "bold");
            Logger::printWithColor($task->getName(), "bold+blue");

            if ($description = $task->getData()["description"])
                Logger::printWithColor(" - {$description}\n", "off");
            else echo "\n";
        }
    }

    /**
     * @param string $taskName
     * @param callable $handler
     */
    public function addHandler(string $taskName, callable $handler) {
        $this->handlers[$taskName][] = $handler;
    }
}