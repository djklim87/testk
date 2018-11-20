<?php

class KafkaConsumer
{
    private $consumer;

    public function __construct($config)
    {
        Logger::log('Consumer class: rdkafka conf init');
        $conf = new RdKafka\Conf();

        Logger::log('Consumer class: start rebalance');
        // Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(null);
                    break;

                default:
                    throw new \Exception($err);
            }
        });
        Logger::log('Consumer class: end rebalance');

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', 'myConsumerGroup');

        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', 'kafka');

        Logger::log('Consumer class: init topic conf');
        $topicConf = new RdKafka\TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');

        // Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);

        Logger::log('Consumer class: rdkafka init');
        $this->consumer = new RdKafka\KafkaConsumer($conf);
        Logger::log('Consumer class: rdkafka init complete');

    }

    public function subscribe($topic){
        Logger::log('Consumer class: subscribe to topic');
        $this->consumer->subscribe([$topic]);
        return $this;
    }

    public function getConsumer(){
        return $this->consumer;
    }
}