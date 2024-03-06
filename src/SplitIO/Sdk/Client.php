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
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\EventsCache;

class Client implements ClientInterface
{
    private $evaluator = null;
    private $impressionListener = null;
    private $queueMetadata = null;

    /**
     * Flag to get Impression's labels feature enabled
     * @var bool
     */
    private bool $labelsEnabled;

    /**
     * @var \SplitIO\Component\Cache\SplitCache
     */
    private SplitCache $splitCache;

    /**
     * @var \SplitIO\Component\Cache\SegmentCache
     */
    private SegmentCache $segmentCache;

    /**
     * @var \SplitIO\Component\Cache\ImpressionCache
     */
    private ImpressionCache $impressionCache;

    /**
     * @var \SplitIO\Component\Cache\EventsCache
     */
    private EventsCache $eventCache;

    /**
     * @param array $storages
     * @param array $options
     */
    public function __construct(array $storages, ?array $options = array())
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
     * @param string $key
     * @param string $featureFlag
     * @param string $treatment
     * @param int $changeNumber
     * @param string|null $label
     * @param string|null $bucketingKey
     *
     * @return \SplitIO\Sdk\Impressions\Impression
     */
    private function createImpression(
        string $key,
        string $featureFlag,
        string $treatment, 
        int $changeNumber,
        ?string $label = '',
        ?string $bucketingKey = null)
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
     * @param string|Key $key
     * @param string $featureFlagName
     * @param string $operation
     * @param array|null $attributes
     *
     * @return null|mixed
     */
    private function doInputValidationForTreatment(string|Key $key, string $featureFlagName, string $operation, ?array $attributes = null)
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
     * @param string $operation
     * @param string|Key $key
     * @param string $featureFlagName
     * @param array $attributes
     *
     * @return mixed
     */
    private function doEvaluation(string $operation, string|Key $key, string $featureFlagName, ?array $attributes)
    {
        $default = array('treatment' => TreatmentEnum::CONTROL, 'config' => null);

        $inputValidation = $this->doInputValidationForTreatment($key, $featureFlagName, $operation, $attributes);
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

            $this->registerData(array($impression), $attributes);
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
            $this->registerData(array($impression), $attributes);
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
    public function getTreatment(string|Key $key, string $featureName, ?array $attributes = null): string
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
    public function getTreatmentWithConfig(string|Key $key, string $featureFlagName, ?array $attributes = null): array
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
     * @param string|Key $key
     * @param array $featureNames
     * @param string $operation
     * @param array|null $attributes
     *
     * @return null|mixed
     */
    private function doInputValidationForTreatments(string|Key $key, array $featureFlagNames, string $operation, ?array $attributes = null)
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

    private function registerData(array $impressions, ?array $attributes)
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
     * @param string $operation
     * @param string|Key $key
     * @param array $featureFlagNames
     * @param array $attributes
     *
     * @return mixed
     */
    private function doEvaluationForTreatments(string $operation, string|Key $key, array $featureFlagNames, ?array $attributes)
    {
        $inputValidation = $this->doInputValidationForTreatments($key, $featureFlagNames, $operation, $attributes);
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
    public function getTreatments(string|Key $key, array $featureFlagNames, ?array $attributes = null): array
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
    public function getTreatmentsWithConfig(string|Key $key, array $featureFlagNames, ?array $attributes = null): array
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
    public function isTreatment(string|Key $key, string $featureFlagName, string $treatment): bool
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
    public function track(string $key, string $trafficType, string $eventType, ?float $value = null, ?array $properties = null): bool
    {
        $key = InputValidator::validateTrackKey($key);
        $trafficType = InputValidator::validateTrafficType($this->splitCache, $trafficType);
        $eventType = InputValidator::validateEventType($eventType);
        $value = InputValidator::validateValue($value);
        $properties = InputValidator::validProperties($properties);

        if (
            is_null($key) || is_null($trafficType) || is_null($eventType) || $value === false
            || $properties === false
        ) {
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

    /**
     * @inheritdoc
     */
    public function getTreatmentsByFlagSets(string|Key $key, array $flagSets, ?array $attributes = null): array
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

    /**
     * @inheritdoc
     */
    public function getTreatmentsWithConfigByFlagSets(string|Key $key, array $flagSets, ?array $attributes = null): array
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

    /**
     * @inheritdoc
     */
    public function getTreatmentsByFlagSet(string|Key $key, string $flagSet, ?array $attributes = null): array
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

    /**
     * @inheritdoc
     */
    public function getTreatmentsWithConfigByFlagSet(string|Key $key, string $flagSet, ?array $attributes = null): array
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

    private function doInputValidationByFlagSets(string|Key $key, array $flagSets, string $operation, ?array $attributes = null): ?array
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

    private function doEvaluationByFlagSets(string $operation, string|Key $key, array $flagSets, ?array $attributes): array
    {
        $inputValidation = $this->doInputValidationByFlagSets($key, $flagSets, $operation, $attributes);
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
        string $matchingKey,
        ?string $bucketingKey,
        string $operation,
        ?array $attributes,
        array $evaluations
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
