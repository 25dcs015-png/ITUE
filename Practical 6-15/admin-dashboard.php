<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
studenthub_require_login("admin");
$user = $_SESSION["logged_in_user"];
$menuItems = studenthub_menu_items($user["role"]);
$dashboardError = "";
$stats = [
    "users" => 0,
    "students" => 0,
    "admins" => 0,
    "events" => 0,
    "registrations" => 0
];
$latestRegistrations = [];
$latestAuditLogs = [];

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
    $stats["users"] = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
    $stats["students"] = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE role = 'student' AND status = 'active'")->fetchColumn();
    $stats["admins"] = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE role = 'admin' AND status = 'active'")->fetchColumn();
    $stats["events"] = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status <> 'deleted'")->fetchColumn();
    $stats["registrations"] = (int) $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

    $registrationQuery = $pdo->query(
        "SELECT registrations.registered_at, students.name AS student_name, students.email, events.title AS event_title
         FROM registrations
         INNER JOIN students ON students.student_id = registrations.student_id
         INNER JOIN events ON events.event_id = registrations.event_id
         ORDER BY registrations.registered_at DESC, registrations.registration_id DESC
         LIMIT 10"
    );
    $latestRegistrations = $registrationQuery->fetchAll();
    $auditQuery = $pdo->query(
        "SELECT audit_logs.action, audit_logs.entity_type, audit_logs.entity_id, audit_logs.details,
                audit_logs.ip_address, audit_logs.created_at, students.name AS admin_name
         FROM audit_logs
         LEFT JOIN students ON students.student_id = audit_logs.admin_id
         ORDER BY audit_logs.created_at DESC, audit_logs.audit_id DESC
         LIMIT 10"
    );
    $latestAuditLogs = $auditQuery->fetchAll();
} catch (Throwable $error) {
    error_log("StudentHub admin dashboard error: " . $error->getMessage());
    $dashboardError = "Dashboard data could not be loaded. Check the database connection and apply the latest studenthub.sql schema.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Admin Dashboard</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="home-page">
<table border="1" width="100%" cellpadding="10" cellspacing="0">
    <tr>
        <td align="left" width="65%"><h1>STUDENTHUB ADMIN DASHBOARD</h1></td>
        <td align="right" width="35%">
            <div class="top-nav-flex">
                <span>ADMIN: <?php echo htmlspecialchars($user["name"], ENT_QUOTES, 'UTF-8'); ?></span>
                <span>LAST LOGIN: <?php echo htmlspecialchars($user["last_login_at"], ENT_QUOTES, 'UTF-8'); ?></span>
                <form action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="submit" class="page-btn active" value="LOGOUT">
                </form>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" bgcolor="#f0f0f0"><h2>ADMINISTRATION</h2></td>
    </tr>
    <tr>
        <td colspan="2" class="content-container-td">
            <div class="info-box">
                <?php if ($dashboardError !== ""): ?>
                    <div class="data-status-msg status-error" style="display: block;"><?php echo htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <h2>USER AND PORTAL STATISTICS</h2>
                    <div class="dashboard-stats">
                        <div class="dashboard-stat"><span>ACTIVE USERS</span><strong><?php echo $stats["users"]; ?></strong></div>
                        <div class="dashboard-stat"><span>ACTIVE STUDENTS</span><strong><?php echo $stats["students"]; ?></strong></div>
                        <div class="dashboard-stat"><span>ACTIVE ADMINS</span><strong><?php echo $stats["admins"]; ?></strong></div>
                        <div class="dashboard-stat"><span>EVENTS</span><strong><?php echo $stats["events"]; ?></strong></div>
                        <div class="dashboard-stat"><span>EVENT REGISTRATIONS</span><strong><?php echo $stats["registrations"]; ?></strong></div>
                    </div>

                    <h2>ADMIN MENU</h2>
                    <nav class="dashboard-menu" aria-label="Administrator menu">
                        <?php foreach ($menuItems as $menuItem): ?>
                            <a href="<?php echo htmlspecialchars($menuItem["url"], ENT_QUOTES, 'UTF-8'); ?>" class="page-btn active" style="text-decoration: none;"><?php echo htmlspecialchars($menuItem["label"], ENT_QUOTES, 'UTF-8'); ?></a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="dashboard-data-grid">
                        <section class="dashboard-section">
                            <h2>LATEST REGISTRATIONS</h2>
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table" border="1" width="100%" cellpadding="8" cellspacing="0">
                                    <thead><tr><th>STUDENT</th><th>EMAIL</th><th>EVENT</th><th>REGISTERED</th></tr></thead>
                                    <tbody>
                                    <?php if (!$latestRegistrations): ?>
                                        <tr><td colspan="4">No event registrations found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($latestRegistrations as $registration): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($registration["student_name"], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($registration["email"], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($registration["event_title"], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($registration["registered_at"], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="dashboard-section">
                            <h2>RECENT ADMIN AUDIT LOG</h2>
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table" border="1" width="100%" cellpadding="8" cellspacing="0">
                                    <thead><tr><th>ADMIN</th><th>ACTION</th><th>RECORD</th><th>DETAILS</th><th>IP ADDRESS</th><th>TIME</th></tr></thead>
                                    <tbody>
                                    <?php if (!$latestAuditLogs): ?>
                                        <tr><td colspan="6">No administrator actions recorded yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($latestAuditLogs as $auditLog): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($auditLog["admin_name"] ?? "Removed admin", ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(strtoupper($auditLog["action"]), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($auditLog["entity_type"]) . ($auditLog["entity_id"] === null ? "" : " #" . $auditLog["entity_id"]), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($auditLog["details"], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($auditLog["ip_address"] ?? "—", ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($auditLog["created_at"], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<script src="script.js"></script>
</body>
</html>
