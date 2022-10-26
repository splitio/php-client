<?php
require_once "vendor/autoload.php";

require_once 'ImpressionClient.php';

use Listener\ImpressionClient;

$parameters = ['scheme' => 'tcp',
               'host' => 'localhost',
               'port' => 6379,
               'timeout' => 881,
               'database' => 0
              ];

$sdkConfig = array(
    'log' => array('adapter' => 'stdout', 'level' => 'error'),
    'labelsEnabled' => true,
    'impressionListener' => new ImpressionClient(),
    'cache' => array(
        'adapter' => 'predis',
        'parameters' => $parameters,
        'options' => array('prefix' => 'listener:')
    ),
    'ipAddress' => null
);

$splitFactory = \SplitIO\Sdk::factory('YOUR_API_KEY', $sdkConfig);
$splitClient = $splitFactory->client();

echo $splitClient->getTreatment('pato', 'testinag_new_split'), "\n";
# echo $splitClient->getTreatment('martin', 'testing_new_split'), "\n";
# echo $splitClient->getTreatment('matias', 'testing_new_split'), "\n";


echo "hostname: " . gethostname() . "\n";
$hostname = gethostname();
echo "hostip: " . gethostbyname($hostname) . "\n";
