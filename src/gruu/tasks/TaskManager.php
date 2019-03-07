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

            $this->tasks[$task->getName()] = $task;
        }
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
        $task->getFunction()->invoke();
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
}