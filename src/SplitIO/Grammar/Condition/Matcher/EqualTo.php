<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Component\Common\Di;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Split as SplitApp;

class EqualTo extends AbstractMatcher
{
    /**
     * @var array
     */
    protected $unaryNumericMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::EQUAL_TO, $negate, $attribute);

        $this->unaryNumericMatcherData = $data;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function evalKey($key)
    {
        SplitApp::logger()->info('---> Evaluating EQUAL_TO');
        if (isset($this->unaryNumericMatcherData['value'])) {
            SplitApp::logger()->info('---> KEY: '.$key);
            SplitApp::logger()->info('---> VAL: '.$this->unaryNumericMatcherData['value']);
            SplitApp::logger()->info('---> ATR: '.$this->attribute);

            if (isset($this->unaryNumericMatcherData['dataType'])
                && DataTypeEnum::isValid($this->unaryNumericMatcherData['dataType']) ) {

                if (DataTypeEnum::DATETIME == $this->unaryNumericMatcherData['dataType']) {

                    //The timestamp sent by server is on milliseconds. PHP timestamp is on seconds.
                    $value = floor($this->unaryNumericMatcherData['value'] / 1000);

                    return $key == $value;
                }
            }

            return $key == $this->unaryNumericMatcherData['value'];
        }

        return false;
    }
}