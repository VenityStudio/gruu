<?php

namespace gruu\php;


use gruu\utils\Logger;
use php\io\File;
use php\io\IOException;
use php\io\Stream;
use php\lang\Module;
use php\lib\fs;

class GruuModule
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var string
     */
    private $file;

    /**
     * @var mixed
     */
    private $result;

    /**
     * GruuModule constructor.
     *
     * @param string $file
     * @throws IOException
     */
    public function __construct(string $file) {
        $this->file = $file;
        $this->load();
    }

    /**
     * @throws IOException
     */
    private function load() {
        try {
            // If file extension equal "phb" then is the compiled php source code to byte code
            $this->module = new Module($this->file, fs::ext($this->file) == "phb");
            $this->result = $this->module->call();
        } catch (\Throwable $exception) {
            Logger::printException($exception);
            exit(1);
        }
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param File|Stream|string $target
     */
    public function dump($target) {
        $this->module->dump($target, false);
    }

    /**
     * @return \ReflectionClass[]
     */
    public function getClasses(): array {
        return $this->module->getClasses();
    }

    /**
     * @return \ReflectionFunction[]
     */
    public function getFunctions(): array {
        return $this->module->getFunctions();
    }

    /**
     * @return array
     */
    public function getConstants(): array {
        return $this->module->getConstants();
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->module->getName();
    }
}