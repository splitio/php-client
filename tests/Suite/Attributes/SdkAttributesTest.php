<?php
namespace SplitIO\Test\Suite\Engine;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;

class SdkAttributesTest extends \PHPUnit_Framework_TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitChanges.json");
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    private function addSegmentsInCache()
    {
        $segmentCache = new SegmentCache();

        //Addinng Employees Segment.
        $segmentEmployeesChanges = file_get_contents(__DIR__."/files/segmentEmployeesChanges.json");
        $this->assertJson($segmentEmployeesChanges);
        $segmentData = json_decode($segmentEmployeesChanges, true);
        $this->assertArrayHasKey('employee_1', $segmentCache->addToSegment($segmentData['name'], $segmentData['added']));

        //Adding Human Beigns Segment.
        $segmentHumanBeignsChanges = file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json");
        $this->assertJson($segmentHumanBeignsChanges);
        $segmentData = json_decode($segmentHumanBeignsChanges, true);
        $this->assertArrayHasKey('user1', $segmentCache->addToSegment($segmentData['name'], $segmentData['added']));

    }

    public function testClient()
    {

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $sdkConfig = [
            'log' => ['adapter' => 'stdout', 'level' => 'info'],
            'cache' => ['adapter' => 'redis', 'options' => ['host' => REDIS_HOST, 'port' => REDIS_PORT]]
        ];

        //Initializing the SDK instance.
        $splitSdk = \SplitIO\Sdk::factory('some-api-key', $sdkConfig);

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        //Assertions
        $this->inOperator($splitSdk);
        $this->equalToOperator($splitSdk);
        $this->greaterThanOrEqualToOperator($splitSdk);

    }

    private function inOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // IN Operator
        //if user.account is in segment all then 100%:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', ['account' => 'my_new_user']));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', null));

        //if user.plan is in list [“pro”, “premium”] then 100%:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', ['plan' => 'pro']));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', ['plan' => 'premium']));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', ['plan' => 'standard']));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', null));
    }

    private function equalToOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // = Operator
        //if user.attr = 0 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', ['attr' => 0]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', ['attr' => -0]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', ['attr' => 0]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', ['attr' => -0]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', null));

        //if user.attr = 10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', ['attr' => 10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', ['attr' => -10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', ['attr' => 10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', ['attr' => -10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', null));

        //if user.attr = -10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', ['attr' => -10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', ['attr' => 10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', ['attr' => -10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', ['attr' => 10]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', ['attr' => 15]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', null));

        //if user.attr = datetime 1458240947021 then split 100:on
        //For DATETIME the EQUAL_TO remove the time in order to compare only the date.
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 06:55:23PM", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 09:12PM", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 00:00:00", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 23:59:59", new \DateTimeZone("UTC")))->getTimestamp()]));

        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/16 23:59:59", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/18 00:00:00", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', null));

    }

    private function greaterThanOrEqualToOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // >= Operator
        //if user.attr >= 10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_10', ['attr' => 10]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_10', ['attr' => 11]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', ['attr' => 9]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', ['attr' => 10]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', ['attr' => 11]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', ['attr' => 9]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', null));

        //if user.attr >= -10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', ['attr' => -10]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', ['attr' => -9]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', ['attr' => -11]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', ['attr' => -10]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', ['attr' => -9]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', ['attr' => -11]));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', []));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', null));

        //if user.attr >= datetime 1458240947021 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 06:55:23PM", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 06:55:00PM", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 06:56:23PM", new \DateTimeZone("UTC")))->getTimestamp()]));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', ['attr' => (new \DateTime("2025/04/17 09:56:23PM", new \DateTimeZone("UTC")))->getTimestamp()]));

        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', ['attr' => (new \DateTime("2016/03/17 06:54:22PM", new \DateTimeZone("UTC")))->getTimestamp()]));

    }
}
