<?php

namespace gruu\utils;

use php\lang\System;
use php\lib\char;
use php\lib\str;

class Logger
{
    protected static $ANSI_CODES = [
        "off"        => 0,
        "bold"       => 1,
        "italic"     => 3,
        "underline"  => 4,
        "blink"      => 5,
        "inverse"    => 7,
        "hidden"     => 8,
        "gray"       => 30,
        "red"        => 31,
        "green"      => 32,
        "yellow"     => 33,
        "blue"       => 34,
        "magenta"    => 35,
        "cyan"       => 36,
        "silver"     => "0;37",
        "white"      => 37,
        "black_bg"   => 40,
        "red_bg"     => 41,
        "green_bg"   => 42,
        "yellow_bg"  => 43,
        "blue_bg"    => 44,
        "magenta_bg" => 45,
        "cyan_bg"    => 46,
        "white_bg"   => 47,
    ];

    private static function getColorPrefix(string $color): string {
        if (gruu()->getArgs()->hasFlag("no-color")) return null;
        if (OS::isUnix()) return char::of(27) . "[" . self::$ANSI_CODES[$color] . "m";

        return null; // If gruu color system don`t support this OS
    }

    /**
     * @param string $str
     * @param string $color
     * @return string
     */
    protected static function withColor(string $str, string $color): string {
        $color_attrs = str::split($color, "+");
        $ansi_str = "";

        foreach ($color_attrs as $attr)
            $ansi_str .= self::getColorPrefix($attr);
        $ansi_str .= $str . self::getColorPrefix("off");

        return $ansi_str;
    }

    /**
     * @param string $str
     * @param string $color
     * @throws \php\io\IOException
     */
    public static function printWithColor(string $str, string $color) {
        System::out()->write(self::withColor($str, $color));
    }

    /**
     * @param string $error
     * @param string $message
     * @throws \php\io\IOException
     */
    public static function printError(string $error, string $message) {
        self::printWithColor($error . ": ", "bold+red");
        self::printWithColor($message . "\n", "bold");
    }

    /**
     * @param string $success
     * @param string $message
     * @throws \php\io\IOException
     */
    public static function printSuccess(string $success, string $message) {
        self::printWithColor($success . ": ", "bold+green");
        self::printWithColor($message . "\n", "bold");
    }

    /**
     * @param string $message
     * @throws \php\io\IOException
     */
    public static function printWarning(string $message) {
        self::printWithColor("Warning: ", "bold+yellow");
        self::printWithColor($message . "\n", "bold");
    }

    /**
     * @param \Throwable $exception
     * @throws \php\io\IOException
     */
    public static function printException(\Throwable $exception) {
        Logger::printError($exception->getMessage(), "in {$exception->getFile()}:{$exception->getLine()}");
        Logger::printWithColor($exception->getTraceAsString() . "\n", "off");
    }

    /**
     * @param string $message
     * @throws \php\io\IOException
     */
    public static function debug(string $message) {
        Logger::printWithColor("[DEBUG] " . $message . "\n", "gray+italic");
    }
}