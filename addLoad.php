<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Core/Logger.php';
require_once 'Core/KafkaProducer.php';

$config_file = 'config_default.ini';
$config      = parse_ini_file($config_file, true);


$host    = 'mysql';
$db      = 'wordpress';
$user    = 'root';
$pass    = 'pass';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);


$producer = new KafkaProducer($config);

for ($i = 0; $i <= 7927; $i += 10) {

    $stmt = $pdo->query('SELECT ID, post_content FROM wp_posts LIMIT 10 OFFSET ' . $i);
    while ($row = $stmt->fetch()) {
        $producer->send('my-docs', json_encode(['id' => $row['ID'], 'text' => $row['post_content']]));
    }
}

