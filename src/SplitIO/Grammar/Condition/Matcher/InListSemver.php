<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Semver\Semver;
use SplitIO\Grammar\Semver\SemverComparer;

class InListSemver extends AbstractMatcher
{
    private $targetList;

    public function __construct($targetList, $negate = false, $attribute = null)
    {
        $this->targetList = array();
        parent::__construct(Matcher::IN_LIST_SEMVER, $negate, $attribute);

        if (is_array($targetList)) {
            foreach ($targetList as $item) {
                $toAdd = Semver::build($item);
    
                if ($toAdd != null) {
                    array_push($this->targetList, $toAdd);
                }
            }
        }
    }

    /**
     *
     * @param mixed $key
     */
    protected function evalKey($key)
    {
        if ($key == null || !is_string($key) || count($this->targetList) == 0) {
            return false;
        }

        $keySemver = Semver::build($key);
        if ($keySemver == null) {
            return false;
        }

        foreach ($this->targetList as $item) {
            if (SemverComparer::equals($keySemver, $item)) {
                return true;
            }
        }

        return false;
    }
}
