<?php
const STUDENTHUB_SESSION_TIMEOUT = 1200;
const STUDENTHUB_REMEMBER_COOKIE = "studenthub_remember";
const STUDENTHUB_REMEMBER_DAYS = 30;

$cookieSecure = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
session_name("StudentHubSession");
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => $cookieSecure,
    "httponly" => true,
    "samesite" => "Lax"
]);
session_start();

function studenthub_set_remember_cookie($value, $expires)
{
    global $cookieSecure;
    setcookie(STUDENTHUB_REMEMBER_COOKIE, $value, [
        "expires" => $expires,
        "path" => "/",
        "secure" => $cookieSecure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function studenthub_clear_remember_cookie()
{
    studenthub_set_remember_cookie("", time() - 3600);
}

function studenthub_csrf_token()
{
    if (empty($_SESSION["auth_csrf_token"])) {
        $_SESSION["auth_csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["auth_csrf_token"];
}

function studenthub_valid_csrf($submittedToken)
{
    return is_string($submittedToken)
        && !empty($_SESSION["auth_csrf_token"])
        && hash_equals($_SESSION["auth_csrf_token"], $submittedToken);
}

function studenthub_dashboard_for_role($role)
{
    return $role === "admin" ? "admin-dashboard.php" : "home.php";
}

function studenthub_menu_items($role)
{
    if ($role === "admin") {
        return [
            ["label" => "ADMIN DASHBOARD", "url" => "admin-dashboard.php"],
            ["label" => "MANAGE STUDENTS", "url" => "student-management.php"],
            ["label" => "MANAGE EVENTS", "url" => "event-management.php"],
            ["label" => "VIEW STUDENT RECORDS", "url" => "records.php"],
            ["label" => "DATABASE STATUS", "url" => "database-test.php"]
        ];
    }

    return [
        ["label" => "PROFILE", "url" => "profile.php"],
        ["label" => "TIME TABLE", "url" => "timetable.html"],
        ["label" => "COURSES", "url" => "courses.html"],
        ["label" => "EVENTS", "url" => "events.html"],
        ["label" => "DATABASE CONNECTION TEST", "url" => "database-test.php"],
        ["label" => "PLACEMENT RECORDS", "url" => "placement.html"],
        ["label" => "SCHOLARSHIP DETAILS", "url" => "scholarship.html"],
        ["label" => "WORKSHOP DETAILS", "url" => "workshop.html"]
    ];
}

function studenthub_log_admin_action($connection, $action, $entityType, $entityId, $details)
{
    $adminId = isset($_SESSION["logged_in_user"]["id"]) ? (int) $_SESSION["logged_in_user"]["id"] : null;
    $ipAddress = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;
    $details = substr((string) $details, 0, 500);

    if ($connection instanceof PDO) {
        $statement = $connection->prepare(
            "INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address)
             VALUES (:admin_id, :action, :entity_type, :entity_id, :details, :ip_address)"
        );
        $statement->execute([
            "admin_id" => $adminId,
            "action" => $action,
            "entity_type" => $entityType,
            "entity_id" => $entityId,
            "details" => $details,
            "ip_address" => $ipAddress
        ]);
        return;
    }

    if ($connection instanceof mysqli) {
        $statement = $connection->prepare(
            "INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $statement->bind_param("ississ", $adminId, $action, $entityType, $entityId, $details, $ipAddress);
        $statement->execute();
        return;
    }

    throw new InvalidArgumentException("Unsupported database connection for audit logging.");
}

function studenthub_clear_session()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => "Lax"
        ]);
    }
    session_destroy();
}

function studenthub_delete_remember_token($studentId)
{
    try {
        require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
        $statement = $pdo->prepare(
            "UPDATE students
             SET remember_token_hash = NULL, remember_token_expires_at = NULL
             WHERE student_id = :student_id"
        );
        $statement->execute(["student_id" => $studentId]);
    } catch (Throwable $error) {
        error_log("StudentHub remember-token cleanup error: " . $error->getMessage());
    }
}

$sessionExpired = false;
if (isset($_SESSION["logged_in_user"])) {
    $lastActivity = isset($_SESSION["last_activity"]) ? (int) $_SESSION["last_activity"] : 0;
    if ($lastActivity === 0 || time() - $lastActivity > STUDENTHUB_SESSION_TIMEOUT) {
        $expiredStudentId = isset($_SESSION["logged_in_user"]["id"]) ? (int) $_SESSION["logged_in_user"]["id"] : 0;
        if ($expiredStudentId > 0) {
            studenthub_delete_remember_token($expiredStudentId);
        }
        studenthub_clear_remember_cookie();
        studenthub_clear_session();
        session_start();
        $sessionExpired = true;
    } else {
        $_SESSION["last_activity"] = time();
    }
}

if (!isset($_SESSION["logged_in_user"]) && !$sessionExpired && !empty($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) {
    $rememberToken = $_COOKIE[STUDENTHUB_REMEMBER_COOKIE];
    if (preg_match("/^[a-f0-9]{64}$/", $rememberToken)) {
        try {
            require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
            $statement = $pdo->prepare(
                "SELECT student_id, student_code, name, email, mobile, course, year, gender, role, last_login_at
                 FROM students
                 WHERE remember_token_hash = :token_hash
                   AND remember_token_expires_at > CURRENT_TIMESTAMP
                   AND status = 'active'
                 LIMIT 1"
            );
            $statement->execute(["token_hash" => hash("sha256", $rememberToken)]);
            $student = $statement->fetch();
            if ($student) {
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
                    "last_login_at" => $student["last_login_at"]
                ];
                $_SESSION["last_activity"] = time();
            } else {
                studenthub_clear_remember_cookie();
            }
        } catch (Throwable $error) {
            error_log("StudentHub remember-token lookup error: " . $error->getMessage());
        }
    } else {
        studenthub_clear_remember_cookie();
    }
}

function studenthub_require_login($requiredRole = null)
{
    if (empty($_SESSION["logged_in_user"])) {
        header("Location: login-form.php");
        exit;
    }

    $userRole = isset($_SESSION["logged_in_user"]["role"]) ? $_SESSION["logged_in_user"]["role"] : "student";
    if ($requiredRole !== null && $userRole !== $requiredRole) {
        header("Location: " . studenthub_dashboard_for_role($userRole));
        exit;
    }
}
