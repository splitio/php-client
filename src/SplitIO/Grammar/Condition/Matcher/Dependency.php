<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Key;

class Dependency
{
    protected $splitName = null;
    protected $treatments = null;
    protected $type = null;
    protected $negate = false;
    protected $attribute = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        $this->type = Matcher::IN_SPLIT_TREATMENT;
        $this->splitName = $data['split'];
        $this->treatments = $data['treatments'];
        $this->negate = $negate;
        $this->attribute = $attribute;
    }

    public function isNegate()
    {
        return $this->negate;
    }

    public function evalKey($key, $attributes = null, $bucketingKey = null)
    {
        $evaluator = Di::getEvaluator();
        $result = $evaluator->evaluateFeature($key, $bucketingKey, $this->splitName, $attributes);
        return (is_array($this->treatments) && in_array($result['treatment'], $this->treatments));
    }
}
