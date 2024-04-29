<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Semver\Semver;
use SplitIO\Grammar\Semver\SemverComparer;
use SplitIO\Split as SplitApp;

class EqualToSemver extends AbstractMatcher
{
    private $toCompare;

    public function __construct($toCompare, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::EQUAL_TO_SEMVER, $negate, $attribute);
        
        $this->toCompare = Semver::build($toCompare);
    }
    
    /**
     *
     * @param mixed $key
     */
    protected function evalKey($key)
    {
        if ($key == null || $this->toCompare == null || !is_string($key)) {
            return false;
        }

        $keySemver = Semver::build($key);
        if ($keySemver == null) {
            return false;
        }

        $result = SemverComparer::Equals($this->toCompare, $keySemver);

        SplitApp::logger()->debug($this->toCompare->getVersion() . " == "
            . $keySemver->getVersion() . " | Result: " . $result);

        return $result;
    }
}
