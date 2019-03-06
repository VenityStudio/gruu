<?php

namespace gruu\utils;


use php\lang\Process;
use php\lang\System;
use php\lib\str;
use php\util\Flow;

class OS
{
    /**
     * Is Linux distribution ?
     *
     * @return bool
     */
    public static function isLinux(): bool {
        return str::contains(str::lower(System::getProperty('os.name')), 'nix') ||
               str::contains(str::lower(System::getProperty('os.name')), 'nux') ||
               str::contains(str::lower(System::getProperty('os.name')), 'aix');
    }

    /**
     * Is Windows ?
     *
     * @return bool
     */
    public static function isWindows(): bool {
        return str::contains(str::lower(System::getProperty('os.name')), 'win');
    }

    /**
     * Is macOS ?
     *
     * @return bool
     */
    public static function isDarwin(): bool {
        return str::contains(str::lower(System::getProperty('os.name')), 'mac');
    }

    /**
     * Is UNIX like OS ?
     *
     * @return bool
     */
    public static function isUnix(): bool {
        return self::isLinux() || self::isDarwin();
    }

    /**
     * @param string $command
     * @param string $directory
     * @param array $customEnv
     * @return Process
     */
    public static function buildProcess(string $command, string $directory = null, array $customEnv = null): Process {
        if (OS::isWindows()) $command = "cmd.exe /c " . $command;
        else $command = "bash " . $command;

        $env = Flow::of($_ENV, $customEnv)->toArray(true);
        return new Process(str::split($command, " "), $directory, $env);
    }
}