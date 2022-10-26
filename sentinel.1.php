<?php

$sentinels = array(
    'tcp://IP:PORT?timeout=NUMBER',
    'tcp://IP:PORT?timeout=NUMBER',
    'tcp://IP:PORT?timeout=NUMBER'
);

$options = array(
    'replication' => 'sentinel',
    'service' => 'SERVICE_MASTER_NAME',
    'prefix' => ''
);

$sdkConfig = array(
    'cache' => array('adapter' => 'predis',
                     'sentinels' => $sentinels,
                     'options' => $options
                    )
);

$splitFactory = \SplitIO\Sdk::factory('YOUR_API_KEY', $sdkConfig);
$splitClient = $splitFactory->client();
