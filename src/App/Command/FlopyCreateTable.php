<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyCreateTable extends ContainerAwareCommand {

    protected function configure(): void
    {
        $this->setName("inowas:flopy:create")
            ->setDescription("Creates the Calculation Table.")
            ->setDefinition(array())
            ->setHelp('Help goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->app['app.calculation.repository']->createTable();
    }
}
