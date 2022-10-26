<?php
require_once "vendor/autoload.php";

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
*/
/*
$keys = array();
foreach ($client as $nodeClient) {
    $nodeClientKeys = $nodeClient->keys("{TESTPREFIXEHASH}php71clusterModeMatias:*");
    $keys = array_merge($keys, $nodeClientKeys);
}*/

$keys = $client->keys("{TESTPREFIXEHASH}php71clusterModeMatias:*");

echo "COUNT " . count($keys) . "\n";
echo json_encode($keys) . "\n";

//$nodeClient->mget($keys);

echo json_encode(count($client->mget($keys))) . "/n";



// $client = new Predis\Client($parameters, $options);
// $client->set('foo', 'bar');
// echo $client->get('foo');
