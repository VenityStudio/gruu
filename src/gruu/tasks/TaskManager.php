<?php

namespace gruu\tasks;


use gruu\php\GruuModule;
use gruu\php\PhpDocParser;
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
     * @throws \Exception
     */
    public function invokeTask(string $name) {
        if (!$this->tasks[$name]) throw new \Exception("Task {$name} not found!");

        $task = $this->tasks[$name];

        if ($parent = $task->getData()["extends"]) {
            $this->invokeTask($parent);
        }

        Logger::printWithColor("> {$name}\n", "bold");

        if ($function = $task->getFunction()) // The task may be empty
            $res = $function->invoke();

        if (isset($this->handlers[$task->getName()]))
            foreach ($this->handlers[$task->getName()] as $handler)
                $handler($task->getData(), $res);
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
            Logger::printWithColor("\t> ", "bold");
            Logger::printWithColor($task->getName(), "bold+blue");

            if ($description = $task->getData()["description"])
                Logger::printWithColor(" - {$description}\n", "bold");
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