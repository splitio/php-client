<?php

declare(strict_types=1);

namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\Manager\LocalhostSplitManager;

class LocalhostSplitManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Regression test that SplitView constructor is called with correct number of arguments
     *
     * @see https://github.com/splitio/php-client/issues/240
     */
    public function testSplitViewConstructorArgumentCount()
    {
        $manager = new LocalhostSplitManager([]);

        // This should not throw any "too few arguments" or "too many arguments" errors
        $splitViews = $manager->splits();
        $this->assertCount(0, $splitViews);
    }
}