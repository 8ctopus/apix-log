<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$console = (new Apix\Log\Logger\Stream('php://stdout'))
    ->setFormat(new Apix\Log\Format\ConsoleColors())
    ->setMinLevel('debug');

$file = (new Apix\Log\Logger\File(__DIR__ . '/app.log'))
    // intercept logs that are >= `warning`
    ->setMinLevel('warning')
    // don't propagate to further buckets
    ->setCascading(false)
    // postpone writing logs to file
    ->setDeferred(true)
    // flush logs to file once 100 logs are collected
    ->setDeferredTrigger(100);

$logger = new Apix\Log\Logger([$console, $file]);

$logger->debug('App started');

$logger->notice('Running out of {items} - left {left}', [
    'items' => 'beers',
    'left' => 5,
]);

$exception = new \Exception('Boo!');

// handled by all loggers
$logger->critical('OMG saw {bad-exception}', ['bad-exception' => $exception]);

// push an object (or array) directly
$logger->error($exception);

// handled by console logger
$logger->debug('App closed gracefully');
