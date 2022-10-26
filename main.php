<?php
require_once "vendor/autoload.php";

use SplitIO\Sdk\Key;
use SplitIO\Sdk\Manager\SplitManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$parameters = array('scheme' => 'tcp',
               'host' => 'localhost',
               'port' => 6379,
               'timeout' => 881,
               'database' => 0
);

$log = new Logger('SplitIO');
$log->pushHandler(new StreamHandler('php://stdout', Logger::WARNING)); // <<< uses a stream
// 'log' => array('psr3-instance' => $log),
// 'log' => array('instance' => $log, 'standard' => 'psr1'),
// 'log' => array('adapter' => 'stdout', 'level' => 'warning'),

$sdkConfig = array(
    // 'log' => array('psr3-instance' => $log, 'standard' => DEFAULT),
    // 'log' => array('psr3-instance' => $log, 'standard' => 'psr3-v1'),
    // 'log' => array('psr3-instance' => $log, 'standard' => 'psr3-v2'),
    // 'log' => array('psr3-instance' => $log, 'standard' => 'psr3-v3'),
    // 'log' => array('adapter' => 'stdout', 'level' => 'debug'),
    'log' => array('adapter' => 'stdout'),
    'labelsEnabled' => true,
    'cache' => array(
        'adapter' => 'predis',
        'parameters' => $parameters,
        'options' => array('prefix' => 'testing:.')
    ),
    'IPAddressesEnabled' => true
);

/*
$manager = new SplitManager();
echo $manager->splitNames();
*/

$splitFactory = \SplitIO\Sdk::factory(null, $sdkConfig);
$splitClient = $splitFactory->client();
$splitManager = $splitFactory->manager();


$names = $splitManager->splitNames();
echo "names: " . json_encode($names) . "\n";

// echo json_encode($splitClient->getTreatment('invalidKey', 'invalid_feature')) . "\n";
// echo json_encode($splitClient->getTreatmentWithConfig('invalidKey', 'invalid_feature')) . "\n";
echo json_encode($splitClient->getTreatments("redo", array(""))), "\n";
// echo json_encode($splitClient->getTreatments("redo", array("TEST_MATIAS4", "barca_tiny_rules"))), "\n";
echo json_encode($splitClient->getTreatmentsWithConfig("redo", array(""))), "\n";
// echo json_encode($splitClient->getTreatmentsWithConfig("redo", array("TEST_MATIAS4", "barca_tiny_rules"))), "\n";

// while (true) {
//     sleep(1);
//     echo "names: " . json_encode($splitManager->splitNames()) . "\n";
//     var_dump($splitManager->split("TEST_MATIAS4"));
//     echo json_encode(count($splitManager->splits())) . "\n";
//     echo json_encode($splitClient->getTreatment("redo", "TEST_MATIAS4")), "\n";
//     echo json_encode($splitClient->getTreatments("redo", array("TEST_MATIAS4", "barca_tiny_rules"))), "\n";
//     echo json_encode($splitClient->getTreatmentsWithConfig("redo", array("TEST_MATIAS4", "barca_tiny_rules"))), "\n";
//     echo json_encode($splitClient->getTreatments("redo", array("TEST_MATIAS4"))), "\n";
//     echo json_encode($splitClient->getTreatments("redo", array("TEST_MATIAS4"))), "\n";
//     echo json_encode($splitClient->track("redo", "user", "event")) . "\n";

//     if (count($names) > 0) {
//         echo json_encode($splitClient->getTreatment("redo", $names[array_rand($names)])), "\n";
//     }
// }
/*
echo json_encode($splitClient->track('matias_test', 'user', 'matias_test', 1, $properties)), "\n";
echo json_encode($splitClient->getTreatmentWithConfig("test", "aaa")), "\n";
echo json_encode($splitClient->getTreatment("test", "sdsddsd")), "\n";
*/

/*
while (true) {
    for ($i = 1; $i <= 200; $i++) {
        $splitClient->getTreatment("test", "barca_tiny_rules", array(
            "deviceType" => ""
        ));
        $splitClient->getTreatmentWithConfig("test", "TEST_RULO");
        $splitClient->getTreatments("test", array("TEST_RULO"));
    }
    echo json_encode($splitClient->getTreatmentWithConfig("test", "barca_tiny_rules", array(
        "deviceType" => ""
    ))), "\n";
    echo json_encode($splitClient->getTreatmentWithConfig("test", "TEST_RULO")), "\n";
    echo json_encode($splitClient->track('matias_test', 'user', 'matias_test', 1, $properties)), "\n";
    sleep(2);
};
*/

/*
echo json_encode($splitClient->track('tinchin', 'user', 'some_event', 1, array("1", 2, 3))), "\n";
echo json_encode($splitClient->track('tinchin', 'user', 'some_event', 1, $properties)), "\n";
echo json_encode($splitClient->track('tinchin', 'user', 'some_event', 1)), "\n";
*/

