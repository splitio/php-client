<?php

namespace SplitIO\Grammar\Condition;

use SplitIO\Split as SplitApp;

class Partition
{
    private $treatment = null;

    private $size = null;

    public function __construct(array $partition)
    {
        SplitApp::logger()->debug(print_r($partition, true));

        if (isset($partition['treatment']) && !empty($partition['treatment'])) {
            $this->setTreatment($partition['treatment']);
        }

        $size = (isset($partition['size']) &&
            is_int($partition['size']) &&
            $partition['size'] >= 0 &&
            $partition['size'] <= 100) ? $partition['size'] : 0;

        $this->setSize($size);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getTreatment()
    {
        return $this->treatment;
    }

    /**
     * @param string $treatment
     */
    public function setTreatment($treatment)
    {
        $this->treatment = $treatment;
    }
}
