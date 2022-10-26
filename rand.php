<?php

$a=array("z","redo","tony","maldo","doc", "emma", "gaston", "javi");
$results=array();

$iterations = 10000000;
for ($i = 1; $i <= $iterations; $i++) {
    $selected = array_rand($a, 1);
    if (!isset($results[$selected])) {
        $results[$selected] = 0;
    }
    $results[$selected]++;
}

$distribution=array();
foreach ($results as $key => $value) {
    $distribution[$key] = $value/$iterations;
}

print json_encode($results) . "\n";
print json_encode($distribution) . "\n";
