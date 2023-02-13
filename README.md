# APIx Log, very thin PSR-3 logger

This project is a detached fork of [APIx Log](https://github.com/apix/log) as I wanted to add features and bug fixes not available in the original version.

[![Latest Stable Version](https://poser.pugx.org/8ctopus/apix-log/version)](https://packagist.org/packages/8ctopus/apix-log)
[![Total Downloads](https://poser.pugx.org/8ctopus/apix-log/downloads)](https://packagist.org/packages/8ctopus/apix-log)
[![License](https://poser.pugx.org/8ctopus/apix-log/license.svg)](https://packagist.org/packages/8ctopus/apix-log)

Minimalist and fast **PSR-3** compliant logger.

* Light, come out-of-the-box bundle with wrappers for:
   * [ErrorLog](src/Logger/ErrorLog.php), [File](src/Logger/File.php), [Mail](src/Logger/Mail.php), [Sapi](src/Logger/Sapi.php) ~ built around the `error_log()` function,
   * [Runtime](src/Logger/Runtime.php) ~ as an Array/ArrayObject wrapper, and [Nil](src/Logger/Nil.php) ~ as Null wrapper,
   * [Stream](src/Logger/Stream.php) ~ logs are sent to sockets, local and remote files, filters and other similar resources (default to standard output bypassing output buffering).

* Extendable, additional logging backends are available:
   * [PHPMailer/apix-log-phpmailer](https://github.com/PHPMailer/apix-log-phpmailer) ~ logs are sent using PHPMailer,
   * [jspalink/apix-log-pushover](https://github.com/jspalink/apix-log-pushover) ~ logs are sent using Pushover,
   * [apix/log-tracker](https://github.com/apix/log-tracker) ~ adds logger/tracker such as Google Analytics, Dashbot, etc...,

* Very fast and even faster when logging is deferred. [See here on how it compares to monolog](https://github.com/apix/log/issues/9)
* Clean API, see the [`LoggerInterface`](src/Logger/LoggerInterface.php) and the [`LogFormatterInterface`](src/LogFormatterInterface.php).
* 100% Unit **tested** and compliant with PSR0, PSR1 and PSR2.

Feel free to comment, send pull requests and patches...

## Basic usage ~ *standalone*

This simple logger is set to intercept `critical`, `alert` and `emergency` logs -- see the [log levels](#log-levels) for the order.

```php
$urgentLogger = new Apix\Log\Logger\Mail('franck@foo.bar');

$urgentLogger
   // catch logs >= to `critical`
   ->setMinLevel('critical')
   ->alert('Running out of {items}', ['items' => 'beers']);
```
## Advanced usage ~ *multi-logs dispatcher*

Let's create an additional logger with purpose of catching log entries that have a severity level of `warning` or more.

```php
$appLogger = new Apix\Log\Logger\File('/var/log/apix_app.log');
$appLogger
   // intercept logs that are >= `warning`
   ->setMinLevel('warning')
   // don't propagate to further buckets
   ->setCascading(false)
   // postpone writing logs to file
   ->setDeferred(true)
   // flush logs to file once 100 logs are collected
   ->setDeferredTrigger(100);
```

`setCascading()` set to *false* (default: *true*) so the entries caught here won't continue downstream past that particular log bucket.\
`setDeferred()` was set to *true* (default: *false*) so processing happens when:
- `__destruct` (end of script generally)
- `flushDeferredLogs` is called
- `setDeferredTrigger` is reached

Now, let's create a main logger object and inject the two previous loggers.

```php
$logger = new Apix\Log\Logger([$urgentLogger, $appLogger]);
```

Let's create an additional logger -- just for development/debug purposes.

```php
if (DEBUG) {
   // Bucket for the remaining logs -- i.e. `notice`, `info` and `debug`
   // default to screen without output buffer
   $devLogger = new Apix\Log\Logger\Stream();

   // $devLogger = new Logger\File('/tmp/apix_debug.log');
   $devLogger->setMinLevel('debug');

   // another way to inject a log bucket
   $logger->add($devLogger);
}
```

Finally, let's push some log entries:

```php
$exception = new \Exception('Boo!');

// handled by both $urgentLogger & $appLogger
$logger->critical('OMG saw {bad-exception}', ['bad-exception' => $exception]);

// handled by $appLogger
// push an object (or array) directly
$logger->error($exception);

// handled by $devLogger
$logger->info('Testing a var {my_var}', ['my_var' => [...]]);
```

## Log levels

The eight [RFC 5424][] levels of logs are supported, in cascading order:

 Severity  | Description
-----------|-----------------------------------------------------------------
 Emergency | System level failure (not application level)
 Alert     | Failure that requires immediate attention
 Critical  | Serious failure at the application level 
 Error     | Runtime errors, used to log unhandled exceptions
 Warning   | May indicate that an error will occur if action is not taken
 Notice    | Events that are unusual but not error conditions
 Info      | Normal operational messages (no action required)
 Debug     | Verbose info useful to developers for debugging purposes (default)

[PSR-3]: http://tools.ietf.org/html/rfc5424
[RFC 5424]: http://tools.ietf.org/html/rfc5424#section-6.2.1

## Installation

   composer require 8ctopus/apix-log

## License

   APIx Log is licensed under the New BSD license -- see the `LICENSE.txt` for the full license details.
