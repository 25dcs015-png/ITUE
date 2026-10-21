<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login-form.php");
    exit;
}

$error = "";
$email = isset($_POST["email"]) && is_string($_POST["email"]) ? strtolower(trim($_POST["email"])) : "";
$password = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
$rememberMe = isset($_POST["remember_me"]) && $_POST["remember_me"] === "1";

if (!studenthub_valid_csrf(isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : null)) {
    $error = "Your login form session has expired or is invalid. Please reload and try again.";
} elseif ($email === "" || $password === "") {
    $error = "Please enter both email and password.";
} else {
    try {
        require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
        $statement = $pdo->prepare(
            "SELECT student_id, student_code, name, email, mobile, password_hash, course, year, gender, role
             FROM students
             WHERE email = :email AND status = 'active'
             LIMIT 1"
        );
        $statement->execute(["email" => $email]);
        $student = $statement->fetch();

        if (!$student || empty($student["password_hash"]) || !password_verify($password, $student["password_hash"])) {
            $error = "Invalid email or password.";
        } else {
            $lastLogin = $pdo->prepare("UPDATE students SET last_login_at = CURRENT_TIMESTAMP WHERE student_id = :student_id");
            $lastLogin->execute(["student_id" => $student["student_id"]]);

            if ($rememberMe) {
                $rememberToken = bin2hex(random_bytes(32));
                $expiresAt = date("Y-m-d H:i:s", time() + STUDENTHUB_REMEMBER_DAYS * 86400);
                $saveToken = $pdo->prepare(
                    "UPDATE students
                     SET remember_token_hash = :token_hash, remember_token_expires_at = :expires_at
                     WHERE student_id = :student_id"
                );
                $saveToken->execute([
                    "token_hash" => hash("sha256", $rememberToken),
                    "expires_at" => $expiresAt,
                    "student_id" => $student["student_id"]
                ]);
                studenthub_set_remember_cookie($rememberToken, time() + STUDENTHUB_REMEMBER_DAYS * 86400);
            } else {
                $clearToken = $pdo->prepare(
                    "UPDATE students
                     SET remember_token_hash = NULL, remember_token_expires_at = NULL
                     WHERE student_id = :student_id"
                );
                $clearToken->execute(["student_id" => $student["student_id"]]);
                studenthub_clear_remember_cookie();
            }

            session_regenerate_id(true);
            $_SESSION["logged_in_user"] = [
                "id" => (int) $student["student_id"],
                "student_code" => $student["student_code"],
                "name" => $student["name"],
                "email" => $student["email"],
                "mobile" => $student["mobile"],
                "course" => $student["course"],
                "year" => $student["year"],
                "gender" => $student["gender"],
                "role" => $student["role"],
                "last_login_at" => date("Y-m-d H:i:s")
            ];
            $_SESSION["last_activity"] = time();
            if ($student["role"] === "admin") {
                studenthub_log_admin_action($pdo, "login", "account", (int) $student["student_id"], "Admin signed in.");
            }
            unset($_SESSION["auth_csrf_token"]);

            header("Location: " . studenthub_dashboard_for_role($student["role"]));
            exit;
        }
    } catch (Throwable $databaseError) {
        error_log("StudentHub login error: " . $databaseError->getMessage());
        $error = "Login is temporarily unavailable because the database could not be reached.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Login Failed</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<br><br><br>
<center>
<table border="1" width="600" cellpadding="16" cellspacing="0">
    <tr>
        <td align="center" bgcolor="#f0f0f0"><h1>LOGIN FAILED</h1></td>
    </tr>
    <tr>
        <td align="center" class="content-container-td">
            <div class="form-box">
                <div style="background: #ffebee; border: 1.5px solid #ffcdd2; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <h3 style="color: #c62828; margin-bottom: 8px;">Authentication Error</h3>
                    <p style="color: #b71c1c; font-size: 14px; margin: 0;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a href="login-form.php" class="page-btn active" style="text-decoration: none; padding: 8px 18px;">« TRY LOGIN AGAIN</a>
                <a href="signup.php" class="page-btn" style="text-decoration: none; padding: 8px 18px;">CREATE ACCOUNT</a>
            </div>
        </td>
    </tr>
</table>
</center>
<script src="script.js"></script>
</body>
</html>
