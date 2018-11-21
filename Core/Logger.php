<?php

class Logger
{
    const LOG_LEVEL_DEBUG = 3;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_PRODUCTION = 1;

    static function log($message, $logLevel = self::LOG_LEVEL_DEBUG)
    {
        if ( ! defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (is_array($message)) {
            $message = json_encode($message);
        }
        fwrite(STDOUT, date('Y-m-d H:i:s') . ' -> ' . $message . "\n");
    }
}