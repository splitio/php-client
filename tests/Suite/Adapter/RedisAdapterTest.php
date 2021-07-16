<?php

namespace SplitIO\Test\Suite\Adapter;
use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use \Predis\Response\ServerException;
use \Predis\ClientException;
use SplitIO\Component\Common\Di;

class RedisAdapterTest extends \PHPUnit\Framework\TestCase
{
    private function getMockedLogger()
    {
        //Initialize mock logger
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        Di::set(Di::KEY_LOG, $logger);

        return $logger;
    }

    public function testRedisWithNullValues()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', "Wrong configuration of redis.");

        $predis = new PRedis(array());
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
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
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
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithParametersPrefixAndSentinels()
    {
        $predis = new PRedis(array(
            'parameters' => array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 881,
                'database' => 0
            ),
            'sentinels' => array('something', 'other'),
            'options' => array(
                'prefix' => 'test-redis-assertion'
            )
        ));
        $predis->addItem('this_is_a_test_key', 'this-is-a-test-value');
        $value = $predis->getItem('this_is_a_test_key');
        $this->assertEquals('this-is-a-test-value', $value->get());
        $result = $predis->deleteItem('this_is_a_test_key');
        $this->assertTrue($result);
    }

    public function testRedisWithEmptySentinels()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', 'At least one sentinel is required.');

        $predis = new PRedis(array(
            'sentinels' => array(),
            'options' => array(
                'distributedStrategy' => 'sentinel'
            )
        ));
    }

    public function testRedisWithSentinelsWithoutOptions()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', "Wrong configuration of redis.");

        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
        ));
    }

    public function testRedisWithSentinelsWithoutReplicationOption()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', "Wrong configuration of redis.");
        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
            'options' => array()
        ));
    }

    public function testRedisWithSentinelsWithWrongReplicationOption()
    {
        $logger = $this->getMockedLogger();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("'replication' option was deprecated please use 'distributedStrategy'"));

        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', "Wrong configuration of redis 'distributedStrategy'.");

        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
            'options' => array(
                'replication' => 'test'
            )
        ));
    }

    public function testRedisWithSentinelsWithoutServiceOption()
    {
        $logger = $this->getMockedLogger();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("'replication' option was deprecated please use 'distributedStrategy'"));

        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            'Master name is required in replication mode for sentinel.'
        );

        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
            'options' => array(
                'replication' => 'sentinel'
            )
        ));
    }

    public function testRedisWithWrongTypeOfSentinels()
    {
        $logger = $this->getMockedLogger();

        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', 'sentinels must be an array.');

        $predis = new PRedis(array(
            'sentinels' => "test",
            'options' => array(
                'replication' => 'sentinel'
            )
        ));
    }

    public function testRedisSentinelWithWrongRedisDistributedStrategy()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "Wrong configuration of redis 'distributedStrategy'."
        );

        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
            'options' => array(
                'distributedStrategy' => 'test'
            )
        ));
    }

    public function testRedisWithSentinels()
    {
        $logger = $this->getMockedLogger();
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("'replication' option was deprecated please use 'distributedStrategy'"));

        $this->expectException('\Predis\ClientException');
        $predis = new PRedis(array(
            'sentinels' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'replication' => 'sentinel',
                'service' => 'master'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithSentinelsAndDistributedStrategy()
    {
        $this->expectException('\Predis\Response\ServerException');
        $predis = new PRedis(array(
            'sentinels' => array(
                'tcp:/MYIP:26379?timeout=3'
            ),
            'options' => array(
                'service' => 'master',
                'distributedStrategy' => 'sentinel'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithEmptyClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', 'At least one clusterNode is required.');

        $predis = new PRedis(array(
            'clusterNodes' => array(),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => '{TEST}'
            )
        ));
    }

    public function testRedisWithClustersWithoutOptions()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', "Wrong configuration of redis.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                '127.0.0.1'
            ),
        ));
    }

    public function testRedisWithWrongTypeOfClusters()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException', 'clusterNodes must be an array.');

        $predis = new PRedis(array(
            'clusterNodes' => "test",
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => '{TEST}'
            )
        ));
    }

    public function testRedisClusterWithWrongRedisDistributedStrategy()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "Wrong configuration of redis 'distributedStrategy'."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                '127.0.0.1'
            ),
            'options' => array(
                'distributedStrategy' => 'test'
            )
        ));
    }

    public function testRedisWithInvalidKeyHashtagInClusters()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "keyHashTag is not valid."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => '{TEST'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithInvalidBeginingKeyHashtagInClusters()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "keyHashTag is not valid."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => 'TEST}'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithWrongTypeKeyHashtagInClusters()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "keyHashTag must be string."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => array()
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithWrongLengthKeyHashtagInClusters()
    {
        $this->expectException(
            'SplitIO\Component\Cache\Storage\Exception\AdapterException',
            "keyHashTag is not valid."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => "{}"
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithClusters()
    {
        $this->expectException('\Predis\ClientException');
        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'keyHashTag' => '{TEST}'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithoutCustomKeyHashtagClusters()
    {
        $this->expectException('\Predis\ClientException');
        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisSSLWithClusterFails()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $predis = new PRedis(array(
            'options' => array(
                'distributedStrategy' => 'cluster',
            ),
            'parameters' => array(
                'tls' => array(),
            ),
        ));
    }

    public function testRedisSSLWithSentinelFails()
    {
        $this->expectException('SplitIO\Component\Cache\Storage\Exception\AdapterException');
        $predis = new PRedis(array(
            'options' => array(
                'distributedStrategy' => 'sentinel',
            ),
            'parameters' => array(
                'tls' => array(),
            ),
        ));
    }

}
