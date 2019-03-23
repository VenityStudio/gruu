<?php

namespace plugins\jphp\jppm;


use gruu\utils\Logger;
use php\io\File;
use php\io\Stream;
use php\lib\fs;
use php\lib\str;

class RemoteRepository extends AbstractRepository
{

    /**
     * @param string $repo
     * @return bool
     */
    public function isFit(string $repo): bool {
        return str::startsWith($repo, "http://") || str::startsWith($repo, "https://");
    }

    /**
     * @param string $name
     * @return array
     * @throws \php\io\IOException
     * @throws \php\format\ProcessorException
     */
    public function find(string $name): array {
        $data = Stream::getContents($this->getSource() . "/repo/find?name={$name}");
        if ($data == null) return [];

        return str::parseAs($data, "json")["versions"] ?: [];
    }

    /**
     * @param string $name
     * @param string $version
     * @param File $downloadTo
     * @return bool
     * @throws \php\io\IOException
     * @throws \php\format\ProcessorException
     */
    public function download(string $name, string $version, File $downloadTo): bool {
        $data = Stream::getContents($this->getSource() . "/repo/get?name={$name}&version={$version}");
        if ($data == null) return false;

        if (($url = str::parseAs($data, "json")["downloadUrl"])) {
            Logger::printWithColor("Download {$name}@{$version} from {$url}\n", "off");
            return fs::copy($url, $downloadTo) > 0;
        }

        return false;
    }
}