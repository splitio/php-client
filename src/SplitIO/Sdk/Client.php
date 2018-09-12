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

class Client implements ClientInterface
{

    private $evaluator = null;

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
    }


    /**
     * @param $matchingKey
     * @param $feature
     * @param $treatment
     * @param string $label
     * @param null $time
     * @param int $changeNumber
     * @param string $bucketingKey
     */
    private function logImpression(
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
        TreatmentImpression::log($impression);
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
        // Key Validation
        if (is_null($key)) {
            SplitApp::logger()->critical('getTreatment: key cannot be null.');
            return TreatmentEnum::CONTROL;
        }

        // Feature Name Validation
        if (is_null($featureName)) {
            SplitApp::logger()->critical('getTreatment: featureName cannot be null.');
            return TreatmentEnum::CONTROL;
        }
        if (!is_string($featureName)) {
            SplitApp::logger()->critical('getTreatment: featureName ' .json_encode($featureName)
                . ' has to be of type "string".');
            return TreatmentEnum::CONTROL;
        }

        //Getting Matching key and Bucketing key
        if ($key instanceof Key) {
            $matchingKey = $key->getMatchingKey();
            $bucketingKey = $key->getBucketingKey();
        } else {
            $strKey = \SplitIO\toString($key, 'key', 'getTreatment:');
            if ($strKey !== false) {
                $matchingKey = $strKey;
                $bucketingKey = null;
            } else {
                SplitApp::logger()->critical('getTreatment: key has to be of type "string" or "SplitIO\Sdk\Key".');
                return TreatmentEnum::CONTROL;
            }
        }

        $impressionLabel = ImpressionLabel::EXCEPTION;

        try {
            $result = $this->evaluator->evalTreatment($matchingKey, $bucketingKey, $featureName, $attributes);

            // Register impression
            $this->logImpression(
                $matchingKey,
                $featureName,
                $result['treatment'],
                $result['impression']['label'],
                $bucketingKey,
                $result['impression']['changeNumber']
            );
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
            $this->logImpression(
                $matchingKey,
                $featureName,
                TreatmentEnum::CONTROL,
                $impressionLabel,
                $bucketingKey
            );
        } catch (\Exception $e) {
            SplitApp::logger()->critical(
                "An error occurred when attempting to log impression for " .
                "feature: $featureName, key: $key"
            );
        }
        return TreatmentEnum::CONTROL;
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
        // Key Validation
        if (is_null($key)) {
            SplitApp::logger()->critical('track: key cannot be null.');
            return false;
        }

        // Event Type Validation
        if (is_null($eventType)) {
            SplitApp::logger()->critical('track: eventType cannot be null.');
            return false;
        }
        if (!is_string($eventType)) {
            SplitApp::logger()->critical('track: eventType must be a string.');
            return false;
        }
        if (!preg_match('/[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}/', $eventType)) {
            SplitApp::logger()->critical('track: eventType must adhere to the regular expression '
                . '[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}.');
            return false;
        }

        // Trafic Type Validation
        if (is_null($trafficType)) {
            SplitApp::logger()->critical('track: trafficType cannot be null.');
            return false;
        }
        if (!is_string($trafficType)) {
            SplitApp::logger()->critical('track: trafficType must be a string.');
            return false;
        }
        if (empty($trafficType)) {
            SplitApp::logger()->critical('track: trafficType must not be an empty string.');
            return false;
        }

        // Value Validation
        if (is_null($value) || !(is_int($value) || is_float($value))) {
            SplitApp::logger()->critical('track: value must be a number.');
            return false;
        }

        try {
            $strKey = \SplitIO\toString($key, 'key', 'track:');
            if ($strKey !== false) {
                $eventDTO = new EventDTO($key, $trafficType, $eventType, $value);
                $eventMessageMetadata = new EventQueueMetadataMessage();
                $eventQueueMessage = new EventQueueMessage($eventMessageMetadata, $eventDTO);

                return EventsCache::addEvent($eventQueueMessage);
            } else {
                SplitApp::logger()->critical('track: key must be a string.');
                return false;
            }
        } catch (\Exception $exception) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->error("Error happens trying aadd events");
            SplitApp::logger()->debug($exception->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }
}
