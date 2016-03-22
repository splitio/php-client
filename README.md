# Split - PHP SDK Client

[ ![Codeship Status for splitio/php-client](https://codeship.com/projects/c7efdb80-b249-0133-eb9b-567510b4e5ac/status?branch=master)](https://codeship.com/projects/133329)

## Installing Split SDK using composer
```
$ composer require splitsoftware/split-sdk-php
```
## Setting backend service
Once that  Split SDK has been installed via composer, you will find the Split background service in the **vendor/bin** folder located in your own project.
You need run this service on background. To do it, you could add an script under Upstart system or use Supervisor.
Take a look to the section: [Split Synchronizer Service](#split-synchronizer-service).

```
/usr/bin/env php /path/to/your/project/vendor/bin/splitio service
```


## Write your code!
```php
/** SDK options */
$options = [
    'log'   => ['adapter' => 'syslog'],
    'cache' => [ 'adapter' => 'redis', 'options' => ['host' => '172.17.0.2', 'port' => 6379]]
];

/** Create the Split Client instance. */
$splitSdk = \SplitIO\Sdk::factory('API_KEY', $options);

/** Checking if the key belong to treatment 'on' in sample_feature. */
if ($splitSdk->isTreatment('key', 'sample_feature', 'on') {
    //Code for enabled feature
} else {
    //Code for disabled feature
}
```

# SDK Architecture
![Split PHP SDK Architecture](https://github.com/splitio/php-client/blob/develop/doc/img/splitio.arch.png?raw=true)

# Split Synchronizer Service
This service is on charge to keep synchronized the Split server information with your local cache in order to improve the performance at the moment to call the isTreatment or getTreatment methods and avoid undesired overtimes.
![Split Synchronizer Service](https://github.com/splitio/php-client/blob/develop/doc/img/splitio.service.png?raw=true)

### Running the synchronizer service on production
When running **Split synchronizer service** in production it's highly recommended launching it from ```suporvisord```. [Suporvisor](http://supervisord.org) is a daemon that launches other processes and ensures they stay running. If for any reason your long running **Split** service halted the supervisor daemon would ensure it starts back up immediately. Supervisor can be installed with any of the following tools: pip, easy_install, apt-get, yum.
In order to configure the synchronizer service, you could follow these steps:

1- Create a folder to copy the service:
```
mkdir /opt/splitsoftware
```

2- Copy the service within the created folder
```
cp -R ./vendor/splitsoftware/split-sdk-php/bin/* /opt/splitsoftware
```

3- Customize the values in the splitio.ini file with the your correct values, such as the api-key and redis information
```
vi /opt/splitsoftware/splitio.ini
```

4- Add the lines below in your supervisor configuration file.
```
[program:splitio_service]
command=/usr/bin/env php /opt/splitsoftware/splitio.phar service --config-file="/opt/splitsoftware/splitio.ini"
process_name = Split Synchronizer Service
numprocs = 1
autostart=true
autorestart=true
user = root
stderr_logfile=/var/log/splitio.err.log
stderr_logfile_maxbytes = 1MB
stdout_logfile=/var/log/splitio.out.log
stdout_logfile_maxbytes = 1MB
```

### Heroku workers
If your application is running on [Heroku](https://www.heroku.com/) you will be able to run this service as a Dyno Worker. To get it, add the line below on your application **Procfile**:
```
worker: php vendor/bin/splitio.phar service
```

# Adapters / Handlers

## Logger - PSR-3 Logger Interface compatibility
[PSR-3 Logger Interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
is a standards recommendation defining a common interface for logging libraries.

Split SDK provides a custom Logger that implement the PSR-3 standard, the default adapter provided by this SDK is **stdout** and also a **syslog** adapter is available too.
This ones are provided for development and debbug purpose, however if you would like have different adapter you could set
your custom Logger class or even integrate some 3rd party module such as Zend Framework Log module. See the sample code below:

`Zend\Log\PsrLoggerAdapter` wraps `Zend\Log\LoggerInterface`, allowing it to be used.

```php
$zendLogLogger = new Zend\Log\Logger;
$psrLogger = new Zend\Log\PsrLoggerAdapter($zendLogLogger);

/** SDK options */
$options = [
    'log'   => ['psr3-instance' => $psrLogger],
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $options);
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

/** SDK options */
$options = [
    'log'   => ['psr3-instance' => $psrLogger],
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $options);
```


## Cache
Split SDK has its own cache implementation, the main and default adapter is Redis.
#### Redis Cache Adapter - Configuration Options
  - **host:**  The HOST value for Redis adapter 
  - **port:** The PORT value for Redis adapter
  - **pass:** The PASSWORD value for Redis adapter
  - **timeout:** The Timeout value (in seconds) for Redis adapter
  - **url:** The full URL for Redis adapter. If this url is set, host, port and pass will be ignored. The url pattern could be: **redis://user:pass@host:port**

#### Provided Redis Cache Adapter - sample code
```php
/** SDK options */
$options = [
    'cache' => [
            'adapter' => 'redis', 
            'options' => [
                            'host' => '172.17.0.2', 
                            'port' => 6379,
                            'pass' => 'somePassword',
                            'timeout' => 10,
                            'url' => 'redis://u:somePassword@172.17.0.2:6379'
                        ]
                ]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $options);
```
**IMPORTANT:** When Redis is used as a cache, sometimes it is handy to let it automatically evict old data as you add new one. 
This behavior is very well known in the community of developers, since it is the default behavior of the popular memcached system.
 **So, is advisable configure a high memory limit or also a noeviction policy.** Please, take a look here: [Using Redis as an LRU cache](http://redis.io/topics/lru-cache)


# Testing the SDK
Within tests folder you can find different test suites in order to run the Split SDK tests. The most important test suite is: **integration** that wrap the others test suites.

### Integration test suite
Before to run this test suite, please be sure to have a Redis instance runing:
- In order to have a local Redis instance you can install [Docker Container Tool](https://www.docker.com) and pull the oficial Redis container running the command ```docker pull redis```.

And set the correct values on the **phpunit.xml** that you should have copied from **phpunit.xml.dist** file. 

For instance:
```xml
<php>
    <const name="REDIS_HOST" value="172.17.0.2"/>
    <const name="REDIS_PORT" value="6379"/>
</php>
```
Once that you have the configuration file with the right values, move to the main project directory and please run the command below:
```
./vendor/bin/phpunit -c phpunit.xml -v --testsuite integration
```
