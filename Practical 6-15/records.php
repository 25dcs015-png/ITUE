<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
studenthub_require_login("admin");

$records = [];
$databaseError = "";

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
    $statement = $pdo->prepare(
        "SELECT student_code, created_at, name, email, mobile, course, year, gender
         FROM students
         WHERE status = 'active'
         ORDER BY created_at DESC, student_id DESC"
    );
    $statement->execute();
    $records = $statement->fetchAll();
} catch (Throwable $error) {
    error_log("StudentHub records page database error: " . $error->getMessage());
    $databaseError = "Student records could not be loaded. Check the MySQL connection and database setup.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Stored Registration Records</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="portal-subpage">

<table border="1" width="100%" cellpadding="10" cellspacing="0">
    <tr>
        <td align="left" width="70%">
            <h1>STUDENTHUB PORTAL</h1>
        </td>
        <td align="right" width="30%">
            <div class="top-nav-flex">
                <a href="signup.php" class="back-button"><b>+ NEW REGISTRATION</b></a>
                <span class="nav-sep">|</span>
                <a href="home.php" class="back-button"><b>HOME</b></a>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" bgcolor="#f0f0f0">
            <h2>DATABASE-STORED STUDENTS (MYSQL DATA)</h2>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="content-container-td">
            <div style="background: #ffffff; padding: 20px; border-radius: 10px; border: 1px solid #c8e6c9;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="color: #1b5e20;">Total Registered Students: <?php echo count($records); ?></h3>
                        <p style="color: #555; font-size: 13px; margin: 4px 0 0 0;">Source: MySQL <code>students</code> table</p>
                    </div>
                    <div>
                        <a href="signup.php" class="page-btn active" style="text-decoration: none;">+ Register Another Student</a>
                    </div>
                </div>

                <?php if ($databaseError !== ""): ?>
                    <div class="data-status-msg status-error" style="display: block;">
                        <?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php elseif (empty($records)): ?>
                    <div class="data-status-msg status-empty" style="display: block;">
                        No student records found yet. Submit the registration form to see records appear here.
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table border="1" width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; text-align: left; font-size: 13px;">
                            <thead>
                                <tr bgcolor="#e8f5e9" style="color: #1b5e20;">
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">STUDENT ID</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">CREATED</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">NAME</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">EMAIL</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">MOBILE</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">COURSE</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">YEAR</th>
                                    <th style="padding: 10px; border: 1px solid #c8e6c9;">GENDER</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $index => $row): ?>
                                    <tr style="background-color: <?php echo $index % 2 === 0 ? '#ffffff' : '#f9fdf9'; ?>;">
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><span class="tag-badge tag-technical"><?php echo htmlspecialchars($row["student_code"], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["created_at"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["email"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["mobile"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["course"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["year"], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($row["gender"], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>

<script src="script.js"></script>
</body>
</html>