/*
$properties = array();
for ($i = 1; $i <= 301; $i++) {
    $properties["prop" . strval($i)] = $i;
}
echo "ESTE: " . json_encode($splitClient->track('tinchin', 'user', 'some_event', 1, $properties)), "\n";

$properties = array();
for ($i = 1; $i <= 110; $i++) {
    $properties["prop" . strval($i)] = str_pad("", 300, "a", STR_PAD_LEFT);
}
echo json_encode($splitClient->track('tinchin', 'user', 'some_event', 1, $properties)), "\n";
/*



// echo json_encode($splitClient->getTreatments("test", array("barca_tiny_rules", "newsplitforfirstcreate"))), "\n";
// echo json_encode($splitClient->track(123456, 'account', 'some_event', 1)), "\n";

/*
for ($i = 1; $i <= 100; $i++) {
    $result = $splitClient->getTreatment(json_encode($i), 'testing_matias');
    if ($result != "nothing") {
        echo $result . "HEREEEEE \n";
    }
}
*/
/*
echo json_encode($splitClient->getTreatmentWithConfig("test", "barca_tiny_rules")), "\n";

$result = $splitClient->getTreatmentWithConfig("test", "barca_tiny_rules");
$config = json_decode($result["config"], true);
$treatment = $result["treatment"];
*/
/*
$result = $splitClient->getTreatmentsWithConfig("key", array("new_boxes", "new_buttons"), null);
$newBoxesResult = $result["new_boxes"];
$configNewBoxes = json_decode($newBoxesResult["config"], true);
$treatmentNewBoxes = $newBoxesResult["treatment"];
echo "configNewBoxes: " . json_encode($configNewBoxes) . "\n";
echo "treatmentNewBoxes: " . $treatmentNewBoxes . "\n";
$newButtonsResult = $result["new_buttons"];
$configNewButtons = json_encode($newButtonsResult["config"], true);
$treatmentNewButton = $newButtonsResult["treatment"];
echo "configNewButtons: " . $configNewButtons . "\n";
echo "treatmentNewButton: " . $treatmentNewButton . "\n";
*/

/*
$result = $splitClient->getTreatmentsWithConfig("key", array("new_boxes", "new_buttons"), null);
$newBoxesResult = $result["new_boxes"];
$configNewBoxes = json_decode($newBoxesResult["config"], true);
$treatmentNewBoxes = $newBoxesResult["treatment"];
$newButtonsResult = $result["new_buttons"];
$configNewButtons = json_encode($newButtonsResult["config"], true);
$treatmentNewButton = $newButtonsResult["treatment"];
*/
// echo "CONFIG: " . $config . "\n";
// var_dump($config);
// echo $config["color"] . "\n";
// echo "TREATMENT: " . $treatment . "\n";

// echo json_encode($splitClient->getTreatments("test", array("barca_tiny_rules"))), "\n";

// echo json_encode($splitManager->split("lalalala")) . "\n";

/*
echo json_encode($splitClient->getTreatment("123456789", "barca_tiny_rules")), "\n";
echo json_encode($splitClient->getTreatment("123456789", "newsplitforfirstcreate")), "\n";

echo json_encode($splitClient->getTreatmentWithConfig("123456789", "barca_tiny_rules")), "\n";
echo json_encode($splitClient->getTreatmentWithConfig("123456789", "newsplitforfirstcreate")), "\n";
*/

// echo json_encode($splitClient->getTreatment("test", "testing_new_split")), "\n";

/*
echo json_encode($splitClient->getTreatments("test", array("barca_tiny_rules", "newsplitforfirstcreate"))), "\n";
echo json_encode($splitClient->getTreatments("test", array(true, true))), "\n";
echo json_encode($splitClient->getTreatmentsWithConfig("test", array(true, true))), "\n";


echo json_encode($splitClient->getTreatmentsWithConfig(
    "test",
    array("barca_tiny_rules", "newsplitforfirstcreate")
)), "\n";

*/
//var_dump($splitManager->split('barca_tiny_rules'));

//var_dump($splitManager->splits());

// echo json_encode($splitManager->split("PHP_7_1_createDeleteUpdateTestShowsInSplitNames")), "\n";

// echo json_encode($splitClient->track(123456, 'user', 'some_event', 1)), "\n";
// echo json_encode($splitClient->track(123456, 'some', 'some_event', 1)), "\n";

// $treatmentResult = $splitClient->getTreatments(123456, ['some_feature']);

// echo count(array_keys($treatmentResult)) ."\n";


// echo $splitClient->getTreatment(new Key("nico", "la"), 'testing_matias'), "\n";
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
