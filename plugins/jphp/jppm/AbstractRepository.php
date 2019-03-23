<?php

namespace plugins\jphp\jppm;


use php\io\File;

abstract class AbstractRepository
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @param string $repo
     * @return bool
     */
    abstract public function isFit(string $repo): bool;

    /**
     * @param string $name
     * @return array
     */
    abstract public function find(string $name): array;

    /**
     * @param string $name
     * @param string $version
     * @param File $downloadTo
     * @return bool
     */
    abstract public function download(string $name, string $version, File $downloadTo): bool;

    /**
     * @return string
     */
    public function getSource(): string {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source) {
        $this->source = $source;
    }
}