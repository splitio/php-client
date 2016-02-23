<?php
namespace SplitIO\Cache;

use SplitIO\Split;

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
        $data = [
            'keyName' => $key,
            'treatment' => $treatment,
            'time' => $time
        ];

        return Split::cache()->saveItemOnList(self::getCacheKeyForImpressionData($featureName), json_encode($data));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAllImpressions($key)
    {
        return Split::cache()->getItemsOnList($key);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function removeImpression($key, $value)
    {
        return Split::cache()->removeItemOnList($key, $value);
    }
}