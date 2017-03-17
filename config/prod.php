<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app['uploaded_file_name'] = 'flopy.json';
$app['models.path'] = __DIR__.'/../var/models';
$app['python.executable'] = 'python3';
$app['flopy.path'] = __DIR__.'/../src/pyModelling';
$app['flopy.entry.script'] = 'inowas.py';
