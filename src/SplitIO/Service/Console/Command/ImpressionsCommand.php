<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Service\Console\OptionsEnum;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImpressionsCommand extends Command
{
    const IMPRESSIONS_PER_TEST_DEFAULT = 100000;

    protected function configure()
    {
        $this
            ->setName('send-impressions')
            ->setDescription('Send treatment impressions');
    }

    public function execute()
    {
        if ($this->get(OptionsEnum::IMPRESSIONS_PER_TEST)) {
            $this->getSplitClient()->sendTestImpressions((int) $this->get(OptionsEnum::IMPRESSIONS_PER_TEST));
        } else {
            $this->getSplitClient()->sendTestImpressions(self::IMPRESSIONS_PER_TEST_DEFAULT);
        }
    }
}
