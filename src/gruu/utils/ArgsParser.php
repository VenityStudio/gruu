<?php

namespace gruu\utils;


use php\lib\arr;
use php\lib\str;

class ArgsParser
{
    /**
     * @var string[]
     */
    private $args = [];

    /**
     * @var string[]
     */
    private $flags = [];

    /**
     * @var string[]
     */
    private $commands;

    public function __construct(array $args) {
        $this->args = $args;
        $this->parse();
    }

    /**
     * @return string[]
     */
    public function getFlags(): array {
        return $this->flags;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array {
        return $this->commands;
    }

    /**
     * @param string $flag
     * @return bool
     */
    public function hasFlag(string $flag): bool {
        return arr::has($this->flags, $flag);
    }

    /**
     * @param string $command
     * @return bool
     */
    public function hasCommand(string $command): bool {
        return arr::has($this->commands, $command);
    }

    protected function parse() {
        foreach ($this->args as $arg) {
            if (str::startsWith($arg, "--")) {
                $this->flags[] = str::sub($arg, 2);
                continue;
            }

            $this->commands[] = $arg;
        }
    }
}