<?php

namespace gruu\tasks;


class Task
{
    /**
     * @var \ReflectionFunction
     */
    private $function;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data;

    /**
     * @return \ReflectionFunction
     */
    public function getFunction(): \ReflectionFunction {
        return $this->function;
    }

    /**
     * @param \ReflectionFunction $function
     */
    public function setFunction(\ReflectionFunction $function): void {
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void {
        $this->data = $data;
    }
}