<?php
namespace SplitIO\Component\Process;

class Process
{
    const STATUS_READY = 'ready';
    const STATUS_STARTED = 'started';
    const STATUS_TERMINATED = 'terminated';

    private $userCommand = null;
    private $process;
    private $processInformation;
    private $pipes;
    private $status;
    private $stdoutBuffer;
    private $stderrBuffer;

    public function __construct($cmd)
    {
        if (!function_exists('proc_open')) {
            throw new \RuntimeException('The Process class relies on proc_open, ' .
                                        'which is not available on your PHP installation.');
        }

        $this->userCommand = $cmd;
        $this->pipes = array();
        $this->processInformation = array();

        $this->status = self::STATUS_READY;

        $this->stderrBuffer = "";
        $this->stdoutBuffer = "";
    }

    public function __destruct()
    {
        if (is_resource($this->process)) {
            @proc_terminate($this->process);
        }
    }

    public function getCommandLine()
    {
        return $this->userCommand;
    }

    public function start()
    {
        $descriptor = array(
                            array('pipe', 'r'),
                            array('pipe', 'w'), // stdout
                            array('pipe', 'w'), // stderr
                        );

        $this->process = proc_open($this->userCommand, $descriptor, $this->pipes);

        if (!is_resource($this->process)) {
            throw new \RuntimeException('Unable to launch a new process.');
        }

        $this->status = self::STATUS_STARTED;

        $this->updateStatus();
    }

    public function isStarted()
    {
        return $this->status != self::STATUS_READY;
    }

    public function wait()
    {
        while ($this->isRunning()) {
            usleep(1000);
        }

        return;
    }

    public function getPid()
    {
        return $this->isRunning() ? $this->processInformation['pid'] : null;
    }

    public function isRunning()
    {
        $this->updateStatus();

        return isset($this->processInformation['running']) ? $this->processInformation['running'] : false;
    }

    public function getStdout()
    {
        $buff = $this->stdoutBuffer;
        $this->stdoutBuffer = "";
        return $buff;
    }

    public function getStderr()
    {
        $buff = $this->stderrBuffer;
        $this->stderrBuffer = "";
        return $buff;
    }

    private function updateStatus()
    {
        if (!is_resource($this->process)) {
            return;
        }

        $this->stdoutBuffer .= PHP_EOL . stream_get_contents($this->pipes[1]);
        $this->stderrBuffer .= PHP_EOL .  stream_get_contents($this->pipes[2]);

        $this->processInformation = proc_get_status($this->process);

        $running = $this->processInformation['running'];

        if (!$running) {
            $this->close();
        }
    }

    private function close()
    {
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        $this->pipes = array();

        if (is_resource($this->process)) {
            proc_close($this->process);
        }
    }
}
