<?php 

/**
 * Gruu bootstrap file
 * @var Gruu $GLOBALS["_GRUU"]
 */

use gruu\Gruu;
use gruu\utils\ArgsParser;
use gruu\utils\OS;
use gruu\php\GruuModule;
use php\lang\Process;
use php\lib\fs;

$args = new ArgsParser($GLOBALS["argv"]);

$GLOBALS["_GRUU"] = $gruu = new Gruu();
$gruu->setArgs($args);
$gruu->start();


/*
 * Gruu functional API for build script
 */

function gruu(): Gruu {
    return $GLOBALS["_GRUU"];
}

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
 * @param bool $force
 */
function invokeTask(string $name, bool $force = false) {
    \gruu()->getTaskManager()->invokeTask($name, $force);
}

/**
 * @param string $path
 */
function addModule(string $path) {
    \gruu()->getTaskManager()->addModule(new GruuModule($path));
}

function fail() {
    \gruu()->fail();
}

/**
 * @param string $path
 * @param string $intoDir
 * @param bool $ignoreErrs
 */
function gruuCopy(string $path, string $intoDir, bool $ignoreErrs = false) {
    if (fs::isFile($path)) {
        if (fs::copy($path, "$intoDir/" . fs::name($path), 1024 * 128) < 0)
            if (!$ignoreErrs)
                fail();
    } else if (fs::isDir($path)) {
        fs::scan($path, function ($file) use ($path, $intoDir, $ignoreErrs) {
            $name = fs::relativize($file, $path);
            if (fs::isDir($file)) {
                fs::makeDir("$intoDir/$name");
                return;
            }

            fs::ensureParent("$intoDir/$name");
            if (fs::copy($file, "$intoDir/$name", 1024 * 128) < 0)
                if (!$ignoreErrs)
                    fail();
        });
    }
}