<?php

require_once 'Core/KafkaConsumer.php';
require_once 'Core/KafkaProducer.php';
require_once 'Core/ManticoreHandler.php';


$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);


$producer        = new KafkaProducer($config);
$consumer        = new KafkaConsumer($config);


$handler = new ManticoreHandler($consumer, $producer, $config);
$handler->handleTopics();



