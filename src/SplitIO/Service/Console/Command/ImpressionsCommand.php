<?php
namespace SplitIO\Service\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImpressionsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('process:send-impressions')
            ->setDescription('Send treatment impressions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSplitClient()->sendTestImpressions();
    }
}