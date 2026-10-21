<?php
$dbHost = getenv("STUDENTHUB_DB_HOST") ?: "127.0.0.1";
$dbName = getenv("STUDENTHUB_DB_NAME") ?: "studenthub";
$dbUser = getenv("STUDENTHUB_DB_USER") ?: "root";
$dbPassword = getenv("STUDENTHUB_DB_PASSWORD") ?: "";

$dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4";

$pdo = new PDO($dsn, $dbUser, $dbPassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
