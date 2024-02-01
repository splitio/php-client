<?php

namespace SplitIO\Test\Suite\Adapter;

use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Test\Suite\Redis\ReflectiveTools;

class RedisAdapterTest extends \PHPUnit\Framework\TestCase
{
    private function getMockedLogger()
    {
        //Initialize mock logger
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->onlyMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'log'))
            ->getMock();

        ReflectiveTools::overrideLogger($logger);

        return $logger;
    }

    public function testRedisWithOnlyParameters()
    {
        $predis = new PRedis(array(
            'parameters' => array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            )
        ));
        $predisClient = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);
        $predisClient->set('this_is_a_test_key', 'this-is-a-test-value');

        $value = $predis->get('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value);

        $predisClient->del('this_is_a_test_key');
    }

    public function testRedisWithParametersAndPrefix()
    {
        $predis = new PRedis(array(
            'parameters' => array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ),
            'options' => array(
                'prefix' => 'test-redis-assertion'
            )
        ));
        $predisClient = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);
        $predisClient->set('test-redis-assertion.this_is_a_test_key', 'this-is-a-test-value');

        $value = $predis->get('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value);

        $predisClient->del('test-redis-assertion.this_is_a_test_key');
    }

    public function testRedisWithInvalidKeyHashtagInClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTag is not valid.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'cluster' => 'predis',
                'keyHashTag' => '{TEST'
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithInvalidBeginingKeyHashtagInClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTag is not valid.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'cluster' => 'redis',
                'keyHashTag' => 'TEST}'
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithWrongTypeKeyHashtagInClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTag must be string.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'cluster' => 'predis',
                'keyHashTag' => array()
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithWrongLengthKeyHashtagInClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTag is not valid.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'cluster' => 'predis',
                'keyHashTag' => "{}"
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithClusters()
    {
        $this->expectException('\Predis\Connection\ConnectionException');
        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
                'keyHashTag' => '{TEST}'
            ),
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithoutCustomKeyHashtagClusters()
    {
        $this->expectException('\Predis\Connection\ConnectionException');
        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithClustersKeyHashTags()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTags must be an array.");

        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
                'keyHashTags' => '{TEST}'
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithClustersKeyHashTagsInvalid()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTags size is zero after filtering valid elements.");

        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
                'keyHashTags' => array(1, 2)
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithClustersKeyHashTagsInvalidHashTags()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $this->expectExceptionMessage("keyHashTags size is zero after filtering valid elements.");

        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
                'keyHashTags' => array("one", "two", "three")
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithClustersKeyHashTagsValid()
    {
        $this->expectException('\Predis\Connection\ConnectionException');
        $predis = new PRedis(array(
            'parameters' => ['tcp://10.0.0.1', 'tcp://10.0.0.2', 'tcp://10.0.0.3'],
            'options' => array(
                'cluster' => 'predis',
                'keyHashTags' => array("{one}", "{two}", "{three}")
            )
        ));

        $predis->get('this_is_a_test_key');
    }

    public function testRedisWithWrongPrefix()
    {
        $predis = new PRedis(array(
            'parameters' => array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ),
            'options' => array(
                'prefix' => array()
            )
        ));
        $predisClient = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);
        $predisClient->set('{SPLITIO}.this_is_a_test_key', 'this-is-a-test-value');

        $value = $predis->get('{SPLITIO}.this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value);

        $predisClient->del('{SPLITIO}.this_is_a_test_key');
    }
}
