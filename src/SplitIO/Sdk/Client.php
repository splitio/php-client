<?php
namespace SplitIO\Sdk;

use SplitIO\Component\Cache\EventsCache;
use SplitIO\Exception\InvalidMatcherException;
use SplitIO\Metrics;
use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Sdk\Events\EventDTO;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Sdk\Events\EventQueueMetadataMessage;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Split;
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
        $this->evaluator = new Evaluator($options);

        if (isset($options['impressionListener'])) {
            $this->impressionListener = new \SplitIO\Sdk\ImpressionListenerWrapper($options['impressionListener']);
        }
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
    private function createImpression(
        $matchingKey,
        $feature,
        $treatment,
        $label = '',
        $bucketingKey = null,
        $changeNumber = -1,
        $time = null
    ) {
        if (!$this->labelsEnabled) {
            $label = null;
        }

        $impression = new Impression(
            $matchingKey,
            $feature,
            $treatment,
            $label,
            $time,
            $changeNumber,
            $bucketingKey
        );

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
            'featureName' => $featureName,
            'impressionLabel' => ImpressionLabel::EXCEPTION
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
        $default = array(
            'treatment' => TreatmentEnum::CONTROL,
            'config' => null
        );

        $inputValidation = $this->doInputValidationForTreatment($key, $featureName, $attributes, $operation);
        if (is_null($inputValidation)) {
            return $default;
        }

        $matchingKey = $inputValidation['matchingKey'];
        $bucketingKey = $inputValidation['bucketingKey'];
        $featureName = $inputValidation['featureName'];
        $impressionLabel = $inputValidation['impressionLabel'];

        try {
            $result = $this->evaluator->evalTreatment($matchingKey, $bucketingKey, $featureName, $attributes);

            // Creates impression
            $impression = $this->createImpression(
                $matchingKey,
                $featureName,
                $result['treatment'],
                $result['impression']['label'],
                $bucketingKey,
                $result['impression']['changeNumber']
            );

            // Register impression
            TreatmentImpression::log($impression);

            // Provides logic to send data to Client
            if (isset($this->impressionListener)) {
                $this->impressionListener->sendDataToClient($impression, $attributes);
            }

            //Register latency value
            MetricsCache::addLatencyOnBucket(
                $metricName,
                Metrics::getBucketForLatencyMicros($result['metadata']['latency'])
            );

            return array(
                'treatment' => $result['treatment'],
                'config' => $result['config'],
            );
        } catch (InvalidMatcherException $ie) {
            SplitApp::logger()->critical('Exception due an INVALID MATCHER');
            $impressionLabel = ImpressionLabel::MATCHER_NOT_FOUND;
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
                $impressionLabel,
                $bucketingKey
            );

            // Register impression
            TreatmentImpression::log($impression);

            // Provides logic to send data to Client
            if (isset($this->impressionListener)) {
                $this->impressionListener->sendDataToClient($impression, $attributes);
            }
        } catch (\Exception $e) {
            SplitApp::logger()->critical(
                "An error occurred when attempting to log impression for " .
                "feature: $featureName, key: $matchingKey"
            );
            SplitApp::logger()->critical($e);
        }
        return $default;
    }

    /**
     * Returns the treatment to show this id for this feature.
     * The set of treatments for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature</li>
     *     <li>The feature was deleted through the web console.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never  returns null.
     *
     * @param $key
     * @param $featureName
     * @param $attributes
     * @return string
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
     * Returns an object with the treatment to show this id for this feature
     * and the config provided.
     * The set of treatments and config for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature</li>
     *     <li>The feature was deleted through the web console.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * This method returns null configuration if:
     * <ol>
     *     <li>config was not set up</li>
     * </ol>
     * @param $key
     * @param $featureName
     * @param $attributes
     * @return string
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
            return array(
                'treatment' => TreatmentEnum::CONTROL,
                'config' => null,
            );
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
                'controlTreatments' => InputValidator::generateControlTreatments($splitNames),
            );
        }

        return array(
            'matchingKey' => $key['matchingKey'],
            'bucketingKey' => $key['bucketingKey'],
            'featureNames' => $splitNames,
        );
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
        $latency = 0;
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
            foreach ($splitNames as $splitName) {
                try {
                    $evalResult = $this->evaluator->evalTreatment($matchingKey, $bucketingKey, $splitName, $attributes);
                    $result[$splitName] = array(
                        'treatment' => $evalResult['treatment'],
                        'config' => $evalResult['config'],
                    );

                    $latency += $evalResult['metadata']['latency'];

                    // Creates impression
                    $impressions[] = $this->createImpression(
                        $matchingKey,
                        $splitName,
                        $evalResult['treatment'],
                        $evalResult['impression']['label'],
                        $bucketingKey,
                        $evalResult['impression']['changeNumber']
                    );
                } catch (\Exception $e) {
                    $result[$splitName] = array(
                        'treatment' => TreatmentEnum::CONTROL,
                        'config' => null,
                    );
                    SplitApp::logger()->critical(
                        $operation . ': An exception occured when evaluating feature: '. $splitName . '. skipping it'
                    );
                    continue;
                }
            }

            // Register impressions
            try {
                if (!empty($impressions)) {
                    TreatmentImpression::log($impressions);
                    if (isset($this->impressionListener)) {
                        foreach ($impressions as $impression) {
                            $this->impressionListener->sendDataToClient($impression, $attributes);
                        }
                    }
                }

                //Register latency value
                MetricsCache::addLatencyOnBucket(
                    $metricName,
                    Metrics::getBucketForLatencyMicros($latency)
                );
            } catch (\Exception $e) {
                SplitApp::logger()->critical(
                    $operation . ': An exception occured when trying to store impressions.'
                );
            }

            return $result;
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }
        return array();
    }

    /**
     * Returns an associative array which each key will be
     * the treatment result for each feature passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureNames is invalid/li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * @param $key
     * @param $featureNames
     * @param $attributes
     * @return array|control
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
            SplitApp::logger()->critical('getTreatmens method is throwing exceptions');
            $splitNames = InputValidator::validateFeatureNames($featureNames, 'getTreatments');
            return !is_null($splitNames) ?
            array_map(
                function ($feature) {
                    return $feature['treatment'];
                },
                InputValidator::generateControlTreatments($splitNames)
            ) : array();
        }
    }

    /**
     * Returns an associative array which each key will be
     * the treatment result and the config for each
     * feature passed as parameter.
     * The set of treatments for a feature can be configured
     * on the Split web console and the config for
     * that treatment.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>featureNames is invalid/li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never returns null.
     *
     * @param $key
     * @param $featureNames
     * @param $attributes
     * @return array|control
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
            return !is_null($splitNames) ? InputValidator::generateControlTreatments($splitNames) : array();
        }
    }

    /**
     * A short-hand for
     * <pre>
     *     (getTreatment(key, feature) == treatment) ? true : false;
     * </pre>
     *
     * This method never throws exceptions.
     * Instead of throwing  exceptions, it returns false.
     *
     * @param $key
     * @param $featureName
     * @param $treatment
     * @return bool
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
    public function track($key, $trafficType, $eventType, $value = null)
    {
        $key = InputValidator::validateTrackKey($key);
        $trafficType = InputValidator::validateTrafficType($trafficType);
        $eventType = InputValidator::validateEventType($eventType);
        $value = InputValidator::validateValue($value);

        if (is_null($key) || is_null($trafficType) || is_null($eventType) || ($value === false)) {
            return false;
        }

        try {
            $eventDTO = new EventDTO($key, $trafficType, $eventType, $value);
            $eventMessageMetadata = new EventQueueMetadataMessage();
            $eventQueueMessage = new EventQueueMessage($eventMessageMetadata, $eventDTO);
            return EventsCache::addEvent($eventQueueMessage);
        } catch (\Exception $exception) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->error("Error happens trying aadd events");
            SplitApp::logger()->debug($exception->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }
}
