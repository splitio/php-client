<?php
require_once "vendor/autoload.php";

use SplitIO\Sdk\Key;

$parameters = array('scheme' => 'tcp',
               'host' => 'poc-times.yygsj3.ng.0001.use1.cache.amazonaws.com',
               'port' => 6379,
               'timeout' => 881,
               'database' => 0
);

$sdkConfig = array(
    'log' => array('adapter' => 'stdout', 'level' => 'warning'),
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'parameters' => $parameters,
        'options' => array('prefix' => 'test:.')
    ),
    'IPAddressesEnabled' => true
);

$splitFactory = \SplitIO\Sdk::factory(null, $sdkConfig);
$splitClient = $splitFactory->client();
$splitManager = $splitFactory->manager();

echo "Something happened here\n";

while (true) {
    sleep(1);
    $names = $splitManager->splitNames();
    echo json_encode($splitClient->getTreatments("redo", $names)), "\n";
}
