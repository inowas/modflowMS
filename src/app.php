<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/app.db',
    ),
));
$app['twig'] = $app->extend('twig', function ($twig, $app) {return $twig;});
$app['app.default_controller'] = function () use ($app) {return new App\Controller\DefaultController($app);};
$app['app.api_controller'] = function () use ($app) {return new App\Controller\ApiController($app);};
$app['app.python_process_factory'] = function () use ($app) {return new App\Process\PythonProcessFactory($app);};
$app['app.python_process_runner'] = function () use ($app) {return new App\Process\ProcessRunner($app['app.calculation.repository'], $app['app.python_process_factory'], 5);};
$app['app.calculation.repository'] = function () use ($app) {return new \App\Repository\CalculationRepository($app['db']);};

return $app;
