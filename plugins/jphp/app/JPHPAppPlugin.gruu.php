<?php

namespace plugins\jphp\app;

use gruu\plugins\Plugin;

class JPHPAppPlugin extends Plugin
{
    private static $configuration;

    /**
     * @return string
     */
    public function getId(): string {
        return "jphp.app";
    }

    public function load() {
        gruu()->getTaskManager()->addHandler("configure", function (array $data, $res) {
            static::$configuration = $res["jphp.app"];
        });

        // Soon ...
    }
}