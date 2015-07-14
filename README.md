# monolog-viewer
Very simple interface for viewing monolog JSON formatted log files

# Basic Usage
```
<?php
require (__DIR__ . '/vendor/autoload.php');
$viewer = new \Jtclark\MonologViewer([
    'user' => 'user',
    'pass' => 'abc123',
    'path' => 'sample.log',
    'template' => 'optional_log_template.twig'
]);

$viewer->authenticate();

$viewer->render();
```

## monolog config
```
$logger = new \Monolog\Logger('app');

$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
$handler = new \Monolog\Handler\StreamHandler('sample.log', \Monolog\Logger::DEBUG);
$handler->setFormatter(new Monolog\Formatter\JsonFormatter());
$logger->pushHandler($handler);
```
