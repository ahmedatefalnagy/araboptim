<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
$stmt = $pdo->query("SHOW CREATE TABLE `trips`");
echo $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] . "\n";
