<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Matcher\DataType\DateTime;
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
    protected function evalKey($key, \SplitIO\Sdk\MatcherClient $client = null)
    {
        if (!is_long($key)) {
            return false;
        }

        SplitApp::logger()->info('---> Evaluating EQUAL_TO');

        if (isset($this->unaryNumericMatcherData['value'])) {
            $logMsg = '---> KEY: '.$key;
            $logMsg .= PHP_EOL.'---> VAL: '.$this->unaryNumericMatcherData['value'];
            $logMsg .= PHP_EOL.'---> ATR: '.$this->attribute;
            SplitApp::logger()->info($logMsg);

            if (isset($this->unaryNumericMatcherData['dataType'])
                && DataTypeEnum::isValid($this->unaryNumericMatcherData['dataType']) ) {
                if (DataTypeEnum::DATETIME == $this->unaryNumericMatcherData['dataType']) {
                    $phpTimestamp   = DateTime::millisecondToPHPTimestamp($this->unaryNumericMatcherData['value']);
                    return DateTime::zeroOutTime($phpTimestamp) == DateTime::zeroOutTime($key);
                }
            }

            return $key == $this->unaryNumericMatcherData['value'];
        }

        return false;
    }
}
