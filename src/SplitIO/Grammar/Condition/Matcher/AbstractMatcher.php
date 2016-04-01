<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;

abstract class AbstractMatcher
{
    protected $type = null;

    protected $negate = false;

    protected $attribute = null;

    protected function __construct($type, $negate = false, $attribute = null)
    {
        SplitApp::logger()->info("Constructing matcher of type: ".$type);

        $this->type = $type;

        $this->negate = $negate;

        $this->attribute = $attribute;
    }

    public function evaluate($key)
    {
        SplitApp::logger()->info("Evaluating on {$this->type} the KEY $key");

        return $this->evalKey($key);
    }

    public function isNegate()
    {
        return $this->negate;
    }

    public function hasAttribute()
    {
        return $this->attribute !== null;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    abstract protected function evalKey($key);
}
