<?php
$mysqliHost = getenv("STUDENTHUB_DB_HOST") ?: "127.0.0.1";
$mysqliName = getenv("STUDENTHUB_DB_NAME") ?: "studenthub";
$mysqliUser = getenv("STUDENTHUB_DB_USER") ?: "root";
$mysqliPassword = getenv("STUDENTHUB_DB_PASSWORD") ?: "";

$mysqli = new mysqli($mysqliHost, $mysqliUser, $mysqliPassword, $mysqliName);
$mysqli->set_charset("utf8mb4");
