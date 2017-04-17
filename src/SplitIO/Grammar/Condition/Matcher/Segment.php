<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Memory\Exception\OpenSharedMemoryException;
use SplitIO\Component\Memory\Exception\ReadSharedMemoryException;
use SplitIO\Component\Memory\Exception\SupportSharedMemoryException;
use SplitIO\Component\Memory\Exception\WriteSharedMemoryException;
use SplitIO\Component\Memory\SharedMemory;
use SplitIO\Engine\Hash\Murmur3Hash;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Split as SplitApp;

class Segment extends AbstractMatcher
{

    /**
     * Size of memory block in bytes
     * @var int
     */
    private $smSize = 100;

    /**
     * mode of shared memory block
     * @var int
     */
    private $smMode = 0644;

    /**
     * Time to live of data in shared memory block
     * @var int
     */
    private $smTtl = 60;

    /**
     * Seed to generate an integer key
     * @var int
     */
    private $smKeySeed = 123123;

    /**
     * For this version only will be the segment name.
     * @var array|string
     */
    protected $userDefinedSegmentMatcherData = null;

    /**
     * @var null
     */
    protected $segmentData = null;

    /**
     * @param $data
     * @param bool|false $negate
     */
    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate, $attribute);

        $this->userDefinedSegmentMatcherData = $data;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function evalKey($key)
    {
        $segmentName = $this->userDefinedSegmentMatcherData;

        $segmentEvaluation = $this->isInCachedSegment($segmentName, $key);

        if ($segmentEvaluation !== null && is_bool($segmentEvaluation)) {
            return $segmentEvaluation;
        } else {
            $segmentCache = new SegmentCache();

            if ($segmentCache->isInSegment($segmentName, $key)) {
                $this->cacheSegmentEvaluation($segmentName, $key, true);
                return true;
            }

            $this->cacheSegmentEvaluation($segmentName, $key, false);
            return false;
        }
    }

    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    private function getSmKey($segmentName, $key)
    {
        $murmurHashFn = new Murmur3Hash();
        return $murmurHashFn->getHash("segment::".$segmentName."::".$key, $this->smKeySeed);
    }

    /**
     * @param $segmentName
     * @param $key
     * @return bool|null
     */
    private function isInCachedSegment($segmentName, $key)
    {
        $ikey = $this->getSmKey($segmentName, $key);

        $value = null;

        try {
            $value = SharedMemory::read($ikey, $this->smMode, $this->smSize);

            if (!is_bool($value)) {
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

    /**
     * @param $segmentName
     * @param $key
     * @param $value
     * @return bool
     */
    private function cacheSegmentEvaluation($segmentName, $key, $value)
    {
        $ikey = $this->getSmKey($segmentName, $key);

        try {
            return SharedMemory::write($ikey, $value, $this->smTtl, $this->smMode, $this->smSize);
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
}
