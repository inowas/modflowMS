<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', 'app.default_controller:indexAction');
$app->post('/', 'app.default_controller:uploadConfigurationAction');
$app->get('/calculation/{id}', 'app.default_controller:calculationAction');
$app->get('/calculation/{id}/files/{filename}', 'app.default_controller:calculationFilesAction');
$app->post('/api/validate', 'app.api_controller:postValidateAction');
$app->post('/api/calculate', 'app.api_controller:postCalculateAction');
$app->post('/api/calculate/{id}', 'app.api_controller:postCalculateAction');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
