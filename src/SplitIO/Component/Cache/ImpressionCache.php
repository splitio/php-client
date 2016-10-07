<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class ImpressionCache
{
    const KEY_IMPRESSION_DATA = "SPLITIO.impressions.{featureName}";

    /**
     * @param $key
     * @return string
     */
    public static function getFeatureNameFromKey($key)
    {
        $prefixLen = strlen(str_replace('{featureName}', '', self::KEY_IMPRESSION_DATA));
        return substr($key, $prefixLen);
    }

    /**
     * @return mixed
     */
    public static function getCacheKeySearchPattern()
    {
        return str_replace('{featureName}', '*', self::KEY_IMPRESSION_DATA);
    }

    /**
     * @param $featureName
     * @return mixed
     */
    public static function getCacheKeyForImpressionData($featureName)
    {
        return str_replace('{featureName}', $featureName, self::KEY_IMPRESSION_DATA);
    }

    /**
     * @param $featureName
     * @param $key
     * @param $treatment
     * @param $time
     * @return bool
     */
    public function addDataToFeature($featureName, $key, $treatment, $time)
    {
        $data = array(
            'keyName' => $key,
            'treatment' => $treatment,
            'time' => $time
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
