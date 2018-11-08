<?php

namespace SplitIO\Test\Suite\Adapter;

use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use \Predis\Response\ServerException;
use \Predis\ClientException;
use SplitIO\Component\Common\Di;

class RedisAdapterTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(AdapterException::class, "Wrong configuration of redis.");

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
        $this->setExpectedException(AdapterException::class, 'At least one sentinel is required.');

        $predis = new PRedis(array(
            'sentinels' => array(),
            'options' => array(
                'distributedStrategy' => 'sentinel'
            )
        ));
    }

    public function testRedisWithSentinelsWithoutOptions()
    {
        $this->setExpectedException(AdapterException::class, "Wrong configuration of redis.");

        $predis = new PRedis(array(
            'sentinels' => array(
                '127.0.0.1'
            ),
        ));
    }

    public function testRedisWithSentinelsWithoutReplicationOption()
    {
        $this->setExpectedException(AdapterException::class, "Wrong configuration of redis.");
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

        $this->setExpectedException(AdapterException::class, "Wrong configuration of redis 'distributedStrategy'.");

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

        $this->setExpectedException(
            AdapterException::class,
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

        $this->setExpectedException(AdapterException::class, 'sentinels must be an array.');

        $predis = new PRedis(array(
            'sentinels' => "test",
            'options' => array(
                'replication' => 'sentinel'
            )
        ));
    }

    public function testRedisSentinelWithWrongRedisDistributedStrategy()
    {
        $this->setExpectedException(
            AdapterException::class,
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

        $this->setExpectedException(ClientException::class);
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
        $this->setExpectedException(ServerException::class);
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
        $this->setExpectedException(AdapterException::class, 'At least one clusterNode is required.');

        $predis = new PRedis(array(
            'clusterNodes' => array(),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => '{TEST}'
            )
        ));
    }

    public function testRedisWithClustersWithoutOptions()
    {
        $this->setExpectedException(AdapterException::class, "Wrong configuration of redis.");

        $predis = new PRedis(array(
            'clusterNodes' => array(
                '127.0.0.1'
            ),
        ));
    }

    public function testRedisWithWrongTypeOfClusters()
    {
        $this->setExpectedException(AdapterException::class, 'clusterNodes must be an array.');

        $predis = new PRedis(array(
            'clusterNodes' => "test",
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => '{TEST}'
            )
        ));
    }

    public function testRedisClusterWithWrongRedisDistributedStrategy()
    {
        $this->setExpectedException(
            AdapterException::class,
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

    public function testRedisWithoutHashtagInClusters()
    {
        $this->setExpectedException(
            AdapterException::class,
            "Hashtag is mandatory for redis cluster."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithInvalidHashtagInClusters()
    {
        $this->setExpectedException(
            AdapterException::class,
            "Hashtag is not valid."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => '{TEST'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithInvalidBeginingHashtagInClusters()
    {
        $this->setExpectedException(
            AdapterException::class,
            "Hashtag is not valid."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => 'TEST}'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithWrongTypeHashtagInClusters()
    {
        $this->setExpectedException(
            AdapterException::class,
            "Hashtag must be string."
        );

        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => array()
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithWrongLengthHashtagInClusters()
    {
        $this->setExpectedException(
            AdapterException::class,
            "Hashtag is not valid."
        );
        
        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => ""
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }

    public function testRedisWithClusters()
    {
        $this->setExpectedException(ClientException::class);
        $predis = new PRedis(array(
            'clusterNodes' => array(
                'tcp://MYIP:26379?timeout=3'
            ),
            'options' => array(
                'distributedStrategy' => 'cluster',
                'hashtag' => '{TEST}'
            )
        ));

        $predis->getItem('this_is_a_test_key');
    }
}
