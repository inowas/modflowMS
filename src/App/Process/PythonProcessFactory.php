<?php

namespace App\Process;

use Silex\Application;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class PythonProcessFactory
{
    /** @var Application $app */
    protected $app;

    /** @var \Symfony\Component\Process\ProcessBuilder  */
    protected $processBuilder;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->processBuilder = new ProcessBuilder();
    }

    public function isValid(string $id): bool
    {
        return file_exists($this->pathToConfigFile($id));
    }

    public function getProcess(string $id): Process
    {
        $this->processBuilder->setPrefix($this->app['python.executable']);
        $this->processBuilder->setWorkingDirectory($this->app['flopy.path']);
        $this->processBuilder->setArguments(array($this->app['flopy.entry.script'], $this->pathToConfigFile($id)));
        $process = $this->processBuilder->getProcess();
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        return $process;
    }

    private function pathToConfigFile($id): string
    {
        return $pathToConfigFile = $this->app['models.path'].'/'.$id.'/'.$this->app['uploaded_file_name'];
    }
}
