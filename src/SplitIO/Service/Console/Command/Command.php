<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Http\Uri;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Service\Client\SplitIOClient;
use SplitIO\Service\Console\ConsoleApp;
use SplitIO\Service\Console\OptionsEnum;
use SplitIO\Component\Utils as SplitIOUtils;

abstract class Command
{

    private $name;

    private $description;

    private $config = array();

    private $application;

    /**
     * @var null|OutputInterface
     */
    private $output = null;

    /**
     * @var null|InputInterface
     */
    private $input = null;

    public function __construct()
    {
        $this->configure();
    }


    public function run(ConsoleApp $application)
    {
        $this->registerApplication($application);
        $this->initialize();
        $this->execute();
    }

    public function registerApplication(ConsoleApp $application)
    {
        $this->application = $application;
    }

    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return ConsoleApp
     */
    protected function getApplication()
    {
        return $this->application;
    }

    private function parseConfiguration()
    {
        $customConfigFile = $this->getApplication()->getInputConfigFile();

        $iniOptions = $this->getApplication()->readConfig($customConfigFile);

        if (!$iniOptions) {
            $this->error("The configuration file is missing");
            exit(1);
        }

        $this->config = $iniOptions;
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

        try {
            CacheTrait::addCache($cacheAdapter, $options);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
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
        $this->getApplication()->writeln("<error>$message</error>");
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        $this->getApplication()->writeln("<info>$message</info>");
    }

    /**
     * @param $message
     */
    public function comment($message)
    {
        $this->getApplication()->writeln("<comment>$message</comment>");
    }

    public function initialize()
    {
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
    }


    /**
     * Configure the name and the description of the command
     * @return mixed
     */
    protected abstract function configure();

    /**
     * Anstract method called to execute the command
     * @return mixed
     */
    public abstract function execute();

}