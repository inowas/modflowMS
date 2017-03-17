<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);

$console->addCommands(array(
    new \App\Command\FlopyCreateTable($app),
    new \App\Command\FlopyListTable($app),
    new \App\Command\FlopyProcessCommand($app),
    new \App\Command\FlopyProcessRunnerCommand($app),
    new \App\Command\FlopyQueueAddCommand($app),
    new \App\Command\FlopyQueueListCommand($app),
    new \App\Command\FlopyValidateCommand($app),
    new \App\Command\FlopyTruncateTable($app),
));

return $console;
