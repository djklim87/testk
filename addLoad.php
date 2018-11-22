<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Core/Logger.php';


echo "Hell script started\n";

require_once 'Core/KafkaProducer.php';

$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);

$producer = new KafkaProducer($config);

for ($i = 1; $i <= 10000; $i++) {
    $producer->send('my-docs', "{\"id\":1,\"text\":\"manticore search awesome\"}");
}

