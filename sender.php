<?php

require_once 'Core/Logger.php';

if(!empty($_POST['message'])) {
    json_decode($_POST['message']);
    if (json_last_error() === JSON_ERROR_NONE) {

        require_once 'Core/KafkaProducer.php';

        $config_file = 'config_default.ini';
        $config      = parse_ini_file($config_file, true);

        $producer = new KafkaProducer($config);

        $producer->send($config['consumer']['topic'], $_POST['message']);

    } else {
        echo "Error: Incompatible data format!\n";
    }
}elseif (!empty($_POST['fill_defaults'])){


    $config_file = 'config_default.ini';
    $config      = parse_ini_file($config_file, true);

    try {
        $manticoreQL = new \PDO('mysql:host=' . $config['manticore']['host'] . ';port=' . $config['manticore']['port']);
    } catch (PDOException $e) {

        die("Sender: Manticore connection error: " . $e->getMessage()."\n");
    }


    $manticoreQL->query("INSERT INTO ".$config['manticore']['table']." (query) VALUES ( 'search')");
    $manticoreQL->query("INSERT INTO ".$config['manticore']['table']." (query) VALUES ('manticore search')");
    echo "Manticore table filled\n";
}else{
    echo "Error: Sending message can't be empty!\n";
}

