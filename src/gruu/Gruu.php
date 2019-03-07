<?php

namespace gruu;


use gruu\php\GruuModule;
use gruu\php\PhpDocParser;
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

        if (!fs::exists("./build.gruu")) {
            Logger::printError("Fatal error", "Gruu build file not found!");
            exit(1);
        }

        $time = Time::millis();

        $module = new GruuModule("./build.gruu");
        foreach ($module->getFunctions() as $function) {
            Logger::printWithColor($function->getName() . ": \n", "blue");

            $data = new PhpDocParser($function->getDocComment());

            Logger::printWithColor(var_export($data->getData(), true) . "\n", "off");
            Logger::printWithColor(var_export($function->invoke(), true) . "\n", "magenta+bold");
        }

        $time = round((Time::millis() - $time) / 1000, 3);
        Logger::printSuccess("Build successful", "\nTotal time: " . $time);
    }
}