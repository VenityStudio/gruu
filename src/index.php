<?php 

/**
 * Gruu bootstrap file
 * @var Gruu $GLOBALS["_GRUU"]
 */

use gruu\Gruu;
use gruu\utils\ArgsParser;
use gruu\utils\OS;
use php\lang\Process;

$args = new ArgsParser($GLOBALS["argv"]);

$GLOBALS["_GRUU"] = $gruu = new Gruu();
$gruu->setArgs($args);
$gruu->start();


/*
 * Gruu functional API for build script
 */

/**
 * @return bool
 */
function isLinux(): bool {
    return OS::isLinux();
}

/**
 * @return bool
 */
function isWindows(): bool {
    return OS::isWindows();
}

/**
 * @return bool
 */
function isDarwin(): bool {
    return OS::isDarwin();
}

/**
 * @return bool
 */
function isUnix(): bool {
    return OS::isUnix();
}

/**
 * @param string $command
 * @param string|null $dir
 * @return Process
 * @throws \php\lang\IllegalArgumentException
 * @throws \php\lang\IllegalStateException
 */
function execute(string $command, string $dir = null): Process {
    return OS::buildProcess($command, $dir)->inheritIO()->start();
}

/**
 * @param string $command
 * @param string|null $dir
 * @return Process
 * @throws \php\lang\IllegalArgumentException
 * @throws \php\lang\IllegalStateException
 */
function executeScript(string $command, string $dir = null): Process {
    return OS::buildShellScriptProcess($command, $dir)->inheritIO()->start();
}

/**
 * @param string $name
 */
function invokeTask(string $name) {
    $GLOBALS["_GRUU"]->getTaskManager()->invokeTask($name);
}

/**
 * @param string $path
 */
function addModule(string $path) {
    $GLOBALS["_GRUU"]->getTaskManager()->addModule($path);
}