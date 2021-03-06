<?php


class ManticoreHandler
{
    /** @var KafkaConsumer */
    private $consumer;
    /** @var array */
    private $config;
    /** @var PDO */
    private $manticoreQL;
    /** @var array */
    private $sendData = [];
    /** @var int */
    private $lastSendTime = 0;


    public function __construct(KafkaConsumer $consumer, $config)
    {
        $this->consumer = $consumer;
        $this->config   = $config;

        try {
            $this->manticoreQL = new \PDO('mysql:host=' . $this->config['manticore']['host'] . ';port=' .
                                          $this->config['manticore']['port']);
        } catch (PDOException $e) {

            die("Handler class: Manticore connection error: " . $e->getMessage() . "\n");
        }
    }


    /**
     * @throws Exception
     */
    public function handleTopics()
    {
        $this->manticoreQL->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        Logger::log('Handler class: get messages');
        $consumer = $this->consumer->subscribe($this->config['consumer']['topic'])->getConsumer();
        Logger::log('Handler class: get messages complete');

        $i = 0;
        while (true) {
            $i++;
            if ($i >= 1) {
                Logger::startTimeMeasure('get_kafka_message');
            }

            $message = $consumer->consume(120 * 1000);
            if ($i >= 1) {
                Logger::endTimeMeasure('get_kafka_message');
            }

            if (empty($message->payload)) {
                continue;
            }
            /*
            $decoded = json_decode($message->payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {

                foreach ($decoded as $k => $v) {
                    $decoded[$k] = str_replace('"', "", $v);
                }

                $encoded = json_encode($decoded);
                $encoded = str_replace("'", "\'", $encoded);

            } else {
                continue;
            }


            // run a single PQ call
            // we might want to run multiple CALL PQs in parallel -  this will require forking several processes

            if ( ! empty($encoded)) {
                $query = "CALL PQ('" . $this->config['manticore']['table'] . "',('" . $encoded . "'), 1 as docs_json, 1 as docs, 1 as query, 'id' as docs_id)";
            */
                $query = "CALL PQ('" . $this->config['manticore']['table'] . "', ".
                         "('" . str_replace("'", "\'", $message->payload) . "'), ".
                         "0 as docs_json, 1 as docs, 1 as query, 'id' as docs_id)";

                Logger::startTimeMeasure('get_manticore_result');
                try {
                    $result = $this->manticoreQL->query($query);
                } catch (Exception $exception) {
                    Logger::log('Manticore exception: ' . $exception->getMessage());
                    Logger::log($query, false);
                }

                Logger::endTimeMeasure('get_manticore_result');
                $final = [];

                if ( ! empty($result)) {

                    foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $final[] = $row;
                    }

                    if ( ! empty($final)) {
                        $sendData[] = json_encode($final);

                    } else {

                        Logger::log('Handler class: CALL PQ return empty result. Query: ');
                        Logger::log('Handler class: Manticore connect to : ' . $this->config['manticore']['host'] .
                                    ', port=' . $this->config['manticore']['port']);
                        Logger::log('Handler class: Query: ' . $query);

                    }

                } else {
                    Logger::log('Handler class: CALL PQ fatal error. ');
                }

                if ( ! empty($sendData)) {
                    $cnt = count($sendData);
                } else {
                    $cnt = 0;
                }

                $sendBy = '';


                /* send_max_batch_size - Max count of CALL PQ results, what we can store before send them to producer */
                if ($cnt >= $this->config['consumer']['send_max_batch_size']) {
                    $sendBy = 'bach size';
                }

                /** send_max_batch_wait - Limit in seconds between sends to producer*/
                if ($this->lastSendTime + $this->config['consumer']['send_max_batch_wait'] < time()) {
                    $sendBy = 'timeout';
                }

                if ($sendBy && ! empty($sendData)) {
                    Logger::startTimeMeasure('send_to_producer');
                    $this->sendToProducer($sendData);
                    Logger::endTimeMeasure('send_to_producer');

                    Logger::log('Handler class: send messages by ' . $sendBy . '. Count ' . $cnt);
                    $sendData = [];
                }
                Logger::endTimeMeasure('all_script');
            /* } */

            echo Logger::getTimeMeasureResults();
        }
    }


    private function sendToProducer($data)
    {
        $curls  = [];
        $result = [];

        $mh = curl_multi_init();

        foreach ($data as $id => $message) {

            $curls[$id] = curl_init();

            curl_setopt($curls[$id], CURLOPT_URL, 'http://producer');
            curl_setopt($curls[$id], CURLOPT_HEADER, 0);
            curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curls[$id], CURLOPT_POST, 1);

            curl_setopt($curls[$id], CURLOPT_POSTFIELDS,
                http_build_query(
                    [
                        'topic'   => $this->config['producer']['topic'],
                        'message' => $message
                    ]
                )
            );


            curl_multi_add_handle($mh, $curls[$id]);
        }

        $running = null;

        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        foreach ($curls as $id => $c) {
            $result[$id] = curl_multi_getcontent($c);
            curl_multi_remove_handle($mh, $c);
        }

        $this->lastSendTime = time();
        curl_multi_close($mh);

        return $result;
    }
}