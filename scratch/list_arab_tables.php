<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo_arab = new PDO("mysql:host=$host;dbname=arab_db;charset=utf8mb4", $user, $pass);
$stmt = $pdo_arab->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
