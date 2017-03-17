<?php

namespace App\Command;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyQueueAddCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this->setName("inowas:flopy:queue:add")
            ->setDescription("Add calculation to queue.")
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $id = $input->getArgument('id');

        if (! Uuid::isValid($id)){
            $output->writeln(sprintf("The given id: %s is not a valid Uuid.", $id));
            return;
        }

        $output->writeln(sprintf("Adding model id: %s to queue.", $id));
        $this->app['app.calculation.repository']->addCalculation($id);
    }
}
