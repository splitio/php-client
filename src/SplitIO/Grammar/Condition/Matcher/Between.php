<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Matcher\DataType\DateTime;
use SplitIO\Split as SplitApp;

class Between extends AbstractMatcher
{
    /**
     * @var array
     */
    protected $betweenMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::BETWEEN, $negate, $attribute);

        $this->betweenMatcherData = $data;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function evalKey($key, array $context = null)
    {
        if (!is_long($key)) {
            return false;
        }

        SplitApp::logger()->info('---> Evaluating BETWEEN');

        if (isset($this->betweenMatcherData['start']) && isset($this->betweenMatcherData['end'])) {
            $logMsg = '---> KEY: '.$key;
            $logMsg .= PHP_EOL.'---> START: '.$this->betweenMatcherData['start'];
            $logMsg .= PHP_EOL.'---> END: '.$this->betweenMatcherData['end'];
            $logMsg .= PHP_EOL.'---> ATR: '.$this->attribute;
            SplitApp::logger()->info($logMsg);

            if (isset($this->betweenMatcherData['dataType'])
                && DataTypeEnum::isValid($this->betweenMatcherData['dataType'])) {
                if (DataTypeEnum::DATETIME == $this->betweenMatcherData['dataType']) {
                    $phpTimestampStart = DateTime::millisecondToPHPTimestamp($this->betweenMatcherData['start']);
                    $phpTimestampEnd   = DateTime::millisecondToPHPTimestamp($this->betweenMatcherData['end']);

                    $normalizedKey = DateTime::zeroOutSeconds($key);

                    return (
                        DateTime::zeroOutSeconds($phpTimestampStart) <= $normalizedKey
                        && $normalizedKey <= DateTime::zeroOutSeconds($phpTimestampEnd)
                    );
                }
            }

            return ($this->betweenMatcherData['start'] <= $key && $key <= $this->betweenMatcherData['end']);
        }

        return false;
    }
}
