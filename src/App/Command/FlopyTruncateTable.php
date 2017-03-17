<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyTruncateTable extends ContainerAwareCommand {

    protected function configure(): void
    {
        $this->setName("inowas:flopy:truncate")
            ->setDescription("Truncates the calculation table.")
            ->setDefinition(array())
            ->setHelp('Help goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->app['app.calculation.repository']->truncateTable();
    }
}
