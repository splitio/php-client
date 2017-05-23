<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\KeyFactory;

class ImpressionCache
{
    const KEY_IMPRESSION_DATA = "SPLITIO/{sdk-language-version}/{instance-id}/impressions.{featureName}";

    /**
     * @param $key
     * @return string
     */
    public static function getFeatureNameFromKey($key)
    {
        $prefixLen = strlen(KeyFactory::make(self::KEY_IMPRESSION_DATA, array('{featureName}' => '')));
        return substr($key, $prefixLen);
    }

    /**
     * @return mixed
     */
    public static function getCacheKeySearchPattern()
    {
        return KeyFactory::make(self::KEY_IMPRESSION_DATA, array('{featureName}' => '*'));
    }

    /**
     * @param $featureName
     * @return mixed
     */
    public static function getCacheKeyForImpressionData($featureName)
    {
        return KeyFactory::make(self::KEY_IMPRESSION_DATA, array('{featureName}' => $featureName));
    }

    /**
     * @param $featureName
     * @param $key
     * @param $treatment
     * @param $time
     * @return bool
     */
    public function addDataToFeature($featureName, $key, $treatment, $time, $changeNumber, $label, $bucketingKey)
    {
        $data = array(
            'keyName' => $key,
            'treatment' => $treatment,
            'time' => $time,
            'changeNumber' => $changeNumber,
            'label' => $label,
            'bucketingKey' => $bucketingKey
        );

        return Di::getCache()->saveItemOnList(self::getCacheKeyForImpressionData($featureName), json_encode($data));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAllImpressions($key)
    {
        return Di::getCache()->getItemsOnList($key);
    }

    public function getRandomImpressions($key, $count)
    {
        return Di::getCache()->getItemsRandomlyOnList($key, $count);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function removeImpression($key, $value)
    {
        return Di::getCache()->removeItemOnList($key, $value);
    }
}
