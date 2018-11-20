<?php

require_once 'Core/KafkaConsumer.php';
require_once 'Core/KafkaProducer.php';
require_once 'Core/ManticoreHandler.php';
require_once 'Core/Logger.php';


$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);

Logger::log('Handler init');
$producer        = new KafkaProducer($config);
Logger::log('Producer class init');
$consumer        = new KafkaConsumer($config);
Logger::log('Consumer class init');

$handler = new ManticoreHandler($consumer, $producer, $config);
$handler->handleTopics();



