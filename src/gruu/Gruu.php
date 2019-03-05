<?php

namespace gruu;


class Gruu
{
    /**
     * @var ArgsParser
     */
    private $args;

    /**
     * @var bool
     */
    private $debug;

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

    /**
     * @return bool
     */
    public function isDebug(): bool {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug) {
        $this->debug = $debug;
    }

    public function getVersion(): string {
        return "0.0.1-dev";
    }

    public function start() {
        if ($this->args->hasFlag("version")) {
            echo "   ____ ________  ____  __\n";
            echo "  / __ `/ ___/ / / / / / /\n";
            echo " / /_/ / /  / /_/ / /_/ / \n";
            echo " \\__, /_/   \\__,_/\\__,_/  \n";
            echo "/____/ {$this->getVersion()} by Venity Group\n";
            exit(0);
        }
    }
}