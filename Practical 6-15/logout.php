<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !studenthub_valid_csrf(isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : null)) {
    http_response_code(403);
    exit("Invalid logout request.");
}

if (!empty($_SESSION["logged_in_user"]["id"])) {
    if (isset($_SESSION["logged_in_user"]["role"]) && $_SESSION["logged_in_user"]["role"] === "admin") {
        require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
        studenthub_log_admin_action($pdo, "logout", "account", (int) $_SESSION["logged_in_user"]["id"], "Admin signed out.");
    }
    studenthub_delete_remember_token((int) $_SESSION["logged_in_user"]["id"]);
}

studenthub_clear_remember_cookie();
studenthub_clear_session();
header("Location: login-form.php?logged_out=1");
exit;
