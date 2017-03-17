<?php

namespace App\Command;

use App\Repository\CalculationRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class FlopyProcessCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this->setName("inowas:flopy:run")
            ->setDescription("Run script with id")
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var CalculationRepository $repo */
        $repo = $this->app['app.calculation.repository'];

        $id = $input->getArgument('id');

        if (! $this->app['app.python_process']->isValid($id)){
            $output->writeln(sprintf('A calculation with id: %s does not exist.', $id));
            $output->writeln('Process cancelled');
            return;
        }

        /** @var Process $process */
        $process = $this->app['app.python_process']->getProcess($id);
        $output->writeln(sprintf('Change workingDirectory %s', $process->getWorkingDirectory()));
        $output->writeln(sprintf('Executing: %s', $process->getCommandLine()));
        $output->writeln('==================================================');
        $output->writeln('');

        $process->run();
        $repo->calculationStarted($id);

        if ($process->isSuccessful()){
            $output->writeln($process->getOutput());
            $repo->calculationFinished($id, true, $process->getOutput());
            return;
        }

        $output->writeln($process->getErrorOutput());
        $repo->calculationFinished($id, false, $process->getOutput());
    }
}
