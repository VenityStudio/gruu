<?php 

/*
    Gruu bootstrap file
*/

use php\lib\fs;

echo "\$GLOBALS[\"argv\"]:\n";

var_dump($GLOBALS["argv"]);

echo "Root dir: " . fs::abs("./") . "\n";
