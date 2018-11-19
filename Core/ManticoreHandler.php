<?php


class ManticoreHandler
{
    /** @var KafkaProducer */
    private $producer;
    /** @var KafkaConsumer */
    private $consumer;
    /** @var array */
    private $config;
    /** @var PDO  */
    private $manticoreQL;


    public function __construct(KafkaConsumer $consumer, KafkaProducer $producer, $config)
    {
        $this->producer    = $producer;
        $this->consumer    = $consumer;
        $this->config      = $config;
        $this->manticoreQL = new \PDO('mysql:host=' . $this->config['manticore']['host'] . ';port=' . $this->config['manticore']['port']);
    }


    /**
     * @throws Exception
     */
    public function handleTopics()
    {

        $consumer = $this->consumer->subscribe($this->config['consumer']['topic'])->getConsumer();

        while (true) {
            $message = $consumer->consume(120*1000);

            if (empty($message->payload)) {
                continue;
            }
            json_decode($message->payload);
            if (json_last_error() === JSON_ERROR_NONE) {
                $docs[] = "'" . $message->payload . "'";
            }else{
                continue;
            }

            // run a single PQ call
            // we might want to run multiple CALL PQs in parallel -  this will require forking several processes

            $query = "CALL PQ('".$this->config['manticore']['table']."',(" . implode(",",
                    $docs) . "), 1 as docs_json ,1 as docs,1 as query,'id' as docs_id)";

            $result = $this->manticoreQL->query($query);
            $final  = [];

            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $final[] = $row;
            }

            $this->producer->send($this->config['producer']['topic'], json_encode($final));
        }
    }
}