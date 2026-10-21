<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
if (!empty($_SESSION["logged_in_user"])) {
    header("Location: " . studenthub_dashboard_for_role($_SESSION["logged_in_user"]["role"]));
} else {
    header("Location: login-form.php");
}
exit;
?>
