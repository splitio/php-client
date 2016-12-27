<?php
namespace SplitIO\Sdk;

use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Memory\Exception\OpenSharedMemoryException;
use SplitIO\Component\Memory\Exception\ReadSharedMemoryException;
use SplitIO\Component\Memory\Exception\SupportSharedMemoryException;
use SplitIO\Component\Memory\Exception\WriteSharedMemoryException;
use SplitIO\Component\Memory\SharedMemory;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;
use SplitIO\Metrics;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\TreatmentImpression;
use SplitIO\Split as SplitApp;

class Client implements ClientInterface
{

    /**
     * Size of memory block in bytes
     * @var int
     */
    private $smSize;

    /**
     * mode of shared memory block
     * @var int
     */
    private $smMode;

    /**
     * Time to live of data in shared memory block
     * @var int
     */
    private $smTtl;


    /**
     * Seed to generate an integer key
     * @var int
     */
    private $smKeySeed;

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
        $this->smSize        = isset($options['memory']['size']) ? $options['memory']['size'] : 40000;
        $this->smMode        = isset($options['memory']['mode']) ? $options['memory']['mode'] : 0644;
        $this->smTtl         = isset($options['memory']['ttl'])  ? $options['memory']['ttl']  : 60;
        $this->smKeySeed     = isset($options['memory']['seed']) ? $options['memory']['seed'] : 123123;
        $this->labelsEnabled = isset($options['labelsEnabled']) ? $options['labelsEnabled'] : true;
    }

    private function getSmKey($featureName)
    {
        return \SplitIO\murmurhash3_int('feature::'.$featureName, $this->smKeySeed);
    }

    /**
     * @param $featureName
     * @return null|\SplitIO\Grammar\Split
     */
    private function getCachedFeature($featureName)
    {
        $ikey = $this->getSmKey($featureName);

        $value = null;

        try {
            $value = SharedMemory::read($ikey, $this->smMode, $this->smSize);

            if (!($value instanceof Split)) {
                return null;
            }
        } catch (SupportSharedMemoryException $se) {
            SplitApp::logger()->warning($se->getMessage());
        } catch (OpenSharedMemoryException $oe) {
            SplitApp::logger()->warning($oe->getMessage());
        } catch (ReadSharedMemoryException $re) {
            SplitApp::logger()->error($re->getMessage());
        } catch (\Exception $e) {
            SplitApp::logger()->error($e->getMessage());
        }

        return $value;
    }

    private function cacheFeature($featureName, \SplitIO\Grammar\Split $split)
    {

        $ikey = $this->getSmKey($featureName);

        try {
            return SharedMemory::write($ikey, $split, $this->smTtl, $this->smMode, $this->smSize);
        } catch (SupportSharedMemoryException $se) {
            SplitApp::logger()->warning($se->getMessage());
        } catch (OpenSharedMemoryException $oe) {
            SplitApp::logger()->error($oe->getMessage());
        } catch (WriteSharedMemoryException $we) {
            SplitApp::logger()->error($we->getMessage());
        } catch (\Exception $e) {
            SplitApp::logger()->error($e->getMessage());
        }

        return false;
    }

    private function evalTreatment($matchingKey, $bucketingKey, $featureName, array $attributes = null)
    {
        $split = null;

        $do_evaluation = false;

        $cachedFeature = $this->getCachedFeature($featureName);

        if ($cachedFeature !== null) {
            $split = $cachedFeature;
            $do_evaluation = true;
        } else {
            $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
            $splitCachedItem = SplitApp::cache()->getItem($splitCacheKey);

            if ($splitCachedItem->isHit()) {
                SplitApp::logger()->info("$featureName is present on cache");
                $splitRepresentation = $splitCachedItem->get();

                $split = new Split(json_decode($splitRepresentation, true));

                $this->cacheFeature($featureName, $split);

                $do_evaluation = true;
            }
        }

        if ($do_evaluation) {
            //If the split was killed, log the impression and return default treatment.
            if ($split->killed()) {
                $defaultTreatment = $split->getDefaultTratment();
                $chn = $split->getChangeNumber();
                $this->logImpression(
                    $matchingKey,
                    $featureName,
                    $defaultTreatment,
                    ImpressionLabel::KILLED,
                    $bucketingKey,
                    $chn
                );
                return $defaultTreatment;
            }

            $timeStart = Metrics::startMeasuringLatency();
            $evaluationResult = Engine::getTreatment($matchingKey, $bucketingKey, $split, $attributes);
            $latency = Metrics::calculateLatency($timeStart);

            $treatment = $evaluationResult[Engine::EVALUATION_RESULT_TREATMENT];
            $impressionLabel = $evaluationResult[Engine::EVALUATION_RESULT_LABEL];

            //If the given key doesn't match on any condition, default treatment is returned
            if ($treatment == null) {
                $treatment = $split->getDefaultTratment();
                $impressionLabel = ImpressionLabel::NO_CONDITION_MATCHED;
            }

            //Registering latency value
            MetricsCache::addLatencyOnBucket(
                Metrics::MNAME_SDK_GET_TREATMENT,
                Metrics::getBucketForLatencyMicros($latency)
            );

            SplitApp::logger()->info("*Treatment for $matchingKey in {$split->getName()} is: $treatment");

            //Logging treatment impressions
            $this->logImpression(
                $matchingKey,
                $featureName,
                $treatment,
                $impressionLabel,
                $bucketingKey,
                $split->getChangeNumber()
            );

            //Returning treatment.
            return $treatment;
        }

        // Split not found impression
        $this->logImpression(
            $matchingKey,
            $featureName,
            TreatmentEnum::CONTROL,
            ImpressionLabel::SPLIT_NOT_FOUND,
            $bucketingKey
        );

        SplitApp::logger()->warning("The SPLIT definition for '$featureName' has not been found'");

        return TreatmentEnum::CONTROL;
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
        $bucketingKey = '',
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
        //Getting Matching key and Bucketing key
        if ($key instanceof Key) {
            $matchingKey = $key->getMatchingKey();
            $bucketingKey = $key->getBucketingKey();
        } else {
            $matchingKey = $key;
            $bucketingKey = $key;
        }

        try {
            return $this->evalTreatment($matchingKey, $bucketingKey, $featureName, $attributes);
        } catch (\Exception $e) {
            SplitApp::logger()->critical('getTreatment method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }

        $this->logImpression(
            $matchingKey,
            $featureName,
            TreatmentEnum::CONTROL,
            ImpressionLabel::EXCEPTION,
            $bucketingKey
        );
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
}
