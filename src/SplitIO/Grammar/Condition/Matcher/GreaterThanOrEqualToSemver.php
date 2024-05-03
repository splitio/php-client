<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Semver\Semver;
use SplitIO\Grammar\Semver\SemverComparer;
use SplitIO\Split as SplitApp;

class GreaterThanOrEqualToSemver extends AbstractMatcher
{
    private $target;

    public function __construct($toCompare, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::GREATER_THAN_OR_EQUAL_TO_SEMVER, $negate, $attribute);
        
        $this->target = Semver::build($toCompare);
    }

    /**
     *
     * @param mixed $key
     */
    protected function evalKey($key)
    {
        if ($key == null || $this->target == null || !is_string($key)) {
            return false;
        }

        $keySemver = Semver::build($key);
        if ($keySemver == null) {
            return false;
        }

        $result = SemverComparer::do($keySemver, $this->target) >= 0;

        SplitApp::logger()->debug($this->target->getVersion() . " >= "
            . $keySemver->getVersion() . " | Result: " . $result);

        return $result;
    }
}
