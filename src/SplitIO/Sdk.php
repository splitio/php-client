<?php
namespace SplitIO;

use SplitIO\Sdk\Client as SdkClient;
use SplitIO\Client;
use SplitIO\Sdk\FakeClient;

class Sdk
{
    //Trait to initialize SDK options
    use InitializationTrait;

    const VERSION = '0.0.1';

    const NAME = 'Split-SDK-PHP';

    const SPLITIO_URL = "https://sdk.split.io";

    /** @var array Arguments for creating clients */
    private $args;

    private function __construct()
    {
    }

    /**
     * @param $apiKey
     * @param array $args
     * @return \SplitIO\Sdk\ClientInterface
     */
    public static function factory($apiKey = 'localhost', array $args = [])
    {
        if ($apiKey == 'localhost') {
            $filePath = (isset($args['splitFile']) && file_exists($args['splitFile'])) ? $args['splitFile'] : null;
            return new FakeClient($filePath);
        }

        //Adding API Key into args array.
        $args['apiKey'] = $apiKey;

        self::initSdk($args);

        return new SdkClient();
    }
}
