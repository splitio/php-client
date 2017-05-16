<?php
namespace SplitIO\Grammar\Condition\Matcher\DataType;

class Set
{

    private $values;

    public function __construct()
    {
        $this->values = array();
    }

    public function add($item)
    {
        $this->values[$item] = true;
    }

    public function remove($item)
    {
        if (isset($this->values[$item])) {
            unset($this->values[$item]);
        }
    }

    public function count()
    {
        return count($this->values);
    }

    public function has($item)
    {
        return in_array($item, array_keys($this->values));
    }

    public function toArray()
    {
        return array_keys($this->values);
    }

    public function union($set2)
    {
        $newSet = Set::fromArray(array_keys($this->values));
        foreach ($set2->toArray() as $elem) {
            $newSet->add($elem);
        }
        return $newSet;
    }

    public function intersect($set2)
    {
        $newSet = new Set();
        foreach (array_keys($this->values) as $item) {
            if ($set2->has($item)) {
                $newSet->add($item);
            }
        };
        return $newSet;
    }

    public function isSubsetOf($set2)
    {
        foreach (array_keys($this->values) as $item) {
            if (!$set2->has($item)) {
                return false;
            }
        }
        return true;
    }

    public function equals($set2)
    {
        return ($this->isSubsetOf($set2) && $set2->isSubsetOf($this));
    }

    public static function fromArray($arr)
    {
        $newSet = new Set();
        foreach ($arr as $item) {
            $newSet->add($item);
        }
        return $newSet;
    }
}
