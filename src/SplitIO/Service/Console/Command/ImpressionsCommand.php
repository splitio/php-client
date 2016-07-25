<?php
namespace SplitIO\Service\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImpressionsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('send-impressions')
            ->setDescription('Send treatment impressions');
    }

    public function execute()
    {
        $this->getSplitClient()->sendTestImpressions();
    }
}