<?php
require_once "vendor/autoload.php";

$parameters = array('scheme' => 'tcp',
               'host' => 'localhost',
               'port' => 6379,
               'timeout' => 881,
               'database' => 0,
);

$sdkConfig = array(
    'log' => array('adapter' => 'stdout', 'level' => 'warning'),
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'parameters' => $parameters,
        //'options' => array('prefix' => 'FLAGSET:.')
    ),
    'IPAddressesEnabled' => true
);

$splitFactory = \SplitIO\Sdk::factory(null, $sdkConfig);
$splitClient = $splitFactory->client();
$splitManager = $splitFactory->manager();


$names = $splitManager->splitNames();
echo "names: " . json_encode($names) . "\n";

echo "splits: " . json_encode(count($splitManager->splits())) . "\n";
echo "splits: " . json_encode($splitManager->splits()) . "\n";
echo "split view: " . json_encode($splitManager->split("LA_SCALONETA")) . "\n";

// echo json_encode($splitClient->getTreatment("redo", "DI_MARIA")), "\n";
// echo json_encode($splitClient->getTreatmentWithConfig("redo", "MESSI")), "\n";
 echo json_encode($splitClient->getTreatments("redo", array("MESSI", "LA_SCALONETA"))), "\n";
 echo json_encode($splitClient->getTreatments("redo", array("MESasdSI", "LA_SCALONdddddETA"))), "\n";
// echo json_encode($splitClient->getTreatmentsWithConfig("redo", array("MESSI", "LA_SCALONETA"))), "\n";
// echo json_encode($splitClient->getTreatmentsWithConfigByFlagSets("redo", array("set_test", "set_sdk_team"))), "\n";
// echo json_encode($splitClient->getTreatmentsByFlagSets("redo", array("set_test", "set_sdk_team"))), "\n";
// echo json_encode($splitClient->getTreatmentsWithConfigByFlagSet("redo", "set_test")), "\n";
// echo json_encode($splitClient->getTreatmentsByFlagSet("admin", "mauro"), null), "\n";
// echo json_encode($splitClient->track('matias_test', 'user', 'matias_test', 1, array())), "\n";