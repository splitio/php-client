<?php
require_once "vendor/autoload.php";

// $options['splitFile'] = dirname(dirname(__DIR__)).'/sdks/php-client/tests/files/.splits';
$options['log'] = array('adapter' => 'stdout', 'level' => 'warning');
$options['splitFile'] = dirname(dirname(__DIR__)).'/sdks/php-client/tests/files/splits.yml';
// $options['splitFile'] = dirname(dirname(__DIR__)).'/sdks/php-client/tests/files/.splits';

$splitFactory = \SplitIO\Sdk::factory('localhost', $options);
$splitSdk = $splitFactory->client();
$splitManager = $splitFactory->manager();

echo "Something happened here\n";

/*
echo $splitSdk->getTreatment('only_key', 'my_feature') . "\n";
echo $splitSdk->getTreatment('invalid_key', 'my_feature') . "\n";
echo $splitSdk->getTreatment('key', 'my_feature') . "\n";
echo $splitSdk->getTreatment('key2', 'other_feature') . "\n";
echo $splitSdk->getTreatment('test', 'other_feature_2') . "\n";
echo $splitSdk->getTreatment('key', 'other_feature_3') . "\n";
echo $splitSdk->getTreatment('key_whitelist', 'other_feature_3') . "\n";

echo $splitSdk->getTreatment(true, 'other_feature_3') . "\n";

echo json_encode($splitSdk->getTreatments('only_key', array('my_feature', 'other_feature'))) . "\n";

echo json_encode($splitSdk->getTreatments(true, array('my_feature', 'other_feature'))) . "\n";
echo json_encode($splitSdk->getTreatments('only_key', array(true, 'other_feature'))) . "\n";


echo json_encode($splitSdk->getTreatmentWithConfig('only_key', 'my_feature')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('invalid_key', 'my_feature')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('key', 'my_feature')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('key2', 'other_feature')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('test', 'other_feature_2')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('key', 'other_feature_3')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('key_whitelist', 'other_feature_3')) . "\n";

echo json_encode($splitSdk->getTreatmentWithConfig(true, 'other_feature_3')) . "\n";
echo json_encode($splitSdk->getTreatmentWithConfig('key_whitelist', true)) . "\n";
*/

echo json_encode($splitSdk->getTreatmentsWithConfig('only_key', array('my_feature', 'other_feature'))) . "\n";

echo json_encode($splitSdk->getTreatmentsWithConfig(true, array('my_feature', 'other_feature'))) . "\n";
echo json_encode($splitSdk->getTreatmentsWithConfig('only_key', array('my_feature', true))) . "\n";

/*
echo json_encode($splitManager->splitNames()) . "\n";

$splitViews = $splitManager->splits();

foreach ($splitViews as $splitView) {
    echo json_encode($splitView->getName()) . "\n";
    echo json_encode($splitView->getTrafficType()) . "\n";
    echo json_encode($splitView->getKilled()) . "\n";
    echo json_encode($splitView->getTreatments()) . "\n";
    echo json_encode($splitView->getChangeNumber()) . "\n";
    echo json_encode($splitView->getConfigs()) . "\n";
    echo "\n";
}

$splitView = $splitManager->split("my_feature");

echo json_encode($splitView->getName()) . "\n";
echo json_encode($splitView->getTrafficType()) . "\n";
echo json_encode($splitView->getKilled()) . "\n";
echo json_encode($splitView->getTreatments()) . "\n";
echo json_encode($splitView->getChangeNumber()) . "\n";
echo json_encode($splitView->getConfigs()) . "\n";
echo "\n";
*/
