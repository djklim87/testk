<?php

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