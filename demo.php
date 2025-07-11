<?php

declare(strict_types=1);
use Apix\Log\Format\MinimalColored;
use Apix\Log\Logger;
use Apix\Log\Logger\File;
use Apix\Log\Logger\Stream;

require __DIR__ . '/vendor/autoload.php';

$console = (new Stream('php://stdout'))
    ->setFormat(new MinimalColored())
    ->setMinLevel('debug');

$file = (new File(__DIR__ . '/app.log'))
    // intercept logs that are >= `warning`
    ->setMinLevel('warning')
    // propagate to further buckets
    ->setCascading(true)
    // postpone writing logs to file
    ->setDeferred(true)
    // flush logs to file once 100 logs are collected
    ->setDeferredTrigger(100);

$logger = new Logger([$console, $file]);

$logger->debug('App started');

$logger->notice('Running out of {items} - left {left}', [
    'items' => 'beers',
    'left' => 5,
]);

$exception = new Exception('Boo!');

// handled by all loggers
$logger->critical('OMG saw {bad-exception}', ['bad-exception' => $exception]);

// push an object (or array) directly
$logger->error($exception);

// handled by console logger
$logger->debug('App closed gracefully');
