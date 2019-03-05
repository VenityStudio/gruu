<?php 

/**
 * Gruu bootstrap file
 */

use gruu\ArgsParser;
use gruu\Gruu;

$args = new ArgsParser($GLOBALS["argv"]);

$gruu = new Gruu();
$gruu->setArgs($args);
$gruu->setDebug($args->hasFlag("debug"));
$gruu->start();