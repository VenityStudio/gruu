<?php

namespace gruu\php;


use php\lib\str;

class PhpDocParser
{
    /**
     * @var string
     */
    private $input;

    /**
     * @var array
     */
    private $data;

    /**
     * PhpDocParser constructor.
     *
     * @param string $phpDoc
     */
    public function __construct(string $phpDoc) {
        $this->input = $phpDoc;
        $this->parse();
    }

    protected function parse() {
        foreach (str::split($this->input, "\n") as $line) { // Yes. It`s works!
            if (!str::startsWith($line, "@")) continue;

            $data = str::split($line, " ", 2);
            $this->data[str::sub($data[0], 1)] = $data[1];
        }
    }

    /**
     * @return array
     */
    public function getData(): ?array {
        return $this->data;
    }
}