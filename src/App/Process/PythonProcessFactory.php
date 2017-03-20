<?php

namespace App\Process;

use Silex\Application;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class PythonProcessFactory
{
    /** @var Application $app */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function isValid(string $id): bool
    {
        return file_exists($this->pathToConfigFile($id));
    }

    public function getProcess(string $id): Process
    {
        $processBuilder = new ProcessBuilder();
        $processBuilder->setPrefix($this->app['python.executable']);
        $processBuilder->setWorkingDirectory($this->app['flopy.path']);
        $processBuilder->setArguments(array($this->app['flopy.entry.script'], $this->pathToConfigFile($id)));
        $process = $processBuilder->getProcess();
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        return $process;
    }

    private function pathToConfigFile($id): string
    {
        return $pathToConfigFile = $this->app['models.path'].'/'.$id.'/'.$this->app['uploaded_file_name'];
    }
}
