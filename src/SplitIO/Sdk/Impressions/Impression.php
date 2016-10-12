<?php
namespace SplitIO\Sdk\Impressions;

/**
 * Class Impression
 * @package SplitIO\Sdk\Impressions
 */
class Impression
{
    /**
     * id in getTreatment or Key.matchingKey
     * @var
     */
    private $id;

    /**
     * feature in getTreatment
     * @var
     */
    private $feature;

    /**
     * treatment returned from getTreatment
     * @var
     */
    private $treatment;

    /**
     * ms since epoch when getTreatment was called
     * @var
     */
    private $time;

    /**
     * of Split that served treatment. -1 if there was no rollout plan.
     * @var
     */
    private $changeNumber;

    /**
     * a short explanation for why this id was shown this treatment for this feature.
     * @var
     */
    private $label;

    /**
     * null or Key.bucketingKey
     * @var
     */
    private $bucketingKey;

    /**
     * Impression constructor.
     * @param $id
     * @param $feature
     * @param $treatment
     * @param $time
     * @param $changeNumber
     * @param $label
     * @param $bucketingKey
     */
    public function __construct(
        $id,
        $feature,
        $treatment,
        $label = '',
        $time = null,
        $changeNumber = -1,
        $bucketingKey = ''
    ) {
        $this->id = $id;
        $this->feature = $feature;
        $this->treatment = $treatment;
        $this->changeNumber = $changeNumber;
        $this->label = $label;
        $this->bucketingKey = $bucketingKey;

        $this->setTime($time);
    }

    public function __toString()
    {
        return sprintf(
            'Impression:  [Feature %s] [Matching Key %s] [Treatment %s] '
            . '[Time %s] [Label %s] [Change Number %s] [Bucketing key %s]',
            $this->feature,
            $this->id,
            $this->treatment,
            $this->time,
            $this->label,
            $this->changeNumber,
            $this->bucketingKey
        );
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        if ($time === null || !is_integer($time)) {
            $dateTimeUTC = new \DateTime("now", new \DateTimeZone("UTC"));
            $milliseconds = $dateTimeUTC->getTimestamp();
        } else {
            $milliseconds = $time;
        }

        $this->time = $milliseconds * 1000;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @param mixed $feature
     */
    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    /**
     * @return mixed
     */
    public function getTreatment()
    {
        return $this->treatment;
    }

    /**
     * @param mixed $treatment
     */
    public function setTreatment($treatment)
    {
        $this->treatment = $treatment;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getChangeNumber()
    {
        return $this->changeNumber;
    }

    /**
     * @param mixed $changeNumber
     */
    public function setChangeNumber($changeNumber)
    {
        $this->changeNumber = $changeNumber;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getBucketingKey()
    {
        return $this->bucketingKey;
    }

    /**
     * @param mixed $bucketingKey
     */
    public function setBucketingKey($bucketingKey)
    {
        $this->bucketingKey = $bucketingKey;
    }
}
