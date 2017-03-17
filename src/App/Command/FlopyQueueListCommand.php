<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyQueueListCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this->setName("inowas:flopy:queue:list")
            ->setDescription("List all queued calculations.")
            ->setDefinition(array())
            ->setHelp('Help goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln(sprintf("List all models in the Modflow queue."));

        #$em = $this->getContainer()->get('doctrine.orm.entity_manager');
        #$calculations = $em->getRepository('AppBundle:ModflowCalculation')
        #    ->findBy(
        #        array('state' => 0),
        #        array('dateTimeAddToQueue' => 'ASC')
        #    );
        #/** @var ModflowCalculation $calculation */
        #foreach ($calculations as $calculation){
        #    $output->writeln($calculation->getModelId()->toString());
        #}

        $output->writeln('List End.');
    }
}
