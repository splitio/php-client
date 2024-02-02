<?php

namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Exception\Exception;

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

    public function evalKey($key, $attributes = null, $bucketingKey = null, $context = null)
    {
        if (!isset($context['evaluator'])) {
            throw new Exception('Evaluator not present in matcher context.');
        }
        $evaluator = $context['evaluator'];
        $result = $evaluator->evaluateFeature($key, $bucketingKey, $this->splitName, $attributes);
        return (is_array($this->treatments) && in_array($result['treatment'], $this->treatments));
    }
}
