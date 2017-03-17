<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class FlopyProcessRunnerCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this->setName("inowas:flopy:processrunner")
            ->setDescription("Starts the processrunner")
            ->addOption('daemon', null, InputOption::VALUE_NONE, 'If set, the process will run as daemon.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $lockHandler = new LockHandler('inowas:flopy:process:runner');
        if (!$lockHandler->lock()) {
            $output->writeln('This command is already running in another process.');
            return 0;
        }

        /** App\Process\ProcessRunner $pr */
        $pr = $this->app['app.python_process_runner'];

        if ($input->getOption('daemon') === true){
            $output->writeln(sprintf("Start ProcessRunner as Daemon"));
            $pr->run(true);
            return true;
        }

        $output->writeln(sprintf("Start ServiceRunner"));
        $pr->run(false);
        return true;
    }
}
