<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class Dependency extends AbstractMatcher
{
    protected $dependencyMatcherData = null;
    private $attribute = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::DEPENDENCY, $negate, $attribute);
        $this->dependencyMatcherData = $data;
        $this->attribute = $attribute;
    }

    protected function evalKey($key, \SplitIO\Sdk\MatcherClient $client = null)
    {
        $treatment = $client->getTreatment($key, $this->dependencyMatcherData['split'], $this->attribute);
        return (is_array($this->dependencyMatcherData['treatments']) &&
                in_array($treatment, $this->dependencyMatcherData['treatments']));
    }
}
