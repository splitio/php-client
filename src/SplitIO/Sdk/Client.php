<?php
namespace SplitIO\Sdk;

use SplitIO\Component\Cache\EventsCache;
use SplitIO\Component\Common\Di;
use SplitIO\Metrics;
use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Sdk\Events\EventDTO;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Sdk\QueueMetadataMessage;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\TreatmentImpression;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Validator\InputValidator;

class Client implements ClientInterface
{

    private $evaluator = null;
    private $impressionListener = null;

    /**
     * Flag to get Impression's labels feature enabled
     * @var bool
     */
    private $labelsEnabled;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->labelsEnabled = isset($options['labelsEnabled']) ? $options['labelsEnabled'] : true;
        $this->evaluator = new Evaluator();
        Di::setEvaluator($this->evaluator);
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
     * @param $feature
     * @param $treatment
     * @param string $label
     * @param null $time
     * @param int $changeNumber
     * @param string $bucketingKey
     *
     * @return \SplitIO\Sdk\Impressions\Impression
     */
    private function createImpression($key, $feature, $treatment, $changeNumber, $label = '', $bucketingKey = null)
    {
        if (!$this->labelsEnabled) {
            $label = null;
        }
        $impression = new Impression($key, $feature, $treatment, $label, null, $changeNumber, $bucketingKey);
        return $impression;
    }

    /**
     * Verifies inputs for getTreatment and getTreatmentWithConfig methods
     *
     * @param $key
     * @param $featureName
     * @param $attributes
     * @param $operation
     *
     * @return null|mixed
     */
    private function doInputValidationForTreatment($key, $featureName, array $attributes = null, $operation)
    {
        $key = InputValidator::validateKey($key, $operation);
        if (is_null($key)) {
            return null;
        }

        $featureName = InputValidator::validateFeatureName($featureName, $operation);
        if (is_null($featureName)) {
            return null;
        }

        if (!InputValidator::validAttributes($attributes, $operation)) {
            return null;
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'featureName' => $featureName
        );
    }

    /**
     * Executes evaluation for getTreatment or getTreatmentWithConfig
     *
     * @param $operation
     * @param $metricName
     * @param $key
     * @param $featureName
     * @param $attributes
     *
     * @return mixed
     */
    private function doEvaluation($operation, $metricName, $key, $featureName, $attributes)
    {
        $registry = Di::getStaticCache();
        $staticCacheKey = json_encode([$operation, $metricName, $key, $featureName, $attributes]);
        if ($registry->has($staticCacheKey)) {
            return $registry->get($staticCacheKey);
        }
        $default = array('treatment' => TreatmentEnum::CONTROL, 'config' => null);

        $inputValidation = $this->doInputValidationForTreatment($key, $featureName, $attributes, $operation);
        if (is_null($inputValidation)) {
            return $registry->add($staticCacheKey, $default);
        }
        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $featureName = $inputValidation['featureName'];
        try {
            $result = $this->evaluator->evaluateFeature($matchingKey, $bucketingKey, $featureName, $attributes);
            if (!InputValidator::isSplitFound($result['impression']['label'], $featureName, $operation)) {
                return $registry->add($staticCacheKey, $default);
            }
            // Creates impression
            $impression = $this->createImpression(
                $matchingKey,
                $featureName,
                $result['treatment'],
                $result['impression']['changeNumber'],
                $result['impression']['label'],
                $bucketingKey
            );

            $this->registerData($impression, $attributes, $metricName, $result['latency']);
            return $registry->add($staticCacheKey, array(
                'treatment' => $result['treatment'],
                'config' => $result['config'],
            ));
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }

        try {
            // Creates impression
            $impression = $this->createImpression(
                $matchingKey,
                $featureName,
                TreatmentEnum::CONTROL,
                -1, // At this point we have no information on the real changeNumber (redis might have failed)
                ImpressionLabel::EXCEPTION,
                $bucketingKey
            );
            $this->registerData($impression, $attributes, $metricName);
        } catch (\Exception $e) {
            SplitApp::logger()->critical(
                "An error occurred when attempting to log impression for " .
                "feature: $featureName, key: $matchingKey"
            );
            SplitApp::logger()->critical($e);
        }
        return $registry->add($staticCacheKey, $default);
    }

