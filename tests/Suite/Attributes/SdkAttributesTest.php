<?php
namespace SplitIO\Test\Suite\Engine;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;

class SdkAttributesTest extends \PHPUnit\Framework\TestCase
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
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => LOG_ADAPTER, 'level' => 'info'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('some-api-key', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        //Assertions
        $this->inOperator($splitSdk);
        $this->equalToOperator($splitSdk);
        $this->greaterThanOrEqualToOperator($splitSdk);
        $this->lessThanOrEqualToOperator($splitSdk);
        $this->betweenOperator($splitSdk);
    }

    private function inOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // IN Operator
        //if user.account is in segment all then 100%:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', array('account' => 'my_new_user')));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_account_in_segment_all', null));

        //if user.plan is in list [“pro”, “premium”] then 100%:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', array('plan' => 'pro')));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', array('plan' => 'premium')));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', array('plan' => 'standard')));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_plan_in_whitelist', null));
    }

    private function equalToOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // = Operator
        //if user.attr = 0 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', array('attr' => 0)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', array('attr' => -0)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_zero', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', array('attr' => 0)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', array('attr' => -0)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_zero', null));

        //if user.attr = 10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', array('attr' => 10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', array('attr' => -10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_number_ten', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', array('attr' => 10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', array('attr' => -10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_ten', null));

        //if user.attr = -10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', array('attr' => -10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', array('attr' => 10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_number_ten', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', array('attr' => -10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', array('attr' => 10)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', array('attr' => 15)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_negative_ten', null));

        //if user.attr = datetime 1458240947021 then split 100:on
        //For DATETIME the EQUAL_TO remove the time in order to compare only the date.
        $date = new \DateTime("2016/03/17 06:55:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 09:12PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 00:00:00", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 23:59:59", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));

        $date = new \DateTime("2016/03/16 23:59:59", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/18 00:00:00", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_eq_datetime_1458240947021', null));
    }

    private function greaterThanOrEqualToOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // >= Operator
        //if user.attr >= 10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_10', array('attr' => 10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_10', array('attr' => 11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', array('attr' => 9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', array('attr' => 10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', array('attr' => 11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', array('attr' => 9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_number_10', null));

        //if user.attr >= -10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', array('attr' => -9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', array('attr' => -9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_negative_number_10', null));

        //if user.attr >= datetime 1458240947021 then split 100:on
        $date = new \DateTime("2016/03/17 06:55:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:55:00PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:56:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2025/04/17 09:56:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:54:22PM", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_gte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
    }

    private function lessThanOrEqualToOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // <= Operator
        //if user.attr <= 10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_10', array('attr' => 10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_10', array('attr' => 9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_10', array('attr' => 11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_number_10', array('attr' => 10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_number_10', array('attr' => 9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_number_10', array('attr' => 11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_number_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_number_10', null));

        //if user.attr <= -10 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_10', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_10', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_10', array('attr' => -9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_10', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_number_10', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_number_10', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_number_10', array('attr' => -9)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_number_10', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_negative_number_10', null));

        //if user.attr <= datetime 1458240947021 then split 100:on
        $date = new \DateTime("2016/03/17 06:55:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:55:00PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:54:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/02/10 09:24:23PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_lte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 06:56:22PM", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_lte_datetime_1458240947021', array('attr' => $date->getTimestamp())));
    }

    private function betweenOperator(\SplitIO\Sdk\ClientInterface $splitSdk)
    {
        // Between Operator
        //if user.attr is between -10 and 20 then split 100:on
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array('attr' => 0)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array('attr' => 20)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array('attr' => 21)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_negative_10_and_20', null));

        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array('attr' => -10)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array('attr' => 0)));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array('attr' => 20)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array('attr' => -11)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array('attr' => 21)));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_number_negative_10_and_20', null));

        //if user.attr is between datetime 1458240947021 and 1459452812642 then split 100:on
        $date = new \DateTime("2016/03/17 06:55:47PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/17 08:34:44PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/31 07:33:32PM", new \DateTimeZone("UTC"));
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("1995/12/17 06:24:00AM", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array('attr' => $date->getTimestamp())));
        $date = new \DateTime("2016/03/31 07:35:48PM", new \DateTimeZone("UTC"));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array('attr' => $date->getTimestamp())));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', array()));
        $this->assertEquals('off', $splitSdk->getTreatment('user1', 'user_attr_btw_datetime_1458240947021_and_1458246884077', null));
    }
}
