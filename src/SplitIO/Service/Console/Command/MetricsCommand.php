<?php
namespace SplitIO\Service\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetricsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('process:send-metrics')
            ->setDescription('Send SDK metrics to the server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSplitClient()->sendMetrics();
    }
}