<?php
require_once "vendor/autoload.php";
/*
$parameters = array(
    'tcp://10.0.5.10:6379?timeout=3',
    'tcp://10.0.5.83:6379?timeout=3',
    'tcp://10.0.5.35:6379?timeout=3'
);

$options = [
    'cluster' => 'redis',
];

$client = new Predis\Client($parameters, $options);

/*
$client->set('{matias2}foo', 'bar');
$client->set('{matias2}foo1', 'bar');
$client->set('{matias2}foo2', 'bar');
$client->set('{matias2}foo3', 'bar');
$client->set('{matias2}foo4', 'bar');


$keys = array();
foreach ($client as $nodeClient) {
    $nodeClientKeys = $nodeClient->keys("{TESTPREFIXEHASH}php71clusterModeMatias:*");
    $keys = array_merge($keys, $nodeClientKeys);
}

echo "COUNT " . count($keys) . "\n";
// echo json_encode($keys) . "\n";

//$nodeClient->mget($keys);

echo json_encode($client->mget($keys)) . "/n";



// $client = new Predis\Client($parameters, $options);
// $client->set('foo', 'bar');
// echo $client->get('foo');

*/
$sdkConfig = array(
    'log' => array('adapter' => 'stdout', 'level' => 'error'),
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'clusterNodes' => array(
            'tcp://client-tracker-0001-001.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
            'tcp://client-tracker-0001-002.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
            'tcp://client-tracker-0002-001.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
            'tcp://client-tracker-0002-002.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
            'tcp://client-tracker-0003-001.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
            'tcp://client-tracker-0003-002.yygsj3.0001.use1.cache.amazonaws.com:6379?timeout=3',
        ),
        'options' => array(
            'distributedStrategy' => 'cluster',
            'prefix' => 'test:.',
            'keyHashTags' => array('{fadon}', '{SPLITIO}')
        )
    ),
);

$splitFactory = \SplitIO\Sdk::factory('YOUR_API_KEY', $sdkConfig);
$splitClient = $splitFactory->client();
$splitManager = $splitFactory->manager();

echo "Something happened here\n";

echo $splitClient->getTreatment('littlespoon', 'PHP_7_1_notInOrOfAfterWorks'), "\n";


// echo $splitClient->getTreatment('pato', 'testing_new_split'), "\n";
// echo $splitClient->getTreatment('martin', 'testing_new_split'), "\n";
// echo $splitClient->getTreatment('matias', 'sample_feature'), "\n";

// echo json_encode($splitManager->split("PHP_7_1_createDeleteUpdateTestShowsInSplitNames"));

echo json_encode($splitManager->splitnames());

//echo json_encode($splitManager->splitNames());
