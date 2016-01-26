<?php
namespace SplitIO;

use SplitIO\Sdk\Client as SdkClient;

class Sdk
{
    const VERSION = '0.0.1';

    const NAME = 'Split-SDK-PHP';

    /** @var array Arguments for creating clients */
    private $args;

    private function __construct()
    {
    }

    /**
     * @param $apiKey
     * @param array $args
     * @return \SplitIO\Sdk\Client
     */
    public static function factory($apiKey, array $args = [])
    {
        return new SdkClient($apiKey, $args);
    }
}