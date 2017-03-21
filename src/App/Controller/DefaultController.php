<?php

namespace App\Controller;

use App\Repository\CalculationRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $projectPath = $this->app['models.path'].'/'.$id;
        $fs = new Filesystem();
        if (! $fs->exists($projectPath)) {
            $this->app->abort(404, sprintf('Project with $id %s does not exist (anymore).', $id));
        }

        $filename = $this->app['models.path'].'/'.$id.'/'.$this->app['uploaded_file_name'];
        if (! file_exists($filename)){
            $this->app->abort(404, sprintf('Calculation with $id %s does not exist anymore.', $id));
        }

        $files = [];
        $finder = new Finder();
        foreach ($finder->files()->in($projectPath) as $file) {
            $files[] = $file;
        }

        /** @var CalculationRepository $repository */
        $repository = $this->app['app.calculation.repository'];
        $calculation = $repository->findByCalculationId($id);

        return $this->app['twig']->render(
            'calculation.html.twig',
            array(
                'configuration' => $this->getConfiguration($projectPath),
                'calculation' => $calculation,
                'files' => $files
            )
        );
    }

    /**
     * @param string $id
     * @param string $filename
     * @return string
     */
    public function calculationFilesAction(string $id, string $filename)
    {
        if (! Uuid::isValid($id)){
            $this->app->abort(404, sprintf('Calculation $id %s is not valid.', $id));
        }

        $projectPath = $this->app['models.path'].'/'.$id;
        $fs = new Filesystem();
        if (! $fs->exists($projectPath)) {
            $this->app->abort(404, sprintf('Project with $id %s does not exist (anymore).', $id));
        }

        $filename = str_replace("_", ".", $filename);
        $finder = new Finder();
        foreach ($finder->files()->in($projectPath)->name($filename) as $file) {
            $response = new Response();
            $response->setContent($file->getContents());
            $response->headers->set('Content-Type', 'text/plain');
            return $response;
        }

        return $this->app->abort(404, 'File not found, sorry.');
    }

    private function getConfiguration($projectPath): string
    {
        $configuration = "{}";
        $finder = new Finder();
        foreach ($finder->files()->in($projectPath)->name('*.json') as $file) {
            $configuration = $file->getContents();
        }
        return $configuration;
    }
}
