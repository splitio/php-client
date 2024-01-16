<?php
namespace SplitIO\Sdk\Manager;

class SplitView
{
    private $name;
    private $trafficType; // the name of the traffic type
    private $killed;
    private $treatments;
    private $changeNumber;
    private $configs;
    private $defaultTreatment;
    private $sets;

    /**
     * SplitView constructor.
     * @param $name
     * @param $trafficType
     * @param $killed
     * @param $treatments
     * @param $changeNumber
     * @param $configurations
     * @param $defaultTreatment
     * @param $sets
     */
    public function __construct($name, $trafficType, $killed, $treatments, $changeNumber, $configs, $defaultTreatment, $sets)
    {
        $this->name = $name;
        $this->trafficType = $trafficType;
        $this->killed = $killed;
        $this->treatments = $treatments;
        $this->changeNumber = $changeNumber;
        $this->configs = $configs;
        $this->defaultTreatment = $defaultTreatment;
        $this->sets = $sets;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTrafficType()
    {
        return $this->trafficType;
    }

    /**
     * @param mixed $trafficType
     */
    public function setTrafficType($trafficType)
    {
        $this->trafficType = $trafficType;
    }

    /**
     * @return mixed
     */
    public function getKilled()
    {
        return $this->killed;
    }

    /**
     * @param mixed $killed
     */
    public function setKilled($killed)
    {
        $this->killed = $killed;
    }

    /**
     * @return mixed
     */
    public function getTreatments()
    {
        return $this->treatments;
    }

    /**
     * @param mixed $treatments
     */
    public function setTreatments($treatments)
    {
        $this->treatments = $treatments;
    }

    /**
     * @return mixed
     */
    public function getChangeNumber()
    {
        return $this->changeNumber;
    }

    /**
     * @param mixed $changeNumber
     */
    public function setChangeNumber($changeNumber)
    {
        $this->changeNumber = $changeNumber;
    }

    /**
     * @return mixed
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param mixed $configs
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }

    /**
     * @param mixed $defaultTreatment
     */
    public function setDefaultTreatment($defaultTreatment)
    {
        $this->defaultTreatment = $defaultTreatment;
    }

    /**
     * @return mixed
     */
    public function getDefaultTreatment()
    {
        return $this->defaultTreatment;
    }

    /**
     * @param mixed $sets
     */
    public function setSets($sets)
    {
        $this->sets = $sets;
    }

    /**
     * @return mixed
     */
    public function getSets()
    {
        return $this->sets;
    }
}
