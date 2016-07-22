<?php
namespace SplitIO\Component\Http;


class ResponseHelper
{
    public static function isSuccessful($statusCode)
    {
        if (false === is_int($statusCode)) {
            trigger_error('ResponseHelper::isSuccessful expected Argument 1 to be Integer', E_USER_WARNING);
        }

        return $statusCode >= 200 && $statusCode < 300;
    }
}