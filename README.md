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
$additional_options = ['cache-adapter'=>$psrCache, 'log-adapter'=>$psrLogger];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);

/** Checking if the feature is enabled or not. */
if ($splitClient->isOn('user-id','feature-name')) {
    //Code for enabled feature
} else {
    //Code for disabled feature
}
```

#SDK Architecture
![Split PHP SDK Architecture](https://github.com/splitio/php-client/blob/develop/doc/img/splitio.arch.png?raw=true)

#Split Synchronizer Service
This service is on charge to keep synchronized the Split server information with your local cache in order improve the performance at the moment to call the isOn method and avoid undesired overtimes.
![Split Synchronizer Service](https://github.com/splitio/php-client/blob/develop/doc/img/splitio.service.png?raw=true)

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

Another sample is [Monolog](https://github.com/Seldaek/monolog). Monolog sends your logs to files, sockets, inboxes, 
databases and various web services. See the complete list of handlers on its [documentation](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md) 

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/** create a log channel */
$psrLogger = new Logger('SplitIO');
$psrLogger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

/** Optional: You could develop your own adapters for cache, log, etc. */
$additional_options = ['log-adapter'=>$psrLogger];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```


## Cache - PSR-6 Cache Interface compatibility

[PSR-6 Cache Interface](https://github.com/php-fig/cache)
is a standards recommendation defining a common interface for caching libraries.

Split SDK has its own implementation of PSR-6 standard, the default adapter is the Filesystem Adapter, however Split SDK provides 2 more implementations thought for production environments,
the first one is a Memcached implementation and the other one is a Redis implementation. See the sample code below in order to know how to set it up.
### Provided Filesystem Cache Adapter - sample code
```php
/**
* Provided Filesystem Cache Adapter
* You can set up this cache adapter provided by Split SDK with your custom configurations.
*/
$additional_options = ['cache' => [
                            'name' => 'filesystem',
                            'options' => [
                                'path'=> '/your/cache/directory'
                            ]
                        ]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```
### Provided Memcached Cache Adapter - sample code
```php
/**
* Provided Memcached Cache Adapter
* You can set up this cache adapter provided by Split SDK with your custom configurations.
*/
$additional_options = ['cache' => [
                            'name' => 'memcached',
                            'options' => [
                                'servers'=>[ //Memcached servers
                                    ['172.17.0.2',11211],
                                    ['172.18.0.4',11211]
                                ]
                            ]
                        ]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```
### Provided Redis Cache Adapter - sample code
```php
/**
* Provided Redis Cache Adapter
* You can set up this cache adapter provided by Split SDK with your custom configurations.
*/
$additional_options = ['cache' => [
                            'name' => 'redis',
                            'options' => [
                                'host' => '172.17.0.3',
                                'port' => 6379
                            ]
                        ]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```
**IMPORTANT:** When Redis is used as a cache, sometimes it is handy to let it automatically evict old data as you add new one. 
This behavior is very well known in the community of developers, since it is the default behavior of the popular memcached system.
 **So, is advisable configure a high memory limit or also a noeviction policy.** Please, take a look here: [Using Redis as an LRU cache](http://redis.io/topics/lru-cache)

### Out of the box Cache Adapters
Additionally, Split SDK could be integrated with other Cache System that implement the PSR-6.
For instance, if you already are using Doctrine Cache on your project, you could integrate it through the [php-cache project](https://github.com/php-cache/doctrine-adapter). See the sample code below:

```php
use Doctrine\Common\Cache\MemcachedCache;
use Cache\Doctrine\CachePool;

// Create a instance of Doctrine's MemcachedCache
$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);
$doctrineCache = new MemcachedCache();
$doctrineCache->setMemcached($memcached);

// Wrap Doctrine's cache with the PSR-6 adapter
$psrPool = new CachePool($doctrineCache);

// Optional: You could develop your own adapters for cache, log, etc.
$additional_options = ['cache-adapter'=>$psrPool];

// Create the Split Client instance.
$splitClient = \SplitIO\Sdk::factory('API_KEY', $additional_options);
```

