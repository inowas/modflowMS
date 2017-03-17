<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyListTable extends ContainerAwareCommand {

    protected function configure(): void
    {
        $this->setName("inowas:flopy:list")
            ->setDescription("Lists all entries in calculations table.")
            ->setDefinition(array())
            ->setHelp('Help goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln([
            'List of all calculations',
            '========================',
            '',
        ]);

        $calculations = $this->app['app.calculation.repository']->fetchAll();
        foreach ($calculations as $value){
            $output->writeln(sprintf('id: %s, calculationId: %s, state: %s', $value['id'], $value['calculation_id'], $value['state']));
        }
    }
}
