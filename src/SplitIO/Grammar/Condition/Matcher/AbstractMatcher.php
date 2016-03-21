<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;

abstract class AbstractMatcher
{
    protected $type = null;

    protected $negate = false;

    protected function __construct($type, $negate = false)
    {
        SplitApp::logger()->info("Constructing matcher of type: ".$type);

        $this->type = $type;

        $this->negate = $negate;
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

    abstract protected function evalKey($key);
}
