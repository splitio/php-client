<?php
namespace SplitIO\Service\Console;

use SplitIO\Component\Common\Enum;

class OptionsEnum extends Enum
{
    const API_KEY                   = 'api-key';
    const CONFIG_FILE               = 'config-file';
    const ENVIRONMENT               = 'environment';

    //Logger
    const LOG_ADAPTER               = 'log-adapter';
    const LOG_LEVEL                 = 'log-level';
    const LOG_CUSTOM                = 'log-custom';

    //Cache
    const CACHE_ADAPTER             = 'cache-adapter';
    const REDIS_HOST                = 'redis-host';
    const REDIS_PORT                = 'redis-port';
    const REDIS_PASS                = 'redis-pass';
    const REDIS_TIMEOUT             = 'redis-timeout';
    const REDIS_URL                 = 'redis-url';
    const FILESYSTEM_PATH           = 'filesystem-path';
    const PREDIS_PARAMETERS         = 'predis-parameters';
    const PREDIS_OPTIONS            = 'predis-options';

    //Process
    const RATE_FETCH_SPLITS         = 'features-refresh-rate';
    const RATE_FETCH_SEGMENTS       = 'segments-refresh-rate';
    const RATE_SEND_IMPRESSIONS     = 'impressions-refresh-rate';
    const RATE_SEND_METRICS         = 'metrics-refresh-rate';

    //Process parameters
    const IMPRESSIONS_PER_TEST      = 'impressions-per-test';
}
