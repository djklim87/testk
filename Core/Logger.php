<?php

class Logger
{
    public static $timeMeasures = [];
    public static $timeInSeconds = [];

    const LOG_LEVEL_DEBUG = 3;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_PRODUCTION = 1;


    static function startTimeMeasure($name)
    {
        self::$timeMeasures[$name] = microtime(true);
    }

    static function endTimeMeasure($name)
    {
        $now = microtime(true);
        if (empty(self::$timeInSeconds[$name])) {
            self::$timeInSeconds[$name] = 0;
        }
        self::$timeInSeconds[$name] += $now - self::$timeMeasures[$name];

        self::startTimeMeasure($name);
    }

    static function getTimeMeasureResults()
    {
        return print_r(self::$timeInSeconds, true);
    }

    static function log($message, $logStd = true, $logLevel = self::LOG_LEVEL_DEBUG)
    {
        if ( ! defined('STDOUT') && $logStd) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (is_array($message)) {
            $message = json_encode($message);
        }

        if ($logStd) {
            fwrite(STDOUT, date('Y-m-d H:i:s') . ' -> ' . $message . "\n");
        } else {
            file_put_contents(
                '/tmp/debug.txt',
                date('Y-m-d H:i:s', time()) . ' -> ' . $message . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }
    }
}