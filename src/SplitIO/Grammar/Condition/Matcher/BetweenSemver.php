<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Semver\Semver;
use SplitIO\Grammar\Semver\SemverComparer;
use SplitIO\Split as SplitApp;

class BetweenSemver extends AbstractMatcher
{
    protected $startTarget = null;
    protected $endTarget = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::BETWEEN_SEMVER, $negate, $attribute);
        
        $this->startTarget = Semver::build($data['start']);
        $this->endTarget = Semver::build($data['end']);
    }
    
    /**
     *
     * @param mixed $key
     */
    protected function evalKey($key)
    {
        if ($key == null || !is_string($key) || $this->startTarget == null || $this->endTarget == null) {
            return false;
        }

        $keySemver = Semver::build($key);
        if ($keySemver == null) {
            return false;
        }

        $result = SemverComparer::do($keySemver, $this->startTarget) >= 0 && SemverComparer::do($keySemver, $this->endTarget) <= 0;

        SplitApp::logger()->debug($this->startTarget->getVersion() . " <= "
            . $keySemver->getVersion() . " <= " . $this->endTarget->getVersion()
            . " | Result: " . $result);

        return $result;
    }
}
