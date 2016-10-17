<?php
namespace SplitIO\Sdk\Manager;

class SplitView
{
    private $name;
    private $trafficType; // the name of the traffic type
    private $killed;
    private $treatments;
    private $changeNumber;

    /**
     * SplitView constructor.
     * @param $name
     * @param $trafficType
     * @param $killed
     * @param $treatments
     * @param $changeNumber
     */
    public function __construct($name, $trafficType, $killed, $treatments, $changeNumber)
    {
        $this->name = $name;
        $this->trafficType = $trafficType;
        $this->killed = $killed;
        $this->treatments = $treatments;
        $this->changeNumber = $changeNumber;
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
}
