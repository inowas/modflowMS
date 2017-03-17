<?php

namespace App\Controller;

use App\Repository\CalculationRepository;
use Ramsey\Uuid\Uuid;
use Silex\Application;

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
