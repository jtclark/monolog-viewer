<?php
date_default_timezone_set('UTC');
require (__DIR__ . '/vendor/autoload.php');
$viewer = new \Jtclark\MonologViewer([
    'user' => 'user',
    'pass' => 'abc123',
    'path' => __DIR__ . '/sample.log',
    //'template' => 'log.twig'
]);

$viewer->authenticate();

$viewer->render(null, $_GET['filter']);