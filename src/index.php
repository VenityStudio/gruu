<?php 

/**
 * Gruu bootstrap file
 */

use gruu\Gruu;
use gruu\utils\ArgsParser;

$args = new ArgsParser($GLOBALS["argv"]);

$gruu = new Gruu();
$gruu->setArgs($args);
$gruu->setDebug($args->hasFlag("debug"));
$gruu->start();