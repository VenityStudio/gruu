<?php

namespace gruu\plugins;


abstract class Plugin
{
    /**
     * @return string
     */
    abstract public function getId(): string;

    abstract public function load();
}