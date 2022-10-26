<?php
require_once "vendor/autoload.php";

$sdkConfig = array(
    'log' => array('adapter' => 'stdout', 'level' => 'info'),
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'sentinels' => array(
            'tcp://10.0.4.112:26379?timeout=3',
            'tcp://10.0.4.239:26379?timeout=3',
            'tcp://10.0.4.227:26379?timeout=3'
        ),
        'options' => array(
            'replication' => 'sentinel',
            'service' => 'mymaster',
            'prefix' => 'php53sentinel:'
        )
    ),
);

$splitFactory = \SplitIO\Sdk::factory('YOUR_API_KEY', $sdkConfig);
$splitClient = $splitFactory->client();

echo "Something happened here\n";

echo $splitClient->getTreatment('matias', 'PHP_7_1_anding'), "\n";
