<?php

namespace plugins\jphp\tasks;


use gruu\tasks\Task;

class BuildTask extends Task
{
    /**
     * BuildTask constructor.
     */
    public function __construct() {
        $this->setName("jphp:build");
        $this->setData([
            "task" => $this->getName(),
            "description" => "Build jPHP application with dependencies to jar file"
        ]);

        gruu()->getTaskManager()->addHandler("jphp:build", [$this, "build"]);
    }

    /**
     * Task
     *
     * @param array $data
     * @param $res
     */
    public function build(array $data, $res) {

    }
}