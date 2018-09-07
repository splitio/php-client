# Split - PHP SDK Client

[ ![Codeship Status for splitio/php-client](https://codeship.com/projects/c7efdb80-b249-0133-eb9b-567510b4e5ac/status?branch=master)](https://codeship.com/projects/133329)

## Installing Split SDK using composer
```
composer require splitsoftware/split-sdk-php
```
## Setting backend service
Once that  Split SDK has been installed via composer, you will find the Split synchronizer service located within **vendor/splitsoftware/split-sdk-php/bin** folder located in your own project.
You need run this service on background. To do it, you could add an script under Upstart system or use Supervisor.
Take a look to the section: [Split Synchronizer Service](#split-synchronizer-service).

**Basic installation:** For a basic installation follow the steps below:
```
1- Copy the distributed config file as environment file
#> cp /path/to/your/project/vendor/splitsoftware/split-sdk-php/bin/splitio.dist.ini /opt/splitsoftware/splitio.ini

2- Add your custom configuration
#> vi /opt/splitsoftware/splitio.ini

3- Run the service!
#> php /path/to/your/project/vendor/splitsoftware/split-sdk-php/bin/splitio -s --config-file="/opt/splitsoftware/splitio.ini"
```

## Write your code!
```php
/** SDK options */
$options = [
    'log'   => ['adapter' => 'syslog', 'level' => 'error'],
    'cache' => [ 'adapter' => 'redis', 'options' => ['host' => '172.17.0.2', 'port' => 6379]]
];

/** Create the Split Client instance. */
$splitFactory = \SplitIO\Sdk::factory('API_KEY', $options);

$splitClient = $splitFactory->client();

/** Checking if the key belong to treatment 'on' in sample_feature. */
if ($splitClient->isTreatment('key', 'sample_feature', 'on')) {
    //Code for enabled feature
} else {
    //Code for disabled feature
}
```

# SDK Architecture
![Split PHP SDK Architecture](https://github.com/splitio/php-client/blob/master/doc/img/splitio.arch.png?raw=true)

# SDK Advanced Settings

## Split Synchronizer Service
This service is on charge to keep synchronized the Split server information with your local cache in order to improve the performance at the moment to call the isTreatment or getTreatment methods and avoid undesired overtimes.
![Split Synchronizer Service](https://github.com/splitio/php-client/blob/master/doc/img/splitio.service.png?raw=true)

### Running the synchronizer service on production
When running **Split synchronizer service** in production it's highly recommended launching it from ```suporvisord```. [Suporvisor](http://supervisord.org) is a daemon that launches other processes and ensures they stay running. If for any reason your long running **Split** service halted the supervisor daemon would ensure it starts back up immediately. Supervisor can be installed with any of the following tools: pip, easy_install, apt-get, yum.
In order to configure the synchronizer service, you could follow these steps:

1- Make a copy of ```splitio.dist.ini``` file as ```splitio.ini``` and customize the values in the splitio.ini file with the your correct values, such as the api-key and redis information
```
cp /path/to/your/project/vendor/splitsoftware/split-sdk-php/bin/splitio.dist.ini /opt/splitsoftware/splitio.ini 
vi /opt/splitsoftware/splitio.ini
```

2- Add the lines below in your supervisor configuration file.
```
[program:splitio_service]
command=/usr/bin/env php /path/to/your/project/vendor/bin/splitio --service --config-file="/opt/splitsoftware/splitio.ini"
process_name = SplitIO
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
If your application is running on [Heroku](https://www.heroku.com/) you will be able to run this service as a Dyno Worker. To get it, follow the steps below located on your project root directory:

1- Create a folder to copy the service:
```
mkdir service
```

2- Copy the service config file within the created folder
```
cp ./vendor/splitsoftware/split-sdk-php/bin/splitio.dist.ini service
```

3- Add the line below on your application **Procfile**:
```
worker: php vendor/bin/splitio --service --config-file="service/splitio.ini"
```


### Attributes support
```php
/** SDK options */
$options = [
    'log'   => ['adapter' => 'syslog', 'level' => 'error'],
    'cache' => [ 'adapter' => 'redis', 'options' => ['host' => '172.17.0.2', 'port' => 6379]]
];

/** Create the Split Client instance. */
$splitFactory = \SplitIO\Sdk::factory('API_KEY', $options);
$splitClient = $splitFactory->client();

/** Set the attributes values as array */
$attributes = ['age' => 20];

/** Checking if the attribute 'age' belong to treatment 'yound' in sample_feature. */
$treatment = $splitClient->getTreatment('key', 'sample_feature', $attributes);

if ($treatment == 'young') {
    //Code for young feature
} else {
    //Code for old feature
}
```
**NOTE:** For date and time values the attribute should be set as Unix Timestamp in UTC. The sample below shows how to do it on PHP using the [DateTime](http://php.net/manual/en/class.datetime.php) and [DateTimeZone](http://php.net/manual/en/class.datetimezone.php) classes:
```php

$attributes = ['suscription' => (new \DateTime("2016/03/17 07:54PM", new \DateTimeZone("UTC")))->getTimestamp()]

```

### Block Until Ready
Due to nature of the SDK, for different reasons the cached information of Splits definitions could be absent at the first evaluations since at the initialization process a pulling action of the information is performed and the fetched data are saved into the cache system (Redis). To prevent this behaviour, the SDK Factory has been provided with a blocking mechanism named "Blocki Until Ready" or BUR. The idea is pretty simple, as developer you'll be able to set a timeout value and if the provided timeout is reached, the SDK going to throw an exception, avoiding its execution.

```php
/** SDK options */
$options = [
    'ready' => 100, //Time in milliseconds
    'log'   => ['adapter' => 'syslog', 'level' => 'error'],
    'cache' => [ 'adapter' => 'redis', 'options' => ['host' => '172.17.0.2', 'port' => 6379]]
];

/** Create the Split Factory instance using BUR. */
try {
   $splitFactory = \SplitIO\Sdk::factory('API_KEY', $options);
} catch (\SplitIO\Exception\TimeOutException $e) {
    //Do something to avoid evaluation errors 
    exit;
}

$splitClient = $splitFactory->client();

$treatment = $splitClient->getTreatment('key', 'sample_feature');

if ($treatment == 'on') {
    //Code for on feature
} else {
    //Code for another feature 
}
```

### Manager API
The Manager API is a class created for debugging purpose. Using this class, as developer, you will be able to fetch a view of the cached data.
The view class is described below:

```php
class SplitView
{
    private $name;
    private $trafficType; // the name of the traffic type
    private $killed;
    private $treatments;
    private $changeNumber;
}
```

In order to use the Manager API an instance is created by the `factory` method of the SDK:
```php
/** SDK options */
$options = [
    'log'   => ['adapter' => 'syslog', 'level' => 'error'],
    'cache' => [ 'adapter' => 'redis', 'options' => ['host' => '172.17.0.2', 'port' => 6379]]
];

/** Create the Split Factory instance. */
$splitFactory = \SplitIO\Sdk::factory('API_KEY', $options);

/** Get the Manager API instance. */
$splitManager = $splitFactory->manager();

/** Given a feature name, return the Split view */
$splitView = $splitManager->split('my_feature_name'); 

echo $splitView->getName();
echo $splitView->getTrafficType();
echo $splitView->getKilled();
echo $splitView->getTreatments();
echo $splitView->getChangeNumber();

/** Resturn an array of views for all cached splits */
$splitViews = $splitManager->splits(); 

foreach ($splitViews as $splitView) {
    echo $splitView->getName();
    echo $splitView->getTrafficType();
    echo $splitView->getKilled();
    echo $splitView->getTreatments();
    echo $splitView->getChangeNumber();
}

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
Split SDK depends of the popular [PRedis](https://github.com/nrk/predis) library.

**IMPORTANT:** When Redis is used as a cache, sometimes it is handy to let it automatically evict old data as you add new one. 
This behavior is very well known in the community of developers, since it is the default behavior of the popular memcached system.
 **So, is advisable configure a high memory limit or also a noeviction policy.** Please, take a look here: [Using Redis as an LRU cache](http://redis.io/topics/lru-cache)

#### PRedis Cache Adapter
The PRedis library is supported as adapter for Redis Cache connections. For further information about how to configure the ```predis``` client, please take a look on [PRedis official docs](https://github.com/nrk/predis)

For ```predis``` installation you can use ```composer``` running the command ```composer require predis/predis```

#### Provided PRedis Cache Adapter - sample code
```php
/** PRedis options */
//The options below, will be loaded as: $client = new Predis\Client($parameters, $options);

$parameters = ['scheme' => 'tcp', 'host' => '172.17.0.2', 'port' => 6379, 'timeout' => 881];
$options = ['profile' => '2.8', 'prefix' => ''];

/** SDK options */
$sdkOptions = [
    'cache' => ['adapter' => 'predis', 'parameters' => $parameters, 'options' => $options]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $sdkOptions);
```


## Memory
In order to improve the evaluation performance, the Features are saved in shared memory. If your PHP instance has been compiled with **--enable-shmop** parameter, the SDK will use the **shmop** functions.
For further information check this URL: [PHP - Shared Memory](http://php.net/manual/en/book.shmop.php)

### Customizing the shared memory blocks
```php
/** SDK options */
$options = [
    'memory' => [
                    'size' => 10000, 
                    'mode' => 0644,
                    'ttl'  =>  60,
                    'seed' => 4560987,
                ]
];

/** Create the Split Client instance. */
$splitClient = \SplitIO\Sdk::factory('API_KEY', $options);
```

| Option | Description | Default value |
| --- | --- | --- |
| size | The size of the shared memory block you wish to create in bytes | 40000 |
| mode | The permissions that you wish to assign to your memory segment, those are the same as permission for a file. Permissions need to be passed in octal form | 0644 |
| ttl | The time to live for the value added into the shared memory block **in seconds** | 60 |
| seed | An integer value used to generate the system's id for the shared memory block | 123123 |



## Impression Listener
In order to handling the result of a `getTreatment`(a.k.a. `Impression`) for own purposes, client is able to access it by using a custom `Impression Listener`. Sdk options have a parameter called `impressionListener` where you could add an implementation of `ImpressionListener`. You **must** implement the `logImpression` method. This method in particular receives a paramater that has data in the following schema:

| Name | Type | Description |
| --- | --- | --- |
| impression | Impression | Impression object that has the featureName, treatment result, label, etc. |
| attributes | Array | A list of attributes passed by the client. |
| instance-id | String | Corresponds to the IP of the machine where the SDK is running. |
| sdk-language-version | String | Indicates the version of the sdk. In this case the language will be php plus the version of it. |

### Implementing custom Impression Listener
Below you could find an example of how implement a custom Impression Listener:
```php
// Implementation Sample for a Custom Impression Listener
class CustomImpressionListener implements \SplitIO\Sdk\ImpressionListener
{
    public function logImpression($data)
    {
        // Custom Behavior
    }
}
```

### Attaching custom Impression Listener
```php
$sdkConfig = array(
    'log' => ...
    'cache' => ...
    'impressionListener' => new CustomImpressionListener(),
);

$splitFactory = \SplitIO\Sdk::factory('YOUR_API_KEY', $sdkConfig);
$splitClient = $splitFactory->client();
```

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
