<?php
$connectionError = "";
$tableCounts = [];

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
    foreach (["students", "events", "registrations"] as $table) {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM `" . $table . "`");
        $statement->execute();
        $tableCounts[$table] = (int) $statement->fetchColumn();
    }
} catch (Throwable $error) {
    error_log("StudentHub database test error: " . $error->getMessage());
    $connectionError = "Database connection failed. Check MySQL is running and import studenthub.sql in phpMyAdmin.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Database Connection Test</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<br><br>
<center>
<table border="1" width="650" cellpadding="16" cellspacing="0">
    <tr>
        <td align="center" bgcolor="#f0f0f0">
            <h1>DATABASE CONNECTION TEST</h1>
        </td>
    </tr>
    <tr>
        <td align="center" class="content-container-td">
            <div class="form-box">
                <?php if ($connectionError === ""): ?>
                    <div style="background: #e8f5e9; border: 1.5px solid #a5d6a7; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <h3 style="color: #1b5e20; margin-bottom: 6px;">MySQL PDO Connection Successful</h3>
                        <p style="margin: 0; color: #2e7d32; font-size: 14px;">Connected to the StudentHub database and verified all required tables.</p>
                    </div>
                    <table border="1" width="100%" cellpadding="8" cellspacing="0" class="info-table">
                        <tr><th>TABLE</th><th>RECORDS</th></tr>
                        <?php foreach ($tableCounts as $table => $count): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $count; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <div style="background: #ffebee; border: 1.5px solid #ffcdd2; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <h3 style="color: #c62828; margin-bottom: 8px;">Database Connection Error</h3>
                        <p style="color: #b71c1c; font-size: 14px; margin: 0;"><?php echo htmlspecialchars($connectionError, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endif; ?>
                <a href="home.php" class="page-btn active" style="text-decoration: none; padding: 8px 20px;">BACK TO HOME</a>
            </div>
        </td>
    </tr>
</table>
</center>

<script src="script.js"></script>
</body>
</html>
