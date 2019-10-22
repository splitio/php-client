<?php
namespace SplitIO\Sdk;

class QueueMetadataMessage
{
    private $sdkVersion;

    private $machineIP;

    private $machineName;

    /**
     * QueueMetadataMessage constructor.
     */
    public function __construct($IPAddressesEnabled = true)
    {
        $this->sdkVersion = 'php-' . \SplitIO\version();
        $this->machineIP = 'na';
        $this->machineName = 'na';
        if ($IPAddressesEnabled) {
            $this->machineIP = \SplitIO\getHostIpAddress();
            if ($this->machineIP != 'unknown') {
                $this->machineName = 'ip-' . str_replace('.', '-', $this->machineIP);
            } else {
                $this->machineName = 'unknown';
            }
        }
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
