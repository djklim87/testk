<?php

require_once 'Core/KafkaConsumer.php';
require_once 'Core/ManticoreHandler.php';
require_once 'Core/Logger.php';
require_once 'Core/Sender.php';


$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);

Logger::log('Handler init');
$sender        = new Sender();
Logger::log('Sender class init');
$consumer        = new KafkaConsumer($config);
Logger::log('Consumer class init');

$handler = new ManticoreHandler($consumer, $sender, $config);
$handler->handleTopics();