    /**
     * @inheritdoc
     */
    public function getTreatment($key, $featureName, array $attributes = null)
    {
        try {
            $result = $this->doEvaluation(
                'getTreatment',
                Metrics::MNAME_SDK_GET_TREATMENT,
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
    public function getTreatmentWithConfig($key, $featureName, array $attributes = null)
    {
        try {
            return $this->doEvaluation(
                'getTreatmentWithConfig',
                Metrics::MNAME_SDK_GET_TREATMENT_WITH_CONFIG,
                $key,
                $featureName,
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
    private function doInputValidationForTreatments($key, $featureNames, array $attributes = null, $operation)
    {
        $splitNames = InputValidator::validateFeatureNames($featureNames, $operation);
        if (is_null($splitNames)) {
            return null;
        }

        $key = InputValidator::validateKey($key, $operation);
        if (is_null($key) || !InputValidator::validAttributes($attributes, $operation)) {
            return array(
                'controlTreatments' => array_fill_keys(
                    $splitNames,
                    array('treatment' => TreatmentEnum::CONTROL, 'config' => null)
                ),
            );
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'featureNames' => $splitNames,
        );
    }

    private function registerData($impressions, $attributes, $metricName, $latency = null)
    {
        try {
            TreatmentImpression::log($impressions, $this->queueMetadata);
            if (isset($this->impressionListener)) {
                $this->impressionListener->sendDataToClient($impressions, $attributes);
            }

            //Register latency value
            if (!is_null($latency)) {
                MetricsCache::addLatencyOnBucket($metricName, Metrics::getBucketForLatencyMicros($latency));
            }
        } catch (\Exception $e) {
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
     * @param $featureNames
     * @param $attributes
     *
     * @return mixed
     */
    private function doEvaluationForTreatments($operation, $metricName, $key, $featureNames, $attributes)
    {
        $inputValidation = $this->doInputValidationForTreatments($key, $featureNames, $attributes, $operation);
        if (is_null($inputValidation)) {
            return array();
        }
        if (isset($inputValidation['controlTreatments'])) {
            return $inputValidation['controlTreatments'];
        }

        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $splitNames = $inputValidation['featureNames'];

        try {
            $result = array();
            $impressions = array();
            $evaluationResults = $this->evaluator->evaluateFeatures(
                $matchingKey,
                $bucketingKey,
                $splitNames,
                $attributes
            );
            foreach ($evaluationResults['evaluations'] as $splitName => $evalResult) {
                if (InputValidator::isSplitFound($evalResult['impression']['label'], $splitName, $operation)) {
                    // Creates impression
                    $impressions[] = $this->createImpression(
                        $matchingKey,
                        $splitName,
                        $evalResult['treatment'],
                        $evalResult['impression']['changeNumber'],
                        $evalResult['impression']['label'],
                        $bucketingKey
                    );
                    $result[$splitName] = array(
                        'treatment' => $evalResult['treatment'],
                        'config' => $evalResult['config'],
                    );
                } else {
                    $result[$splitName] = array('treatment' => TreatmentEnum::CONTROL, 'config' => null);
                }
            }
            $this->registerData($impressions, $attributes, $metricName, $evaluationResults['latency']);
            return $result;
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
    public function getTreatments($key, $featureNames, array $attributes = null)
    {
        try {
            return array_map(
                function ($feature) {
                    return $feature['treatment'];
                },
                $this->doEvaluationForTreatments(
                    'getTreatments',
                    Metrics::MNAME_SDK_GET_TREATMENTS,
                    $key,
                    $featureNames,
                    $attributes
                )
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatments method is throwing exceptions');
            $splitNames = InputValidator::validateFeatureNames($featureNames, 'getTreatments');
            return is_null($splitNames) ? array() : array_fill_keys($splitNames, TreatmentEnum::CONTROL);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTreatmentsWithConfig($key, $featureNames, array $attributes = null)
    {
        try {
            return $this->doEvaluationForTreatments(
                'getTreatmentsWithConfig',
                Metrics::MNAME_SDK_GET_TREATMENTS_WITH_CONFIG,
                $key,
                $featureNames,
                $attributes
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatmentsWithConfig method is throwing exceptions');
            $splitNames = InputValidator::validateFeatureNames($featureNames, 'getTreatmentsWithConfig');
            return is_null($splitNames) ? array() :
                array_fill_keys($splitNames, array('treatment' => TreatmentEnum::CONTROL, 'config' => null));
        }
    }

    /**
     * @inheritdoc
     */
    public function isTreatment($key, $featureName, $treatment)
    {
        try {
            $calculatedTreatment = $this->getTreatment($key, $featureName);

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
        $trafficType = InputValidator::validateTrafficType($trafficType);
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
            return EventsCache::addEvent($eventQueueMessage);
        } catch (\Exception $exception) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->error("Error happened when trying to add events");
            SplitApp::logger()->debug($exception->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }
}
