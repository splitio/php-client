<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Service\Console\OptionsEnum;
use SplitIO\Component\Process\Process;

class ServiceCommand extends Command
{
    const BIN_PHP = '/usr/bin/env php';

    private $process = array();

    protected function configure()
    {
        $this->setName('service')
            ->setDescription('Running Split as service');
    }

    private function registerProcess($cmd, $rate)
    {
        $this->process[] = array('rate' => $rate, 'last' => time(), 'process' => new Process($cmd));
    }

    private function cmd($processCmd)
    {
        $command = array();

        //By default is php development bin script
        $splitBin = 'splitio';

        $command[] = self::BIN_PHP;
        $command[] = SPLITIO_SERVICE_HOME . DIRECTORY_SEPARATOR . $splitBin;
        $command[] = '--process="'.$processCmd.'"';
        $command[] = '--config-file='.getenv('SPLITIO_SERVICE_CONFIGFILE');

        return implode(' ', $command);
    }

    public function execute()
    {
        $this->info("Running Split Synchronizer Service ...");

        $seconds = 0.5;
        $micro = $seconds * 1000000;

        $this->registerProcess($this->cmd('fetch-splits'), $this->get(OptionsEnum::RATE_FETCH_SPLITS));
        $this->registerProcess($this->cmd('fetch-segments'), $this->get(OptionsEnum::RATE_FETCH_SEGMENTS));
        $this->registerProcess($this->cmd('send-impressions'), $this->get(OptionsEnum::RATE_SEND_IMPRESSIONS));
        $this->registerProcess($this->cmd('send-metrics'), $this->get(OptionsEnum::RATE_SEND_METRICS));

        $numOfProcess = count($this->process);
        while (true) {

            for ($i=0; $i < $numOfProcess; $i++) {

                $gap = time() - $this->process[$i]['last'];

                if ($gap >= (int) $this->process[$i]['rate']) {

                    if (!$this->process[$i]['process']->isRunning()) {
                        try {

                            $this->process[$i]['process']->start();
                            if ($this->process[$i]['process']->isStarted()) {

                                if ($this->getApplication()->isVerbose()) {
                                    $this->comment("Process started successfully: "
                                        . $this->process[$i]['process']->getCommandLine());
                                }

                            }

                        } catch (\Exception $e) {

                            $this->logger()->critical($e->getMessage());
                            $this->error($e->getMessage());

                        }

                    }
                    $this->process[$i]['last'] = time();
                }

            }

            usleep($micro);
        }
    }

}
