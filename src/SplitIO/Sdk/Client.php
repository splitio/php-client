<?php
namespace SplitIO\Sdk;

use SplitIO\Sdk\Events\EventDTO;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Sdk\QueueMetadataMessage;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Validator\InputValidator;
use SplitIO\Sdk\Validator\FlagSetsValidator;

class Client implements ClientInterface
{
    private $evaluator = null;
    private $impressionListener = null;
    private $queueMetadata = null;

    /**
     * Flag to get Impression's labels feature enabled
     * @var bool
     */
    private $labelsEnabled;

    /**
     * @var \SplitIO\Component\Cache\SplitCache
     */
    private $splitCache;

    /**
     * @var \SplitIO\Component\Cache\SegmentCache
     */
    private $segmentCache;

    /**
     * @var \SplitIO\Component\Cache\ImpressionCache
     */
    private $impressionCache;

    /**
     * @var \SplitIO\Component\Cache\EventsCache
     */
    private $eventCache;

    /**
     * @param array $storages
     * @param array $options
     */
    public function __construct($storages, $options = array())
    {
        $this->splitCache = $storages['splitCache'];
        $this->segmentCache = $storages['segmentCache'];
        $this->impressionCache = $storages['impressionCache'];
        $this->eventCache = $storages['eventCache'];
        $this->labelsEnabled = isset($options['labelsEnabled']) ? $options['labelsEnabled'] : true;
        $this->evaluator = new Evaluator($this->splitCache, $this->segmentCache);
        if (isset($options['impressionListener'])) {
            $this->impressionListener = new \SplitIO\Sdk\ImpressionListenerWrapper($options['impressionListener']);
        }
        $this->queueMetadata = new QueueMetadataMessage(
            isset($options['IPAddressesEnabled']) ? $options['IPAddressesEnabled'] : true
        );
    }

    /**
     * Builds new Impression object
     *
     * @param $matchingKey
     * @param $featureFlag
     * @param $treatment
     * @param string $label
     * @param $time
     * @param int $changeNumber
     * @param string $bucketingKey
     *
     * @return \SplitIO\Sdk\Impressions\Impression
     */
    private function createImpression($key, $featureFlag, $treatment, $changeNumber, $label = '', $bucketingKey = null)
    {
        if (!$this->labelsEnabled) {
            $label = null;
        }
        $impression = new Impression($key, $featureFlag, $treatment, $label, null, $changeNumber, $bucketingKey);
        return $impression;
    }

    /**
     * Verifies inputs for getTreatment and getTreatmentWithConfig methods
     *
     * @param $key
     * @param $featureFlagName
     * @param $attributes
     * @param $operation
     *
     * @return null|mixed
     */
    private function doInputValidationForTreatment($key, $featureFlagName, array $attributes = null, $operation)
    {
        $key = InputValidator::validateKey($key, $operation);
        if (is_null($key)) {
            return null;
        }

        $featureFlag = InputValidator::validateFeatureFlagName($featureFlagName, $operation);
        if (is_null($featureFlag)) {
            return null;
        }

        if (!InputValidator::validAttributes($attributes, $operation)) {
            return null;
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'featureFlagName' => $featureFlag
        );
    }

