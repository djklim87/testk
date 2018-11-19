<?php

if(!empty($_POST['message'])) {
    json_decode($_POST['message']);
    if (json_last_error() === JSON_ERROR_NONE) {

        require_once 'Core/KafkaProducer.php';

        $config_file = 'config_default.ini';
        $config      = parse_ini_file($config_file, true);

        $producer = new KafkaProducer($config);

        $producer->send($config['consumer']['topic'], $_POST['message']);

    } else {
        echo 'Error: Incompatible data format!';
    }
}elseif (!empty($_POST['fill_defaults'])){


    $config_file = 'config_default.ini';
    $config      = parse_ini_file($config_file, true);
    $manticoreQL = new \PDO('mysql:host=' . $config['manticore']['host'] . ';port=' . $config['manticore']['port']);
    $manticoreQL->query("INSERT INTO ".$config['manticore']['table']." (query) VALUES ( 'search'), ('manticore search')");
    echo 'Manticore table filled';
}else{
    echo 'Error: Sending message can\'t be empty!';
}

