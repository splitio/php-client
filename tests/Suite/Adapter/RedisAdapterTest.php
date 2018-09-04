<?php

namespace SplitIO\Test\Suite\Adapter;

use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use \Predis\Response\ServerException;

class RedisAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testRedisWithNullValues()
    {
        $predis = new PRedis(array());
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithOnlyParameters()
    {
        $predis = new PRedis(array(
            'parameters' => [
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ]
        ));
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithParametersAndPrefix()
    {
        $predis = new PRedis(array(
            'parameters' => [
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ],
            'options' => [
                'prefix' => 'test-redis-assertion'
            ]
        ));
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithParametersPrefixAndSentinels()
    {
        $predis = new PRedis(array(
            'parameters' => [
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ],
            'sentinels' => ['something', 'other'],
            'options' => [
                'prefix' => 'test-redis-assertion'
            ]
        ));
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithEmptySentinels()
    {
        $this->setExpectedException(AdapterException::class, 'At least one sentinel is required.');

        $predis = new PRedis(array(
            'sentinels' => [],
        ));
    }

    public function testRedisWithSentinelsWithoutOptions()
    {
        $this->setExpectedException(AdapterException::class, 'Missing replication mode in options.');

        $predis = new PRedis(array(
            'sentinels' => ['127.0.0.1'],
        ));
    }

    public function testRedisWithSentinelsWithoutReplicationOption()
    {
        $this->setExpectedException(AdapterException::class, 'Missing replication mode in options.');
        $predis = new PRedis(array(
            'sentinels' => ['127.0.0.1'],
            'options' => [
            ]
        ));
    }

    public function testRedisWithSentinelsWithWrongReplicationOption()
    {
        $this->setExpectedException(AdapterException::class, 'Wrong configuration of redis replication.');

        $predis = new PRedis(array(
            'sentinels' => ['127.0.0.1'],
            'options' => [
                'replication' => 'test'
            ]
        ));
    }

    public function testRedisWithSentinelsWithoutServiceOption()
    {
        $this->setExpectedException(
            AdapterException::class,
            'Master name is required in replication mode for sentinel.'
        );

        $predis = new PRedis(array(
            'sentinels' => ['127.0.0.1'],
            'options' => [
                'replication' => 'sentinel'
            ]
        ));
    }

    public function testRedisWithSentinels()
    {
        $this->setExpectedException(ServerException::class);
        $predis = new PRedis(array(
            'sentinels' => ['127.0.0.1'],
            'options' => [
                'replication' => 'sentinel',
                'service' => 'master'
            ]
        ));

        $predis->getItem('this_is_a_test_key');
    }
}
