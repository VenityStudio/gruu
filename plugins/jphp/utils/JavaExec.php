<?php

namespace plugins\jphp\utils;

use gruu\utils\Logger;
use php\io\File;
use php\lang\Process;
use php\lib\fs;
use php\lib\str;
use plugins\jphp\vendor\Vendor;

class JavaExec
{
    /**
     * @var array
     */
    private $classPaths = [];

    /**
     * @var array
     */
    private $jvmArgs = [];

    /**
     * @var string
     */
    private $javaBin = "java";

    /**
     * @var array
     */
    private $environment = null;

    /**
     * @var array
     */
    private $systemProperties = [];

    /**
     * @var string
     */
    private $mainClass;

    /**
     * JavaExec constructor.
     * @param string $mainClass
     * @param array $jvmArgs
     * @param array|null $environment
     */
    public function __construct(string $mainClass = 'php.runtime.launcher.Launcher', array $jvmArgs = [], array $environment = null)
    {
        $this->mainClass = $mainClass;
        $this->jvmArgs = $jvmArgs;
        $this->environment = $environment;

        $javaBin = "java";

        if ($_ENV['JAVA_HOME']) {
            $javaBin = $_ENV['JAVA_HOME'] . '/bin/java';
            if (fs::isFile("$javaBin.exe"))
                $javaBin = "$javaBin.exe";
        }

        $this->javaBin = $javaBin;
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * @param array $environment
     * @return JavaExec
     */
    public function setEnvironment(array $environment): JavaExec
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return string
     */
    public function getMainClass(): string
    {
        return $this->mainClass;
    }

    /**
     * @param string $mainClass
     * @return JavaExec
     */
    public function setMainClass(string $mainClass): JavaExec
    {
        $this->mainClass = $mainClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getJavaBin(): string
    {
        return $this->javaBin;
    }

    /**
     * @param string $javaBin
     * @return JavaExec
     */
    public function setJavaBin(string $javaBin): JavaExec
    {
        $this->javaBin = $javaBin;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassPaths(): array
    {
        return $this->classPaths;
    }

    /**
     * @param array $classPaths
     * @return JavaExec
     */
    public function setClassPaths(array $classPaths): JavaExec
    {
        $this->classPaths = $classPaths;
        return $this;
    }

    /**
     * @return array
     */
    public function getSystemProperties(): array
    {
        return $this->systemProperties;
    }

    /**
     * @param array $systemProperties
     * @return JavaExec
     */
    public function setSystemProperties(array $systemProperties): JavaExec
    {
        $this->systemProperties = $systemProperties;
        return $this;
    }

    /**
     * @return array
     */
    public function getJvmArgs(): array
    {
        return $this->jvmArgs;
    }

    /**
     * @param array $jvmArgs
     * @return JavaExec
     */
    public function setJvmArgs(array $jvmArgs): JavaExec
    {
        $this->jvmArgs = $jvmArgs;
        return $this;
    }

    /**
     * @param Vendor $vendor
     */
    public function addFromVendor(Vendor $vendor) {
        foreach ($vendor->getFiles() as $file)
            $this->addClassPath($file);
    }

    /**
     * @param string $classPath
     * @return JavaExec
     */
    public function addClassPath(string $classPath): JavaExec
    {
        $this->classPaths[] = $classPath;
        return $this;
    }

    /**
     * @param array $args
     * @param string|null $directory
     * @return Process
     */
    public function run(array $args = [], string $directory = null)
    {
        $sysArgs = [];

        foreach ($this->systemProperties as $key => $value)
            $sysArgs[] = "-D{$key}={$value}";

        $commands = flow([
            $this->javaBin, '-cp', str::join($this->classPaths, File::PATH_SEPARATOR)],
            $this->jvmArgs, $sysArgs, [$this->mainClass], $args
        )->toArray();

        Logger::debug(str::join($commands, " "));

        $process = new Process(
            $commands,
            $directory,
            $this->environment
        );

        return $process;
    }
}