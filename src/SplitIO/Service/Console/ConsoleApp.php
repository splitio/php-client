<?php
namespace SplitIO\Service\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleApp extends Application
{
    private $di = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    public function getHelp()
    {
        return Logo::getAsciiLogo() . $this->getLongVersion();
    }

    public function readConfig($configFile = null)
    {
        if ($configFile === null) {
            $configFile = SPLITIO_SERVICE_HOME .'/splitio.ini';
        }

        $config = false;
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile, true);

            if ($config !== false) {
                putenv('SPLITIO_SERVICE_CONFIGFILE="'.$configFile.'"');
            }
        }

        return $config;
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @throws \Exception
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        parent::run($input, $output);
    }
}