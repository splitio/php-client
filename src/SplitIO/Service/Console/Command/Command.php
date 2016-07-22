<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Http\Uri;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Service\Client\SplitIOClient;
use SplitIO\Service\Console\OptionsEnum;
use SplitIO\Component\Utils as SplitIOUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{

    private $config = array();

    /**
     * @var null|OutputInterface
     */
    private $output = null;

    /**
     * @var null|InputInterface
     */
    private $input = null;

    private function parseConfiguration()
    {

        $customConfigFile = $this->input->getOption(OptionsEnum::CONFIG_FILE);

        $iniOptions = $this->getApplication()->readConfig($customConfigFile);

        if (!$iniOptions) {
            $this->error("The configuration file is missing");
            exit(1);
        }

        $cliOptions = $this->input->getOptions();
        $validCliOptions = array();

        foreach ($cliOptions as $k => $v) {
            if ($v !== null) {
                $validCliOptions[$k] = $v;
            }
        }

        $this->config = array_merge($iniOptions, $validCliOptions);
    }

    /**
     * Register the logger class
     */
    private function registerLogger()
    {
        $customLog = $this->get(OptionsEnum::LOG_CUSTOM);
        if ($customLog === null) {
            LoggerTrait::addLogger($this->get(OptionsEnum::LOG_ADAPTER), $this->get(OptionsEnum::LOG_LEVEL));
        } else {
            if (file_exists($customLog)) {
                LoggerTrait::addLoggerFromFile($customLog);
            } else {
                $this->error("Custom log file not found");
                exit(1);
            }
        }
    }

    private function registerCache()
    {
        $cacheAdapter = $this->get(OptionsEnum::CACHE_ADAPTER);
        $options = array();

        if ($cacheAdapter == 'redis') {
            $options = array(
                OptionsEnum::REDIS_HOST => $this->get(OptionsEnum::REDIS_HOST),
                OptionsEnum::REDIS_PORT => $this->get(OptionsEnum::REDIS_PORT),
                OptionsEnum::REDIS_PASS => $this->get(OptionsEnum::REDIS_PASS),
                OptionsEnum::REDIS_TIMEOUT => $this->get(OptionsEnum::REDIS_TIMEOUT),
            );

            $_redisUrl = $this->get(OptionsEnum::REDIS_URL);
            if (!empty($_redisUrl)) {
                $uri = new Uri($this->get(OptionsEnum::REDIS_URL));

                $options[OptionsEnum::REDIS_HOST] = $uri->getHost();
                $options[OptionsEnum::REDIS_PORT] = $uri->getPort();
                $options[OptionsEnum::REDIS_PASS] = $uri->getPass();
            }
        } elseif ($cacheAdapter == 'predis') {
            $options[OptionsEnum::PREDIS_OPTIONS]    = json_decode($this->get(OptionsEnum::PREDIS_OPTIONS), true);
            $options[OptionsEnum::PREDIS_PARAMETERS] = json_decode($this->get(OptionsEnum::PREDIS_PARAMETERS), true);
        }

        CacheTrait::addCache($cacheAdapter, $options);
    }

    /**
     * Register the Split Client.
     */
    private function registerSplitHttpClient()
    {
        $apiKey = $this->get(OptionsEnum::API_KEY);

        if (empty($apiKey)) {
            $this->error("THE API KEY MUST NOT BE EMPTY!");
            exit(1);
        }

        //Setting the Split Client to connect Split servers
        if (Di::get(Di::KEY_SPLIT_CLIENT) === null) {
            Di::set(Di::KEY_SPLIT_CLIENT, new SplitIOClient($apiKey));
        }
    }

    /**
     * @return \SplitIO\Service\Client\SplitIOClient;
     */
    public function getSplitClient()
    {
        return Di::get(Di::KEY_SPLIT_CLIENT);
    }

    /**
     * @return \SplitIO\Service\Console\ConsoleApp
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return Di::getLogger();
    }

    /**
     * @return null|\SplitIO\Component\Cache\Pool
     */
    public function cache()
    {
        return Di::getCache();
    }

    /**
     * @param $key
     * @return null|string
     */
    public function get($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        $this->output->writeln("<error>$message</error>");
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        $this->output->writeln("<info>$message</info>");
    }

    /**
     * @param $message
     */
    public function comment($message)
    {
        $this->output->writeln("<comment>$message</comment>");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        //Parse ini file and merge with command options.
        $this->parseConfiguration();

        //Setting the environment
        SplitIOUtils\environment($this->get(OptionsEnum::ENVIRONMENT), true);

        //Register the logger class.
        $this->registerLogger();

        //Register the cache class.
        $this->registerCache();

        //Register the SplitIO Client
        $this->registerSplitHttpClient();

        //Call _initialize method on child command
        if (method_exists($this, 'init')) {
            $this->init($input, $output);
        }
    }


}