<?php
namespace SplitIO\Grammar\Condition\Matcher\DataType;

class DateTime
{

    /**
     * @param $milliseconds
     * @return float
     */
    public static function millisecondToPHPTimestamp($milliseconds)
    {
        //The timestamp sent by server is on milliseconds. PHP timestamp is on seconds.
        return floor($milliseconds / 1000);
    }

    /**
     * @param $timestamp
     * @return int
     */
    public static function zeroOutTime($timestamp)
    {
        $dateTimeUTC = new \DateTime();
        $dateTimeUTC->setTimezone(new \DateTimeZone("UTC"));
        $dateTimeUTC->setTimestamp($timestamp);

        //Reseting time to zero
        $dateTimeUTC->setTime(0, 0, 0);

        return $dateTimeUTC->getTimestamp();
    }

    /**
     * @param $timestamp
     * @return int
     */
    public static function zeroOutSeconds($timestamp)
    {
        $dateTimeUTC = new \DateTime();
        $dateTimeUTC->setTimezone(new \DateTimeZone("UTC"));
        $dateTimeUTC->setTimestamp($timestamp);

        //Reseting seconds to zero
        $dateTimeUTC->setTime($dateTimeUTC->format('H'), $dateTimeUTC->format('i'), 0);

        return $dateTimeUTC->getTimestamp();
    }
}
