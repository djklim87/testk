<?php

class Sender{
    private $producersUrl = [];

    public function __construct($producersUrl = ['http://producer:80'])
    {
        $this->producersUrl = $producersUrl;
    }


    public function send($topic, $data){
        $multi = curl_multi_init();
        $channels = [];

        foreach ($this->producersUrl as $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS,
                http_build_query(['topic' => $topic, 'message' => $data]));


            curl_multi_add_handle($multi, $ch);

            $channels[$url] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) == -1) {
                continue;
            }

            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($channels as $channel) {

            $queryResult = curl_multi_getcontent($channel);
            Logger::log('Sender class: message sended. Producer answer: ');
            curl_multi_remove_handle($multi, $channel);
        }

        curl_multi_close($multi);
    }
}