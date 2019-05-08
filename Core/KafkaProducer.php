<?php

class KafkaProducer
{

    private $producer;

    public function __construct($config) {
        Logger::log('Producer class: rdkafka init');
        $this->producer = new RdKafka\Producer();
        $this->producer->setLogLevel(LOG_DEBUG);
        Logger::log('Producer class: add brokers - '.getenv('KAFKA_HOST'));
        $this->producer->addBrokers(getenv('KAFKA_HOST'));
    }

    public function send($topic, $message){
        Logger::log('Producer class: subscribe topic - '.$topic);
        $topic = $this->producer->newTopic($topic);
        Logger::log('Producer class: start send message');
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        Logger::log('Producer class: send complete');
    }
}

