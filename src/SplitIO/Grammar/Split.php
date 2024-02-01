<?php
namespace SplitIO\Grammar;

use SplitIO\Split as SplitApp;
use SplitIO\Engine\Hash\HashAlgorithmEnum;

class Split
{
    const DEFAULT_TRAFFIC_ALLOCATION = 100;

    private $name = null;

    private $trafficTypeName = null;

    private $seed = null;

    private $status = null;

    private $killed = false;

    private $conditions = null;

    private $defaultTreatment = null;

    private $changeNumber = -1;

    private $algo = HashAlgorithmEnum::LEGACY;

    private $trafficAllocation = self::DEFAULT_TRAFFIC_ALLOCATION;

    private $trafficAllocationSeed = null;

    private $configurations = null;
    private $sets = null;

    public function __construct(array $split)
    {
        SplitApp::logger()->debug(print_r($split, true));

        $this->name = $split['name'];
        $this->trafficTypeName = $split['trafficTypeName'];
        $this->seed = $split['seed'];
        $this->status = $split['status'];
        $this->killed = $split['killed'];
        $this->defaultTreatment = $split['defaultTreatment'];
        $this->changeNumber = (isset($split['changeNumber'])) ? $split['changeNumber'] : -1;
        $this->algo = (isset($split['algo'])) ? $split['algo'] : HashAlgorithmEnum::LEGACY;
        $this->trafficAllocation = isset($split['trafficAllocation']) ?
            $split['trafficAllocation'] : self::DEFAULT_TRAFFIC_ALLOCATION;
        $this->trafficAllocationSeed = isset($split['trafficAllocationSeed']) ?
            $split['trafficAllocationSeed'] : null;
        $this->configurations = isset($split['configurations']) && count($split['configurations']) > 0 ?
            $split['configurations'] : null;
        $this->sets = isset($split['sets']) ? $split['sets'] : array();
        
        SplitApp::logger()->info("Constructing Feature Flag: ".$this->name);

        if (isset($split['conditions']) && is_array($split['conditions'])) {
            $this->conditions = array();
            foreach ($split['conditions'] as $condition) {
                $this->conditions[] = new Condition($condition);
            }
        }
    }

    /**
     * @return bool
     */
    public function killed()
    {
        return (bool) $this->killed;
    }

    /**
     * @return string
     */
    public function getDefaultTratment()
    {
        return $this->defaultTreatment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return int
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * @return int
     */
    public function getChangeNumber()
    {
        return $this->changeNumber;
    }

    /**
     * @return null
     */
    public function getTrafficTypeName()
    {
        return $this->trafficTypeName;
    }

    /**
     * @return null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getTreatments()
    {
        if (!$this->conditions) {
            return array();
        }

        $treatments = array();
        foreach ($this->conditions as $condition) {
            $condTreatments = $condition->getTreatments();
            foreach ($condTreatments as $treatment) {
                $treatments[$treatment] = true;
            }
        }

        return array_keys($treatments);
    }

    /**
     * @return HashAlgorithmEnum
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    public function getTrafficAllocation()
    {
        return $this->trafficAllocation;
    }

    public function getTrafficAllocationSeed()
    {
        return $this->trafficAllocationSeed;
    }

    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @return array|null
     */
    public function getSets()
    {
        return $this->sets;
    }
}
