<?php

class KafkaProducer
{

    private $producer;

    public function __construct($config) {

        $this->producer = new RdKafka\Producer();
        $this->producer->setLogLevel(LOG_DEBUG);
        $this->producer->addBrokers($config['kafka']['brokers']);
    }

    public function send($topic, $message){
        $topic = $this->producer->newTopic($topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }
}

