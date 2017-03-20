<?php

namespace App\Command;

use App\Repository\CalculationRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Process\Process;

class FlopyRabbitMQRunnerCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this->setName("inowas:flopy:rabbit:runner")
            ->setDescription("Starts the rabbitMQ listener")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $lockHandler = new LockHandler('inowas:flopy:rabbit:runner');
        if (!$lockHandler->lock()) {
            $output->writeln('This command is already running in another process.');
            return 0;
        }

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('calculation', false, false, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
        $callback = function($msg){
            echo " [x] Received ", $msg->body, "\n";
            $this->calculate($msg->body);
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return true;
    }

    protected function calculate(string $id){

        /** @var CalculationRepository $repo */
        $repo = $this->app['app.calculation.repository'];

        if (! $this->app['app.python_process_factory']->isValid($id)){
            return;
        }

        /** @var Process $process */
        $process = $this->app['app.python_process_factory']->getProcess($id);
        $process->run();
        $repo->calculationStarted($id);

        if ($process->isSuccessful()){
            $repo->calculationFinished($id, true, $process->getOutput());
            unset($process);
            return;
        }

        $repo->calculationFinished($id, false, $process->getErrorOutput());
        unset($process);
        return;
    }
}
