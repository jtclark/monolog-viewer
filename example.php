<?php
date_default_timezone_set('UTC');
require (__DIR__ . '/vendor/autoload.php');
$viewer = new \Jtclark\MonologViewer([
    'user' => 'user',
    'pass' => 'abc123',
    'path' => 'sample.log',
    'template' => ''
]);

$viewer->authenticate();

$viewer->render();