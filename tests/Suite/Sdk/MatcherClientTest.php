<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\MatcherClient;

class MatcherClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTreatmentSuccessful()
    {
        $evaluator = $this
            ->getMockBuilder('\SplitIO\Sdk\Evaluator')
            ->disableOriginalConstructor()
            ->setMethods(array('evalTreatment'))
            ->getMock();

        $evaluator->method('evalTreatment')->willReturn(array('treatment' =>'on'));
        $evaluator->expects($this->once())
            ->method('evalTreatment')
            ->with('key1', null, 'feature1', null);
        
        $client = new MatcherClient($evaluator);
        $treatment = $client->getTreatment('key1', 'feature1', null);
        $this->assertEquals($treatment, 'on');
    }

    public function testGetTreatmentFailure()
    {
        $evaluator = $this
            ->getMockBuilder('\SplitIO\Sdk\Evaluator')
            ->disableOriginalConstructor()
            ->setMethods(array('evalTreatment'))
            ->getMock();

        $evaluator->method('evalTreatment')->will($this->throwException(new \Exception()));
        $evaluator->expects($this->once())
            ->method('evalTreatment')
            ->with('key1', null, 'feature1', null);
        
        $client = new MatcherClient($evaluator);
        $treatment = $client->getTreatment('key1', 'feature1', null);
        $this->assertEquals($treatment, \SplitIO\Grammar\Condition\Partition\TreatmentEnum::CONTROL);
    }
}
