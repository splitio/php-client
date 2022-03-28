<?php
namespace SplitIO\Component\Initialization;

use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Common\ServiceProvider;

class CacheTrait
{
    public static function addCache($adapter, array $options)
    {
        $adapter_config = array(
            'name' => 'predis',
            'options' => array(
                'options'               => isset($options['predis-options'])
                    ? $options['predis-options'] : null,
                'parameters'            => isset($options['predis-parameters'])
                    ? $options['predis-parameters'] : null,
                'sentinels'             => isset($options['predis-sentinels'])
                    ? $options['predis-sentinels'] : null,
                'clusterNodes'          => isset($options['predis-clusterNodes'])
                    ? $options['predis-clusterNodes'] : null,
                'distributedStrategy'   => isset($options['predis-distributedStrategy'])
                    ? $options['predis-distributedStrategy'] : null,
            )
        );

        ServiceProvider::registerCache(new Pool(array('adapter' => $adapter_config)));
    }
}
