<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";

if (!empty($_SESSION["logged_in_user"])) {
    header("Location: " . studenthub_dashboard_for_role($_SESSION["logged_in_user"]["role"]));
    exit;
}

$loginMessage = isset($_GET["registered"]) ? "Registration successful. Please log in with your new account." : "";
if (isset($_GET["logged_out"])) {
    $loginMessage = "You have been logged out successfully.";
}
if ($sessionExpired) {
    $loginMessage = "Your session expired. Please log in again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Login</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<br><br><br>
<center>
<form id="login-form" action="login.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <table border="1" width="600" cellpadding="16" cellspacing="0">
        <tr>
            <td align="center" bgcolor="#f0f0f0">
                <h1>LOGIN</h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="content-container-td">
                <div class="form-box">
                    <?php if ($loginMessage !== ""): ?>
                        <div style="background: #e8f5e9; border: 1.5px solid #a5d6a7; border-radius: 8px; padding: 14px; margin-bottom: 16px; color: #1b5e20;">
                            <?php echo htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <table border="0" width="100%" class="form-table" cellpadding="10" cellspacing="8">
                        <tr>
                            <td align="right" width="35%"><b>EMAIL:</b></td>
                            <td align="left" width="65%">
                                <input type="email" id="login-email" name="email" size="35" maxlength="254" required placeholder="Enter your email">
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><b>PASSWORD:</b></td>
                            <td align="left">
                                <input type="password" id="login-password" name="password" size="35" required placeholder="Enter your password">
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align="left">
                                <label><input type="checkbox" name="remember_me" value="1"> Remember me for 30 days</label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <div class="form-submit-container">
                                    <input type="submit" value="  LOGIN  ">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td align="center">
                <div class="bottom-link-flex">
                    <a href="signup.php"><h3>DON'T HAVE AN ACCOUNT? STUDENT REGISTRATION</h3></a>
                </div>
            </td>
        </tr>
    </table>
</form>
</center>

<script src="script.js"></script>
</body>
</html>
