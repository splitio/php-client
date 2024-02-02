<?php

namespace SplitIO\Component\Common;

use ErrorException;

class ErrorHandler
{
    /**
     * Errors stack
     * @var array
     */
    protected static $errStack = array();

    /**
     *  Register function to catch the PHP Fatal Error.
     */
    public static function registerShutdown()
    {
        register_shutdown_function(array(get_called_class(), 'shutdown'));
    }

    /**
     * Static Method registered to catch Fatal Errors
     */
    public static function shutdown()
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $type = $error["type"];
        $file = $error["file"];
        $line = $error["line"];
        $message  = $error["message"];

        /** @todo Log the fatal Error */
        //echo "Error `$type` in file `$file` on line $line with message `$message`";
    }

    /**
     * Method to start the custom error handler
     * @param int $errorLevel
     */
    public static function start($errorLevel = \E_WARNING)
    {
        if (!static::$errStack) {
            set_error_handler(array(get_called_class(), 'addError'), $errorLevel);
        }
        static::$errStack[] = null;
    }

    /**
     * Method to stop the custon error handler
     * @param bool|false $throw
     * @return mixed|null
     * @throws mixed
     */
    public static function stop($throw = false)
    {
        $errorException = null;
        if (static::$errStack) {
            $errorException = array_pop(static::$errStack);
            if (!static::$errStack) {
                restore_error_handler();
            }
            if ($errorException && $throw) {
                throw $errorException;
            }
        }
        return $errorException;
    }

    /**
     * Method to add the caught error
     * @param $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        $errStack = & static::$errStack[count(static::$errStack) - 1];
        $errStack = new ErrorException($errstr, 0, $errno, $errfile, $errline, $errStack);
    }
}
