<?php
require_once "vendor/autoload.php";

use SplitIO\Sdk\Key;

$parameters = array('scheme' => 'tcp',
               'host' => 'localhost',
               'port' => 6379,
               'timeout' => 881,
               'database' => 0
);

$sdkConfig = array(
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'parameters' => $parameters,
        'options' => array('prefix' => 'demo:.')
    ),
    'ipAddress' => true
);

$splitFactory = \SplitIO\Sdk::factory(null, $sdkConfig);
$splitClient = $splitFactory->client();

while (true == true) {
    for ($i = 1; $i <= 200; $i++) {
        $splitClient->getTreatment('pato', 'testing_new_matias');
    }
    for ($i = 1; $i <= 100; $i++) {
        $splitClient->track('some_key', 'user', 'some', 1);
    }
    sleep(5);
}
