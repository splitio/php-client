<?php
namespace SplitIO\Service\Console;

class Logo
{
    public static function getAsciiLogo()
    {
        return <<<EOF

       __      ____        _ _ _
      / /__   / ___| _ __ | (_) |_
     / / \ \  \___ \| '_ \| | | __|
     \ \  \ \  ___) | |_) | | | |_
      \_\ / / |____/| .__/|_|_|\__|
         /_/        |_|


EOF;
    }

    public static function getColoredAsciiLogo()
    {
        return "\033[1;34m" . self::getAsciiLogo() . "\033[0m";
    }
}
