<?php
/**
 * Helper functions
 */
namespace SplitIO\Component\Utils;

defined('SPLITIO_URL') || define('SPLITIO_URL', 'https://sdk.split.io');
defined('SPLITIO_EVENTS_URL') || define('SPLITIO_EVENTS_URL', 'https://events.split.io');

function setEnvironment($env)
{
    putenv('SPLITIO_PHP_SDK_ENV='.$env);
}

function getEnvironment()
{
    $env = getenv('SPLITIO_PHP_SDK_ENV');
    if (empty($env)) {
        return 'production';
    } else {
        return $env;
    }
}

/**
 * @param null $env
 * @param bool|false $set
 * @return bool|string
 */
function environment($env = null, $set = false)
{
    if ($env !== null && $set) {
        setEnvironment($env);
    } elseif ($env !== null && ! $set) {
        if ($env == getEnvironment()) {
            return true;
        } else {
            return false;
        }
    }

    return getEnvironment();
}

function getSplitServerUrl()
{
    $env = environment();

    switch ($env) {
        case 'development':
            return getenv('SPLITIO_DEV_URL');
            break;

        case 'loadtesting':
            return getenv('SPLITIO_LOADTESTING_URL');
            break;

        case 'testing':
            return getenv('SPLITIO_TESTING_URL');
            break;

        case 'staging':
            return getenv('SPLITIO_STAGE_URL');
            break;

        case 'production':
        default:
            return SPLITIO_URL;
    }
}

function getSplitEventsUrl()
{
    $env = environment();

    switch ($env) {
        case 'development':
            return getenv('SPLITIO_EVENTS_DEV_URL');
            break;

        case 'loadtesting':
            return getenv('SPLITIO_EVENTS_LOADTESTING_URL');
            break;

        case 'testing':
            return getenv('SPLITIO_EVENTS_TESTING_URL');
            break;

        case 'staging':
            return getenv('SPLITIO_EVENTS_STAGE_URL');
            break;

        case 'production':
        default:
            return SPLITIO_EVENTS_URL;
    }
}

function isAssociativeArray($arr)
{
    if (!is_array($arr)) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}
