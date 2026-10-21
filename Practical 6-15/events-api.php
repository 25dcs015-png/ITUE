<?php
header("Content-Type: application/json; charset=utf-8");

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
    $statement = $pdo->prepare("CALL get_events_with_registration_counts()");
    $statement->execute();
    echo json_encode($statement->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log("StudentHub events API error: " . $error->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Events could not be loaded from the database."]);
}
