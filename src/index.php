<?php 

/**
 * Gruu bootstrap file
 */

use gruu\Gruu;
use gruu\utils\ArgsParser;
use gruu\utils\OS;

$args = new ArgsParser($GLOBALS["argv"]);

$GLOBALS["_GRUU"] = $gruu = new Gruu();
$gruu->setArgs($args);
$gruu->start();

function execute(string $command, string $dir = null): bool {
    try {
        return OS::buildProcess($command, $dir)->inheritIO()->startAndWait()->getExitValue() == 0;
    } catch (Throwable $e) {
        return false;
    }
}