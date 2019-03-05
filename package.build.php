<?php

// build file for jppm

use packager\Event;

function task_build(Event $e) {
    Tasks::run("app:build", [], []);

    foreach (["./bin/gruu", "./bin/gruu.bat"] as $file)
        Tasks::copy($file, "./build/");
}