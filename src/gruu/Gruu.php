<?php

namespace gruu;


use gruu\utils\ArgsParser;
use gruu\utils\Logger;
use php\lang\Module;
use php\lib\fs;
use php\time\Time;

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

        if (!fs::exists("./build.gruu")) {
            Logger::printError("Fatal error", "Gruu build file not found!");
            exit(1);
        }

        $time = Time::millis();

        try {
            $module = new Module("./build.gruu");
            var_dump($module->getConstants());
        } catch (\Throwable $exception) {
            Logger::printException($exception);
            exit(1);
        }

        $time = round((Time::millis() - $time) / 1000, 3);
        Logger::printSuccess("Build successful", "\nTotal time: " . $time);
    }
}