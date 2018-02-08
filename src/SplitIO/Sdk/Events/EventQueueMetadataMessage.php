<?php
namespace SplitIO\Sdk\Events;

class EventQueueMetadataMessage
{
    private $sdkVersion;

    private $machineIP;

    private $machineName;

    /**
     * EventQueueMetadataMessage constructor.
     */
    public function __construct()
    {
        $this->sdkVersion = 'php-' . \SplitIO\version();
        $this->machineIP = \SplitIO\getHostIpAddress();
        $this->machineName = 'unknown';
    }


    /**
     * @return mixed
     */
    public function getSdkVersion()
    {
        return $this->sdkVersion;
    }

    /**
     * @param mixed $sdkVersion
     */
    public function setSdkVersion($sdkVersion)
    {
        $this->sdkVersion = $sdkVersion;
    }

    /**
     * @return mixed
     */
    public function getMachineIP()
    {
        return $this->machineIP;
    }

    /**
     * @param mixed $machineIP
     */
    public function setMachineIP($machineIP)
    {
        $this->machineIP = $machineIP;
    }

    /**
     * @return mixed
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * @param mixed $machineName
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;
    }

    public function toArray()
    {
        return array(
            's' => $this->sdkVersion,
            'i' => $this->machineIP,
            'n' => $this->machineName
        );
    }
}
