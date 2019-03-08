<?php

namespace gruu\plugins;


use gruu\tasks\Task;

class PluginLoader
{
    /**
     * Handler for task `plugins`
     *
     * @param array $data
     * @param $res
     */
    public function __invoke(array $data, $res) {
        var_dump($res); // Soon ...
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
}