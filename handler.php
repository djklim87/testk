<?php

require_once 'Core/KafkaConsumer.php';
require_once 'Core/ManticoreHandler.php';
require_once 'Core/Logger.php';

Logger::startTimeMeasure('all_script');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);

Logger::log('Handler init');
$consumer        = new KafkaConsumer($config);
Logger::log('Consumer class init');

$handler = new ManticoreHandler($consumer, $config);
$handler->handleTopics();



