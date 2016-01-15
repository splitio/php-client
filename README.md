# Split - PHP SDK Client
## Installing Split SDK using composer
```
$ composer require splitio/split-sdk-php
```
## Setting backend cron job
```
# exec crontab -e and add the line below
*/1 * * * * php /path/to/your/project/vendor/bin/splitio <API-KEY>
```
## Write your code!
```php
/** Optional: You could develop your own adapters for cache, log, etc. */
$additional_options = ['cache-adapter'=>$myCacheAdapter, 'log-adapter'=>$psrLogger];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);

/** Checking if the feature is enabled or not. */
if ($splitClient->isOn('user-id','feature-name')) {
    //Code for enabled feature
} else {
    //Code for disabled feature
}
```

# Adapters / Handlers

## Logger - PSR-3 Logger Interface compatibility
[PSR-3 Logger Interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
is a standards recommendation defining a common interface for logging libraries.

Split SDK provides a custom Logger that implement the PSR-3 standard, the default adapter provided by this SDK is Syslog.
This one is provided for development and debbug purpose, however if you would like have different adapter you could set
your custom Logger class or even integrate some 3rd party module such as Zend Framework Log module. See the sample code below:

`Zend\Log\PsrLoggerAdapter` wraps `Zend\Log\LoggerInterface`, allowing it to be used.

```php
$zendLogLogger = new Zend\Log\Logger;
$psrLogger = new Zend\Log\PsrLoggerAdapter($zendLogLogger);

/** Optional: You could develop your own adapters for cache, log, etc. */
$additional_options = ['log-adapter'=>$psrLogger];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```
For further information about Zend-Log module, please go to [Zend/Log](http://framework.zend.com/manual/current/en/modules/zend.log.overview.html) documentation.