    /**
     * Executes evaluation for getTreatment or getTreatmentWithConfig
     *
     * @param $operation
     * @param $metricName
     * @param $key
     * @param $featureFlagName
     * @param $attributes
     *
     * @return mixed
     */
    private function doEvaluation($operation, $key, $featureFlagName, $attributes)
    {
        $default = array('treatment' => TreatmentEnum::CONTROL, 'config' => null);

        $inputValidation = $this->doInputValidationForTreatment($key, $featureFlagName, $attributes, $operation);
        if (is_null($inputValidation)) {
            return $default;
        }
        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $featureFlagName = $inputValidation['featureFlagName'];
        try {
            $result = $this->evaluator->evaluateFeature($matchingKey, $bucketingKey, $featureFlagName, $attributes);
            if (!InputValidator::isSplitFound($result['impression']['label'], $featureFlagName, $operation)) {
                return $default;
            }
            // Creates impression
            $impression = $this->createImpression(
                $matchingKey,
                $featureFlagName,
                $result['treatment'],
                $result['impression']['changeNumber'],
                $result['impression']['label'],
                $bucketingKey
            );

            $this->registerData($impression, $attributes);
            return array(
                'treatment' => $result['treatment'],
                'config' => $result['config'],
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }

        try {
            // Creates impression
            $impression = $this->createImpression(
                $matchingKey,
                $featureFlagName,
                TreatmentEnum::CONTROL,
                -1, // At this point we have no information on the real changeNumber (redis might have failed)
                ImpressionLabel::EXCEPTION,
                $bucketingKey
            );
            $this->registerData($impression, $attributes);
        } catch (\Exception $e) {
            SplitApp::logger()->critical(
                "An error occurred when attempting to log impression for " .
                "featureFlagName: $featureFlagName, key: $matchingKey"
            );
            SplitApp::logger()->critical($e);
        }
        return $default;
    }

    /**
     * @inheritdoc
     */
    public function getTreatment($key, $featureName, array $attributes = null)
    {
        try {
            $result = $this->doEvaluation(
                'getTreatment',
                $key,
                $featureName,
                $attributes
            );
            return $result['treatment'];
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatment method is throwing exceptions');
            return TreatmentEnum::CONTROL;
        }
    }

    /**
     * @inheritdoc
     */
    public function getTreatmentWithConfig($key, $featureFlagName, array $attributes = null)
    {
        try {
            return $this->doEvaluation(
                'getTreatmentWithConfig',
                $key,
                $featureFlagName,
                $attributes
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentWithConfig method is throwing exceptions');
            return array('treatment' => TreatmentEnum::CONTROL, 'config' => null);
        }
    }

    /**
     * Verifies inputs for getTreatments and getTreatmentsWithConfig methods
     *
     * @param $key
     * @param $featureNames
     * @param $attributes
     * @param $operation
     *
     * @return null|mixed
     */
    private function doInputValidationForTreatments($key, $featureFlagNames, array $attributes = null, $operation)
    {
        $featureFlags = InputValidator::validateFeatureFlagNames($featureFlagNames, $operation);
        if (is_null($featureFlags)) {
            return null;
        }

        $key = InputValidator::validateKey($key, $operation);
        if (is_null($key) || !InputValidator::validAttributes($attributes, $operation)) {
            return array(
                'controlTreatments' => array_fill_keys(
                    $featureFlags,
                    array('treatment' => TreatmentEnum::CONTROL, 'config' => null)
                ),
            );
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'featureFlagNames' => $featureFlags,
        );
    }

    private function registerData($impressions, $attributes)
    {
        try {
            if (is_null($impressions) || (is_array($impressions) && 0 == count($impressions))) {
                return;
            }
            $toStore = (is_array($impressions)) ? $impressions : array($impressions);
            $this->impressionCache->logImpressions($toStore, $this->queueMetadata);
            if (isset($this->impressionListener)) {
                $this->impressionListener->sendDataToClient($toStore, $attributes);
            }
        } catch (\Exception $e) {
            SplitApp::logger()->warning($e->getMessage());
            SplitApp::logger()->critical(
                ': An exception occurred when trying to store impressions.'
            );
        }
    }

    /**
     * Executes evaluation for getTreatments or getTreatmentsWithConfig
     *
     * @param $operation
     * @param $metricName
     * @param $key
     * @param $featureFlagNames
     * @param $attributes
     *
     * @return mixed
     */
    private function doEvaluationForTreatments($operation, $key, $featureFlagNames, $attributes)
    {
        $inputValidation = $this->doInputValidationForTreatments($key, $featureFlagNames, $attributes, $operation);
        if (is_null($inputValidation)) {
            return array();
        }
        if (isset($inputValidation['controlTreatments'])) {
            return $inputValidation['controlTreatments'];
        }

        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $featureFlags = $inputValidation['featureFlagNames'];

        try {
            $evaluationResults = $this->evaluator->evaluateFeatures(
                $matchingKey,
                $bucketingKey,
                $featureFlags,
                $attributes
            );
            return $this->processEvaluations(
                $matchingKey,
                $bucketingKey,
                $operation,
                $attributes,
                $evaluationResults['evaluations']
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getTreatments($key, $featureFlagNames, array $attributes = null)
    {
        try {
            return array_map(
                function ($feature) {
                    return $feature['treatment'];
                },
                $this->doEvaluationForTreatments(
                    'getTreatments',
                    $key,
                    $featureFlagNames,
                    $attributes
                )
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatments method is throwing exceptions');
            $featureFlags = InputValidator::validateFeatureFlagNames($featureFlagNames, 'getTreatments');
            return is_null($featureFlags) ? array() : array_fill_keys($featureFlags, TreatmentEnum::CONTROL);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTreatmentsWithConfig($key, $featureFlagNames, array $attributes = null)
    {
        try {
            return $this->doEvaluationForTreatments(
                'getTreatmentsWithConfig',
                $key,
                $featureFlagNames,
                $attributes
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsWithConfig method is throwing exceptions');
            $featureFlags = InputValidator::validateFeatureFlagNames($featureFlagNames, 'getTreatmentsWithConfig');
            return is_null($featureFlags) ? array() :
                array_fill_keys($featureFlags, array('treatment' => TreatmentEnum::CONTROL, 'config' => null));
        }
    }

    /**
     * @inheritdoc
     */
    public function isTreatment($key, $featureFlagName, $treatment)
    {
        try {
            $calculatedTreatment = $this->getTreatment($key, $featureFlagName);

            if ($calculatedTreatment !== TreatmentEnum::CONTROL) {
                if ($treatment == $calculatedTreatment) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->critical("SDK Client on isTreatment is critical");
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function track($key, $trafficType, $eventType, $value = null, $properties = null)
    {
        $key = InputValidator::validateTrackKey($key);
        $trafficType = InputValidator::validateTrafficType($this->splitCache, $trafficType);
        $eventType = InputValidator::validateEventType($eventType);
        $value = InputValidator::validateValue($value);
        $properties = InputValidator::validProperties($properties);

        if (is_null($key) || is_null($trafficType) || is_null($eventType) || $value === false
            || $properties === false) {
            return false;
        }

        try {
            $eventDTO = new EventDTO($key, $trafficType, $eventType, $value, $properties);
            $eventQueueMessage = new EventQueueMessage($this->queueMetadata, $eventDTO);
            return $this->eventCache->addEvent($eventQueueMessage);
        } catch (\Exception $exception) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->error("Error happened when trying to add events");
            SplitApp::logger()->debug($exception->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }

    public function getTreatmentsByFlagSets($key, $flagSets, array $attributes = null)
    {
        try {
            return array_map(
                function ($feature) {
                    return $feature['treatment'];
                },
                $this->doEvaluationByFlagSets(
                    'getTreatmentsByFlagSets',
                    $key,
                    $flagSets,
                    $attributes
                )
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsByFlagSets method is throwing exceptions');
            return array();
        }
    }

    public function getTreatmentsWithConfigByFlagSets($key, $flagSets, array $attributes = null)
    {
        try {
            return $this->doEvaluationByFlagSets(
                'getTreatmentsWithConfigByFlagSets',
                $key,
                $flagSets,
                $attributes
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsWithConfigByFlagSets method is throwing exceptions');
            return array();
        }
    }

    public function getTreatmentsByFlagSet($key, $flagSet, array $attributes = null)
    {
        try {
            return array_map(
                function ($feature) {
                    return $feature['treatment'];
                },
                $this->doEvaluationByFlagSets(
                    'getTreatmentsByFlagSet',
                    $key,
                    array($flagSet),
                    $attributes
                )
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsByFlagSet method is throwing exceptions');
            return array();
        }
    }

    public function getTreatmentsWithConfigByFlagSet($key, $flagSet, array $attributes = null)
    {
        try {
            return $this->doEvaluationByFlagSets(
                'getTreatmentsWithConfigByFlagSet',
                $key,
                array($flagSet),
                $attributes
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsWithConfigByFlagSet method is throwing exceptions');
            return array();
        }
    }

    private function doInputValidationByFlagSets($key, $flagSets, array $attributes = null, $operation)
    {
        $key = InputValidator::validateKey($key, $operation);
        if (is_null($key) || !InputValidator::validAttributes($attributes, $operation)) {
            return null;
        }

        $sets = FlagSetsValidator::areValid($flagSets, $operation);
        if (is_null($sets)) {
            return null;
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'flagSets' => $sets,
        );
    }

    private function doEvaluationByFlagSets($operation, $key, $flagSets, $attributes)
    {
        $inputValidation = $this->doInputValidationByFlagSets($key, $flagSets, $attributes, $operation);
        if (is_null($inputValidation)) {
            return array();
        }

        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $flagSets = $inputValidation['flagSets'];

        try {
            $evaluationResults = $this->evaluator->evaluateFeaturesByFlagSets(
                $matchingKey,
                $bucketingKey,
                $flagSets,
                $attributes
            );
            return $this->processEvaluations(
                $matchingKey,
                $bucketingKey,
                $operation,
                $attributes,
                $evaluationResults['evaluations']
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }
        return array();
    }

    private function processEvaluations(
        $matchingKey,
        $bucketingKey,
        $operation,
        $attributes,
        $evaluations
    ) {
        $result = array();
        $impressions = array();
        foreach ($evaluations as $featureFlagName => $evalResult) {
            if (InputValidator::isSplitFound($evalResult['impression']['label'], $featureFlagName, $operation)) {
                // Creates impression
                $impressions[] = $this->createImpression(
                    $matchingKey,
                    $featureFlagName,
                    $evalResult['treatment'],
                    $evalResult['impression']['changeNumber'],
                    $evalResult['impression']['label'],
                    $bucketingKey
                );
                $result[$featureFlagName] = array(
                    'treatment' => $evalResult['treatment'],
                    'config' => $evalResult['config'],
                );
            } else {
                $result[$featureFlagName] = array('treatment' => TreatmentEnum::CONTROL, 'config' => null);
            }
        }
        $this->registerData($impressions, $attributes);
        return $result;
    }
}
