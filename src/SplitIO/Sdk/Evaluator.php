<?php

namespace SplitIO\Sdk;

use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Memory\Exception\OpenSharedMemoryException;
use SplitIO\Component\Memory\Exception\ReadSharedMemoryException;
use SplitIO\Component\Memory\Exception\SupportSharedMemoryException;
use SplitIO\Component\Memory\Exception\WriteSharedMemoryException;
use SplitIO\Component\Memory\SharedMemory;
use SplitIO\Component\Common\Di;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;
use SplitIO\Metrics;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\Split as SplitApp;

class Evaluator
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
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->smSize        = isset($options['memory']['size']) ? $options['memory']['size'] : 40000;
        $this->smMode        = isset($options['memory']['mode']) ? $options['memory']['mode'] : 0644;
        $this->smTtl         = isset($options['memory']['ttl'])  ? $options['memory']['ttl']  : 60;
        $this->smKeySeed     = isset($options['memory']['seed']) ? $options['memory']['seed'] : 123123;
    }

    private function getSmKey($featureName)
    {
        $murmurHashFn = new \SplitIO\Engine\Hash\Murmur3Hash();
        return $murmurHashFn->getHash('feature::'.$featureName, $this->smKeySeed);
    }

    private function cacheFeature($featureName, \SplitIO\Grammar\Split $split)
    {
        $ikey = $this->getSmKey($featureName);
        try {
            return SharedMemory::write($ikey, $split, $this->smTtl, $this->smMode, $this->smSize);
        } catch (SupportSharedMemoryException $se) {
            SplitApp::logger()->debug($se->getMessage());
        } catch (OpenSharedMemoryException $oe) {
            SplitApp::logger()->debug($oe->getMessage());
        } catch (WriteSharedMemoryException $we) {
            SplitApp::logger()->debug($we->getMessage());
        } catch (\Exception $e) {
            SplitApp::logger()->debug($e->getMessage());
        }
        return false;
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
            SplitApp::logger()->debug($se->getMessage());
        } catch (OpenSharedMemoryException $oe) {
            SplitApp::logger()->debug($oe->getMessage());
        } catch (ReadSharedMemoryException $re) {
            SplitApp::logger()->debug($re->getMessage());
        } catch (\Exception $e) {
            SplitApp::logger()->debug($e->getMessage());
        }

        return $value;
    }

    private function fetchSplit($featureName)
    {
        $split = null;
        $cachedFeature = $this->getCachedFeature($featureName);
        if ($cachedFeature !== null) {
            $split = $cachedFeature;
        } else {
            $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
            $splitCachedItem = SplitApp::cache()->getItem($splitCacheKey);

            if ($splitCachedItem->isHit()) {
                SplitApp::logger()->info("$featureName is present on cache");
                $splitRepresentation = $splitCachedItem->get();
                $split = new Split(json_decode($splitRepresentation, true));
                $this->cacheFeature($featureName, $split);
            }
        }
        return $split;
    }

    public function evalTreatment($matchingKey, $bucketingKey, $featureName, array $attributes = null)
    {
        $result = array(
            'treatment' => TreatmentEnum::CONTROL,
            'impression' => array(
                'label' => ImpressionLabel::SPLIT_NOT_FOUND,
                'changeNumber' => null,
            ),
            'metadata' => array(
                'latency' => null,
            ),
        );

        $split = $this->fetchSplit($featureName);
        if ($split != null) {
            if ($split->killed()) {
                $result['treatment'] = $split->getDefaultTratment();
                $result['impression']['label'] = ImpressionLabel::KILLED;
                $result['impression']['changeNumber'] = $split->getChangeNumber();
            } else {
                Di::setMatcherClient(new MatcherClient($this));
                $timeStart = Metrics::startMeasuringLatency();
                $evaluationResult = Engine::getTreatment(
                    $matchingKey,
                    $bucketingKey,
                    $split,
                    $attributes
                );
                $latency = Metrics::calculateLatency($timeStart);
    
                $treatment = $evaluationResult[Engine::EVALUATION_RESULT_TREATMENT];
                $impressionLabel = $evaluationResult[Engine::EVALUATION_RESULT_LABEL];
    
                //If the given key doesn't match on any condition, default treatment is returned
                if ($treatment == null) {
                    $treatment = $split->getDefaultTratment();
                    $impressionLabel = ImpressionLabel::NO_CONDITION_MATCHED;
                }
    
                SplitApp::logger()->info("*Treatment for $matchingKey in {$split->getName()} is: $treatment");

                $result['treatment'] = $treatment;
                $result['metadata']['latency'] = $latency;
                $result['impression']['label'] = $impressionLabel;
                $result['impression']['changeNumber'] = $split->getChangeNumber();
            }
        } else {
            SplitApp::logger()->warning("The SPLIT definition for '$featureName' has not been found'");
        }

        return $result;
    }
}
