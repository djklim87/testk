<?php

class KafkaProducer
{

    private $producer;

    public function __construct($config) {
        Logger::log('Producer class: rdkafka init');
        $this->producer = new RdKafka\Producer();
        $this->producer->setLogLevel(LOG_DEBUG);
        Logger::log('Producer class: add brokers - '.$config['kafka']['brokers']);
        $this->producer->addBrokers($config['kafka']['brokers']);
    }

    public function send($topic, $message){
        Logger::log('Producer class: subscribe topic - '.$topic);
        $topic = $this->producer->newTopic($topic);
        Logger::log('Producer class: start send message');
        $result = $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        echo 'Sended. Result: '.json_encode($result);
        Logger::log('Producer class: send complete');
    }
}

