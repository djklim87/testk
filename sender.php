<?php

require_once 'Core/Logger.php';

if(empty($_POST['message'])){

    echo "Error: Sending message can't be empty!\n";

}elseif (empty($_POST['topic'])){

    echo "Error: Topic for sending can't be empty!\n";

}else{

    json_decode($_POST['message']);
    if (json_last_error() === JSON_ERROR_NONE) {

        require_once 'Core/KafkaProducer.php';

        $config_file = 'config_default.ini';
        $config      = parse_ini_file($config_file, true);

        $producer = new KafkaProducer($config);

        $producer->send($_POST['topic'], $_POST['message']);

    } else {

        echo "Error: Incompatible data format!\n";
    }

}


