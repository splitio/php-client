<?php
namespace SplitIO\Grammar\Condition;

use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

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

        $this->treatment = (isset($partition['treatment']) && !empty($partition['treatment']))
            ? new TreatmentEnum($partition['treatment'])
            : new TreatmentEnum(TreatmentEnum::OFF);

        $this->size = (isset($partition['size']) &&
            is_int($partition['size']) &&
            $partition['size'] >= 0 &&
            $partition['size'] <= 100) ? $partition['size'] : 100;
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
     * @return \SplitIO\Grammar\Condition\Partition\TreatmentEnum
     */
    public function getTreatment()
    {
        return $this->treatment;
    }

    /**
     * @param \SplitIO\Grammar\Condition\Partition\TreatmentEnum
     */
    public function setTreatment(TreatmentEnum $treatment)
    {
        $this->treatment = $treatment;
    }
}
