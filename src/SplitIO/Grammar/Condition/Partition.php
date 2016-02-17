<?php
namespace SplitIO\Grammar\Condition;

/*
{
  "treatment": "on",
  "size": 10
}
*/

class Partition
{
    private $treatment = null;

    private $size = null;

    public function __construct(array $partition)
    {
        \SplitIO\Common\Di::getInstance()->getLogger()->debug(print_r($partition, true));

        if (isset($partition['treatment']) && !empty($partition['treatment'])) {
            $this->treatment = $partition['treatment'];
        }

        $this->size = (isset($partition['size']) &&
            is_int($partition['size']) &&
            $partition['size'] >= 0 &&
            $partition['size'] <= 100) ? $partition['size'] : 0;
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
