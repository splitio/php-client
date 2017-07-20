<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Key;

class Dependency
{
    protected $dependencyMatcherData = null;
    protected $type = null;
    protected $negate = false;
    protected $attribute = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        $this->type = Matcher::IN_SPLIT_TREATMENT;
        $this->dependencyMatcherData = $data;
        $this->negate = $negate;
        $this->attribute = $attribute;
    }

    public function isNegate()
    {
        return $this->negate;
    }

    public function evalKey($key, $attributes = null, $bucketingKey = null)
    {
        $client = Di::getMatcherClient();
        $_key = ($bucketingKey == null) ? $key : new Key($key, $bucketingKey);
        $treatment = $client->getTreatment($_key, $this->dependencyMatcherData['split'], $attributes);
        return (is_array($this->dependencyMatcherData['treatments']) &&
                in_array($treatment, $this->dependencyMatcherData['treatments']));
    }
}
