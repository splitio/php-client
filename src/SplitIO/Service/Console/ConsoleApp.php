<?php
namespace SplitIO\Service\Console;

use SplitIO\Service\Console\Command\Command;
use SplitIO\Service\Console\Command\ServiceCommand;

class ConsoleApp
{
    const FG_COLOR_ERROR = "\033[0;31m";

    const FG_COLOR_INFO = "\033[1;33m";

    const FG_COLOR_COMMENT = "\033[0;32m";

    const FG_COLOR_CLOSE_MARK = "\033[0m";

    private $di = null;

    private $name = "";

    private $commands = array();

    private $_helpSeparatorLength=0;

    private $verboseMode = false;

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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getHelp()
    {
        $commandHelp = array();

        foreach ($this->commands as $k => $v) {
            if ($k != 'service') {
                $_separator = implode('', array_fill(0, $this->_helpSeparatorLength - strlen($k) + 4, ' '));
                $commandHelp[] = $k.$_separator.$v->getDescription();
            }

        }

        $helpOutput = Logo::getAsciiLogo() . PHP_EOL;
        $helpOutput .= "Split Synchronizer Service" . PHP_EOL;
        $helpOutput .= PHP_EOL . "Usage:" . PHP_EOL;
        $helpOutput .= "\tsplitio --process=\"<process>\" [options]" . PHP_EOL;
        $helpOutput .= "\tsplitio --service [options]" . PHP_EOL;
        $helpOutput .= "\tsplitio --help" . PHP_EOL . PHP_EOL;

        $helpOutput .= PHP_EOL . "Options:" . PHP_EOL;
        $helpOutput .= "-c, --config-file      Set the path to splitio.ini configuration file" . PHP_EOL;
        $helpOutput .= "-h, --help             Show this help" . PHP_EOL;
        $helpOutput .= "-v, --verbose          Enable verbose mode" . PHP_EOL;

        $helpOutput .= PHP_EOL . "Service:" . PHP_EOL;
        $helpOutput .= "-s, --service          Run this program as a service" . PHP_EOL;

        $helpOutput .= PHP_EOL . "Process:" . PHP_EOL;
        $helpOutput .= implode(PHP_EOL, $commandHelp) . PHP_EOL;

        $helpOutput .= PHP_EOL . PHP_EOL;

        $setConfigFile = $this->readConfig();

        if ($setConfigFile === false) {
            $helpOutput .= "<info>WARNING: The common config file located in " .SPLITIO_SERVICE_HOME .
                " is missing." .
                "\n         You must to provide the config file using the option '-c, --config-file'</info>" .
                PHP_EOL . PHP_EOL;
        }




        return $helpOutput;
    }

    public function writeln($message)
    {
        $message = str_replace("<error>", self::FG_COLOR_ERROR, $message);
        $message = str_replace("</error>", self::FG_COLOR_CLOSE_MARK, $message);

        $message = str_replace("<info>", self::FG_COLOR_INFO, $message);
        $message = str_replace("</info>", self::FG_COLOR_CLOSE_MARK, $message);

        $message = str_replace("<comment>", self::FG_COLOR_COMMENT, $message);
        $message = str_replace("</comment>", self::FG_COLOR_CLOSE_MARK, $message);

        echo $message . PHP_EOL;
    }

    private function checkConfigFile($configFile = null)
    {
        if ($configFile === null) {
            $configFile = SPLITIO_SERVICE_HOME .'/splitio.ini';
        }

        if (file_exists($configFile)) {
            return $configFile;
        }

        return false;
    }

    public function readConfig($configFile = null)
    {
        $configFile = $this->checkConfigFile($configFile);

        $config = false;
        if ($configFile !== false) {
            $config = parse_ini_file($configFile, true);

            if ($config !== false) {
                putenv('SPLITIO_SERVICE_CONFIGFILE="'.$configFile.'"');
            }
        }

        return $config;
    }

    public function add(Command $command)
    {
        $commandName = $command->getName();
        $strlenCommandName = strlen($commandName);

        if ($strlenCommandName > $this->_helpSeparatorLength) {
            $this->_helpSeparatorLength = $strlenCommandName;
        }

        $this->commands[$commandName] = $command;
    }

    public function getInputOptions()
    {
        $options = "h::v::p:c:s::";
        $longOptions = array("help::", "verbose::", "process:","config-file:", "service::");

        return getopt($options, $longOptions);
    }

    public function getInputConfigFile()
    {
        $parsedOptions = $this->getInputOptions();

        if (isset($parsedOptions['c'])) {
            return $parsedOptions['c'];
        }

        if (isset($parsedOptions['config-file'])) {
            return $parsedOptions['config-file'];
        }

        return null;
    }

    public function isVerbose()
    {
        return $this->verboseMode;
    }

    /**
     * @return int
     */
    public function run()
    {
        $parsedOptions = $this->getInputOptions();

        //Checking  HELP!
        if (empty($parsedOptions) ||
            isset($parsedOptions['h']) && ($parsedOptions['h'] === false) ||
            isset($parsedOptions['help']) && ($parsedOptions['help'] === false)) {

            $this->writeln($this->getHelp());
            exit(0);
        }

        if (isset($parsedOptions['v']) && ($parsedOptions['v'] === false) ||
            isset($parsedOptions['verbose']) && ($parsedOptions['verbose'] === false)) {

            $this->verboseMode = true;

        }

        if (isset($parsedOptions['s']) && ($parsedOptions['s'] === false) ||
            isset($parsedOptions['service']) && ($parsedOptions['service'] === false)) {

            $service = $this->commands['service'];

            if ($service instanceof ServiceCommand) {

                $service->run($this);
            }
        }

        $processName = null;
        if (isset($parsedOptions['p'])) {
            $processName = $parsedOptions['p'];
        }

        if (isset($parsedOptions['process'])) {
            $processName = $parsedOptions['process'];
        }

        if ($processName !== null && isset($this->commands[$processName])) {

            $cmd = $this->commands[$processName];

            if ($cmd instanceof Command) {
                $cmd->run($this);
                exit(0);
            }
        }

        $this->writeln($this->getHelp());
        exit(0);
    }
}