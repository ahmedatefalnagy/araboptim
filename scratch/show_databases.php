<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
$stmt = $pdo->query("SHOW DATABASES");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
