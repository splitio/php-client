<?php
namespace SplitIO\Grammar;

use SplitIO\Split as SplitApp;

class Split
{
    private $orgId = null;

    private $environment = null;

    private $name = null;

    private $trafficTypeId = null;

    private $trafficTypeName = null;

    private $seed = null;

    private $status = null;

    private $killed = false;

    private $conditions = null;

    private $defaultTreatment = null;

    public function __construct(array $split)
    {
        SplitApp::logger()->debug(print_r($split, true));

        $this->orgId = $split['orgId'];
        $this->environment = $split['environment'];
        $this->name = $split['name'];
        $this->trafficTypeId = $split['trafficTypeId'];
        $this->trafficTypeName = $split['trafficTypeName'];
        $this->seed = $split['seed'];
        $this->status = $split['status'];
        $this->killed = $split['killed'];
        $this->defaultTreatment = $split['defaultTreatment'];

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
}
