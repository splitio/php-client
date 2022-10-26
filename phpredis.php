<?php

$redis = new Redis();

$redis->connect('127.0.0.1', 6379);

echo $redis->ping();

echo "ALL KEYS " . json_encode($redis->keys("*")) . "\n";

echo $redis->set("tincho1", "tinchin") . "\n";
echo $redis->set("tincho2", "tinchin") . "\n";
echo $redis->set("tincho3", "tinchin") . "\n";
echo $redis->set("tincho4", "tinchin") . "\n";
echo $redis->set("tincho5", "tinchin") . "\n";
echo $redis->set("tincho6", "tinchin") . "\n";

echo json_encode($redis->mget(["tincho1", "tincho2", "tincho3", "tincho4", "tincho5", "tincho6"])) . "\n";

$nodes = array('10.0.5.10:6379', '10.0.5.34:6379', '10.0.5.35:6379');
$cluster = new RedisCluster(null, $nodes);
//$cluster->setOption(Redis::OPT_PREFIX, 'php71go:');


echo $cluster->set("tincho1", "tinchin") . "\n";
echo $cluster->set("tincho2", "tinchin") . "\n";
echo $cluster->set("tincho3", "tinchin") . "\n";
echo $cluster->set("tincho4", "tinchin") . "\n";
echo $cluster->set("tincho5", "tinchin") . "\n";
echo $cluster->set("tincho6", "tinchin") . "\n";

echo json_encode($cluster->mget(["tincho1", "tincho2", "tincho3", "tincho4", "tincho5", "tincho6"])) . "\n";

echo json_encode($cluster->_masters()) . "\n";

/*
foreach ($cluster->_masters() as $arr_master) {
    $keys = $cluster->keys("*");
}
*/

// echo "ALL KEYS " . json_encode($cluster->keys("*")) . "\n";

$clusterKeys = $cluster->keys("php71go*");

// $keys = array_values(array_unique($clusterKeys));

echo "KEYS COUNT: " . count($clusterKeys) . "\n";

// echo "KEYS " . json_encode($clusterKeys) . "\n";

$t=time();
echo "TIME1 " . $t . "\n";
// echo json_encode($cluster->mget($clusterKeys)) . "\n";
$items = $cluster->mget($clusterKeys);
// echo json_encode($items) . "\n";
$t2=time();
echo "TIME2 " . $t2 . "\n";

echo "Something happened here\n";

// echo $splitClient->getTreatment('pato', 'testing_new_split'), "\n";
// echo $splitClient->getTreatment('martin', 'testing_new_split'), "\n";
// echo $splitClient->getTreatment('matias', 'sample_feature'), "\n";

/*
'clusterNodes' => array(
            'tcp://127.0.0.1:30001?timeout=3',
            'tcp://127.0.0.1:30002?timeout=3',
            'tcp://127.0.0.1:30003?timeout=3',
            'tcp://127.0.0.1:30004?timeout=3',
            'tcp://127.0.0.1:30005?timeout=3',
            'tcp://127.0.0.1:30006?timeout=3'
        ),
*/

// echo json_encode($splitManager->split("PHP_7_1_createDeleteUpdateTestShowsInSplitNames"));

// echo json_encode($splitManager->splits());

// echo json_encode($splitManager->splitNames());
