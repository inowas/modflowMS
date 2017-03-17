<?php
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FlopyValidateCommand extends ContainerAwareCommand {

    protected function configure() {

        $this->setName("inowas:flopy:validate")
            ->setDescription("Checks the config-file.")
            ->addArgument('id', InputArgument::OPTIONAL, 'The ID of the model')
            ->setDefinition(array())
            ->setHelp('Help goes here');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id = $input->getArgument('id');
        // open folder
    }
}
