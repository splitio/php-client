<?php
namespace SplitIO\Sdk;

use SplitIO\Common\Di;
use SplitIO\Cache\Pool;
use SplitIO\Cache\Item;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Log\Handler\Stdout;
use SplitIO\Log\Logger;

class Client
{
    public function __construct($apiKey, array $args = [])
    {
        $di = Di::getInstance();

        $stdoutAdapter = new Stdout();
        $di->setLogger(new Logger($stdoutAdapter, \Psr\Log\LogLevel::INFO));

        $adapter_config = [
            'name' => 'filesystem',
            'options' => [
                'path'=> '/home/sarrubia/cache'
            ]
        ];

        $di->setCache(new Pool([ 'adapter' => $adapter_config ]));
    }

    public function isOn($userId, $featureName)
    {
        if (!is_string($userId) || !is_string($featureName)) {
            return false;
        }

        $cacheInstance = Di::getInstance()->getCache();

        $cacheKey = \SplitIO\generateCacheKey($userId, $featureName);

        Di::getInstance()->getLogger()->info("Cache-key: $cacheKey");

        if ($cacheInstance->hasItem($cacheKey)) {
            Di::getInstance()->getLogger()->info("Item Found with cache-key: $cacheKey");
            if ($cacheInstance->getItem($cacheKey)->get() === TreatmentEnum::ON) {
                return true;
            }
        }

        return false;
    }
}