<?php
namespace SplitIO\Grammar;

use SplitIO\Split as SplitApp;

class Split
{
    private $name = null;

    private $trafficTypeName = null;

    private $seed = null;

    private $status = null;

    private $killed = false;

    private $conditions = null;

    private $defaultTreatment = null;

    private $changeNumber = -1;

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


        SplitApp::logger()->info("Constructing Split: ".$this->name);

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
        $treatments = array();

        if ($this->conditions) {
            $condition = $this->conditions[0];
            $treatments = $condition->getTreatments();
        }

        return $treatments;
    }
}
