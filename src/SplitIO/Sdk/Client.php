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
        $key = InputValidator::validateKey($key);
        if (is_null($key)) {
            return TreatmentEnum::CONTROL;
        }

        $featureName = InputValidator::validateFeatureName($featureName);
        if (is_null($featureName)) {
            return TreatmentEnum::CONTROL;
        }

        $matchingKey = $key['matchingKey'];
        $bucketingKey = $key['bucketingKey'];

        $impressionLabel = ImpressionLabel::EXCEPTION;

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
                Metrics::MNAME_SDK_GET_TREATMENT,
                Metrics::getBucketForLatencyMicros($result['metadata']['latency'])
            );

            return $result['treatment'];
        } catch (InvalidMatcherException $ie) {
            SplitApp::logger()->critical('Exception due an INVALID MATCHER');
            $impressionLabel = ImpressionLabel::MATCHER_NOT_FOUND;
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatment method is throwing exceptions');
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
                "feature: $featureName, key: $key"
            );
        }
        return TreatmentEnum::CONTROL;
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
            $splitNames = InputValidator::validateGetTreatments($featureNames);

            if (is_null($splitNames)) {
                return null;
            }

            $result = array();
            for ($i = 0; $i < count($splitNames); $i++) {
                $featureName = $splitNames[$i];
                $result[$featureName] = $this->getTreatment($key, $featureName, $attributes);
            }
            return $result;
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatments method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }

        return null;
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
