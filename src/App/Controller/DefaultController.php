<?php

namespace App\Controller;

use App\Repository\CalculationRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController
{
    /** @var  Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function indexAction()
    {
        return $this->app['twig']->render('index.html.twig', array());
    }


    public function uploadConfigurationAction(Request $request)
    {
        $name = $this->app['uploaded_file_name'];
        $modelsPath = $this->app['models.path'];

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $configFile */
        $configFile = $request->files->get('file');
        if (! $configFile->isValid()){
            return new JsonResponse('Uploaded file is not valid');
        }

        $uuid = Uuid::uuid4();
        #if ($id && Uuid::isValid($id)){
        #    $uuid = Uuid::fromString($id);
        #}

        $configFile->move($modelsPath.'/'.$uuid->toString(), $name);
        $filename = $modelsPath.'/'.$uuid->toString().'/'.$name;

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('calculation', false, false, false, false);
        $msg = new AMQPMessage($uuid, array('delivery_mode' => 2));
        $channel->basic_publish($msg, '', 'task_queue');
        $channel->close();
        $connection->close();

        $this->app['app.calculation.repository']->addCalculation($uuid->toString());
        return $this->app->redirect(sprintf('/calculation/%s', $uuid->toString()));
    }

    /**
     * @param $id
     * @return string
     */
    public function calculationAction(string $id)
    {
        if (! Uuid::isValid($id)){
            $this->app->abort(404, sprintf('Calculation $id %s is not valid.', $id));
        }

        $filename = $this->app['models.path'].'/'.$id.'/'.$this->app['uploaded_file_name'];
        if (! file_exists($filename)){
            $this->app->abort(404, sprintf('Calculation with $id %s does not exist anymore.', $id));
        }

        /** @var CalculationRepository $repository */
        $repository = $this->app['app.calculation.repository'];
        $calculation = $repository->findByCalculationId($id);

        return $this->app['twig']->render(
            'calculation.html.twig',
            array(
                'configuration' => file_get_contents($filename),
                'calculation' => $calculation
            )
        );
    }
}
