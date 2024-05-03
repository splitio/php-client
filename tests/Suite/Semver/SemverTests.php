<?php
namespace SplitIO\Test\Suite\Metrics;

use SplitIO\Component\Common\Di;
use SplitIO\Grammar\Semver\Semver;

class SemverTests extends \PHPUnit\Framework\TestCase
{
    private function setupSplitApp()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $parameters = array(
            'scheme' => 'redis',
            'host' => "localhost",
            'port' => 6379,
            'timeout' => 881
        );
        $options = array('prefix' => TEST_PREFIX);
        $sdkConfig = array(
            'log' => array('adapter' => 'stdout', 'level' => 'info'),
            'cache' => array('adapter' => 'predis',
                'parameters' => $parameters,
                'options' => $options
            )
        );

        $splitFactory = \SplitIO\Sdk::factory('apikey', $sdkConfig);
        $splitFactory->client();
    }
    
    public function testValidSemver()
    {
        $this->setupSplitApp();

        $handle = fopen(__DIR__."/../../files/valid-semantic-versions.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $v1 = str_replace("\n","",$_line[0]);
                $v2 = str_replace("\n","",$_line[1]);

                $semver1 = Semver::build($v1);
                $semver2 = Semver::build($v2);

                $this->assertNotNull($semver1, $v1);
                $this->assertNotNull($semver2, $v2);
                $this->assertEquals($v1, $semver1->getVersion());
                $this->assertEquals($v2, $semver2->getVersion());
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }

    public function testInvalidSemver()
    {
        $this->setupSplitApp();

        $handle = fopen(__DIR__."/../../files/invalid-semantic-versions.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $v1 = str_replace("\n","",$_line[0]);

                $this->assertNull(Semver::build($v1));
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }
}