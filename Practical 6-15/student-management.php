<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
studenthub_require_login("admin");

$message = "";
$messageType = "status-empty";
$search = isset($_GET["search"]) && is_string($_GET["search"]) ? trim($_GET["search"]) : "";
$statusFilter = isset($_GET["status"]) && is_string($_GET["status"]) ? $_GET["status"] : "active";
if (!in_array($statusFilter, ["active", "deleted", "all"], true)) {
    $statusFilter = "active";
}
$page = isset($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$pageSize = 10;
$validCourses = [
    "Computer Science and Engineering",
    "Information Technology",
    "Electronics and Communication",
    "Mechanical Engineering",
    "Civil Engineering"
];
$validYears = ["1st Year", "2nd Year", "3rd Year", "4th Year"];
$validGenders = ["Male", "Female", "Other"];

function student_management_redirect($search, $status)
{
    $params = ["status" => $status];
    if ($search !== "") {
        $params["search"] = $search;
    }
    header("Location: student-management.php?" . http_build_query($params));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedSearch = isset($_POST["search"]) && is_string($_POST["search"]) ? trim($_POST["search"]) : "";
    $postedStatus = isset($_POST["status"]) && is_string($_POST["status"]) ? $_POST["status"] : "active";
    if (!in_array($postedStatus, ["active", "deleted", "all"], true)) {
        $postedStatus = "active";
    }

    if (!studenthub_valid_csrf(isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : null)) {
        $message = "Your form session has expired or is invalid. Please reload and try again.";
        $messageType = "status-error";
    } else {
        $action = isset($_POST["action"]) && is_string($_POST["action"]) ? $_POST["action"] : "";
        $studentId = isset($_POST["student_id"]) ? (int) $_POST["student_id"] : 0;
        try {
            require __DIR__ . DIRECTORY_SEPARATOR . "db-mysqli.php";
            if ($action === "save") {
                $name = isset($_POST["name"]) && is_string($_POST["name"]) ? trim($_POST["name"]) : "";
                $email = isset($_POST["email"]) && is_string($_POST["email"]) ? strtolower(trim($_POST["email"])) : "";
                $mobile = isset($_POST["mobile"]) && is_string($_POST["mobile"]) ? trim($_POST["mobile"]) : "";
                $course = isset($_POST["course"]) && is_string($_POST["course"]) ? $_POST["course"] : "";
                $year = isset($_POST["year"]) && is_string($_POST["year"]) ? $_POST["year"] : "";
                $gender = isset($_POST["gender"]) && is_string($_POST["gender"]) ? $_POST["gender"] : "";
                $gpaInput = isset($_POST["gpa"]) && is_string($_POST["gpa"]) ? trim($_POST["gpa"]) : "";
                $gpa = $gpaInput === "" ? null : filter_var($gpaInput, FILTER_VALIDATE_FLOAT);

                if (!preg_match("/^[A-Za-z\s]{2,50}$/", $name)) {
                    $message = "Name must contain 2 to 50 letters and spaces.";
                } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $message = "Enter a valid email address.";
                } elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
                    $message = "Mobile number must be a valid 10-digit number.";
                } elseif (!in_array($course, $validCourses, true) || !in_array($year, $validYears, true) || !in_array($gender, $validGenders, true)) {
                    $message = "Select valid course, year, and gender values.";
                } elseif ($gpaInput !== "" && ($gpa === false || $gpa < 0 || $gpa > 10)) {
                    $message = "GPA must be between 0 and 10.";
                } else {
                    $duplicate = $mysqli->prepare("SELECT student_id FROM students WHERE email = ? AND student_id <> ? LIMIT 1");
                    $duplicate->bind_param("si", $email, $studentId);
                    $duplicate->execute();
                    if ($duplicate->get_result()->num_rows > 0) {
                        $message = "That email address is already in use.";
                    } elseif ($studentId > 0) {
                        $statement = $mysqli->prepare(
                            "UPDATE students
                             SET name = ?, email = ?, mobile = ?, course = ?, year = ?, gender = ?, gpa = ?
                             WHERE student_id = ? AND status = 'active'"
                        );
                        $statement->bind_param("ssssssdi", $name, $email, $mobile, $course, $year, $gender, $gpa, $studentId);
                        $statement->execute();
                        if ($statement->affected_rows > 0) {
                            studenthub_log_admin_action($mysqli, "updated", "student", $studentId, "Updated student " . $email . ".");
                        }
                        $message = $statement->affected_rows > 0 ? "Student record updated successfully." : "No student record was changed.";
                        $messageType = $statement->affected_rows > 0 ? "status-success" : "status-empty";
                    } else {
                        $studentCode = "ADM" . date("ymd") . strtoupper(bin2hex(random_bytes(3)));
                        $emptyPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
                        $statement = $mysqli->prepare(
                            "INSERT INTO students
                                (student_code, name, email, mobile, password_hash, course, year, gender, gpa)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        $statement->bind_param("ssssssssd", $studentCode, $name, $email, $mobile, $emptyPasswordHash, $course, $year, $gender, $gpa);
                        $statement->execute();
                        studenthub_log_admin_action($mysqli, "created", "student", (int) $mysqli->insert_id, "Created student " . $email . ".");
                        $message = "Student record added successfully.";
                        $messageType = "status-success";
                    }
                }
            } elseif ($action === "delete" || $action === "restore") {
                if ($studentId < 1) {
                    $message = "Select a valid student record.";
                } else {
                    $newStatus = $action === "delete" ? "deleted" : "active";
                    $currentAdminId = (int) $_SESSION["logged_in_user"]["id"];
                    $target = $mysqli->prepare("SELECT student_id, role FROM students WHERE student_id = ? LIMIT 1");
                    $target->bind_param("i", $studentId);
                    $target->execute();
                    $targetStudent = $target->get_result()->fetch_assoc();
                    if (!$targetStudent) {
                        $message = "Student record was not found.";
                    } elseif ($action === "delete" && ($studentId === $currentAdminId || $targetStudent["role"] === "admin")) {
                        $message = "Admin accounts cannot be deleted from student management.";
                        $messageType = "status-error";
                    } else {
                        $statement = $mysqli->prepare("UPDATE students SET status = ? WHERE student_id = ? AND status <> ?");
                        $statement->bind_param("sis", $newStatus, $studentId, $newStatus);
                        $statement->execute();
                        if ($statement->affected_rows > 0) {
                            studenthub_log_admin_action($mysqli, $action === "delete" ? "deleted" : "restored", "student", $studentId, ucfirst($action) . " student record.");
                        }
                        $message = $statement->affected_rows > 0
                            ? ($action === "delete" ? "Student record moved to deleted records." : "Student record restored successfully.")
                            : "No student record was changed.";
                        $messageType = $statement->affected_rows > 0 ? "status-success" : "status-empty";
                    }
                }
            } else {
                $message = "Unknown student management action.";
            }
        } catch (Throwable $error) {
            error_log("StudentHub student management error: " . $error->getMessage());
            $message = "The student record could not be saved. Please check the database and try again.";
            $messageType = "status-error";
        }
    }
    $search = $postedSearch;
    $statusFilter = $postedStatus;
} elseif (isset($_GET["result"])) {
    $results = [
        "added" => ["Student record added successfully.", "status-success"],
        "updated" => ["Student record updated successfully.", "status-success"],
        "deleted" => ["Student record moved to deleted records.", "status-success"],
        "restored" => ["Student record restored successfully.", "status-success"]
    ];
    if (isset($results[$_GET["result"]])) {
        $message = $results[$_GET["result"]][0];
        $messageType = $results[$_GET["result"]][1];
    }
}

$searchPattern = "%" . $search . "%";
$statusCondition = $statusFilter === "all" ? "" : " AND status = ?";
$total = 0;
$students = [];
$editStudent = null;
$databaseError = "";

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db-mysqli.php";
    if ($statusFilter === "all") {
        $count = $mysqli->prepare("SELECT COUNT(*) AS total FROM students WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)");
        $count->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
    } else {
        $count = $mysqli->prepare("SELECT COUNT(*) AS total FROM students WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?) AND status = ?");
        $count->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $statusFilter);
    }
    $count->execute();
    $total = (int) $count->get_result()->fetch_assoc()["total"];
    $totalPages = max(1, (int) ceil($total / $pageSize));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $pageSize;

    if ($statusFilter === "all") {
        $statement = $mysqli->prepare(
            "SELECT student_id, student_code, name, email, mobile, course, year, gender, gpa, role, status
             FROM students
             WHERE name LIKE ? OR email LIKE ? OR course LIKE ?
             ORDER BY student_id DESC
             LIMIT ? OFFSET ?"
        );
        $statement->bind_param("sssii", $searchPattern, $searchPattern, $searchPattern, $pageSize, $offset);
    } else {
        $statement = $mysqli->prepare(
            "SELECT student_id, student_code, name, email, mobile, course, year, gender, gpa, role, status
             FROM students
             WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?) AND status = ?
             ORDER BY student_id DESC
             LIMIT ? OFFSET ?"
        );
        $statement->bind_param("ssssii", $searchPattern, $searchPattern, $searchPattern, $statusFilter, $pageSize, $offset);
    }
    $statement->execute();
    $students = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

    if (isset($_GET["edit"])) {
        $editId = (int) $_GET["edit"];
        $edit = $mysqli->prepare("SELECT student_id, name, email, mobile, course, year, gender, gpa FROM students WHERE student_id = ? AND status = 'active' LIMIT 1");
        $edit->bind_param("i", $editId);
        $edit->execute();
        $editStudent = $edit->get_result()->fetch_assoc() ?: null;
    }
} catch (Throwable $error) {
    error_log("StudentHub student list error: " . $error->getMessage());
    $databaseError = "Student records could not be loaded. Apply the latest studenthub.sql schema and check the MySQL connection.";
    $totalPages = 1;
}

if (isset($_GET["export"]) && $_GET["export"] === "csv" && $databaseError === "") {
    $exportSql = "SELECT student_code, name, email, mobile, course, year, gender, gpa, status FROM students WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)";
    if ($statusFilter !== "all") {
        $exportSql .= " AND status = ?";
    }
    $exportSql .= " ORDER BY student_id";
    $export = $mysqli->prepare($exportSql);
    if ($statusFilter === "all") {
        $export->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
    } else {
        $export->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $statusFilter);
    }
    $export->execute();
    $rows = $export->get_result();
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=studenthub-students.csv");
    $output = fopen("php://output", "w");
    fputcsv($output, ["Student ID", "Name", "Email", "Mobile", "Course", "Year", "Gender", "GPA", "Status"]);
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, array_values($row));
    }
    fclose($output);
    exit;
}

$pageParams = ["status" => $statusFilter];
if ($search !== "") {
    $pageParams["search"] = $search;
}
$exportParams = $pageParams;
$exportParams["export"] = "csv";
$adminName = $_SESSION["logged_in_user"]["name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Student Management</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="portal-subpage">
<table border="1" width="100%" cellpadding="10" cellspacing="0">
    <tr>
        <td align="left" width="60%"><h1>STUDENT MANAGEMENT</h1></td>
        <td align="right" width="40%">
            <div class="top-nav-flex">
                <span>ADMIN: <?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="admin-dashboard.php" class="back-button"><b>ADMIN HOME</b></a>
                <form action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="back-button"><b>LOGOUT</b></button>
                </form>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" bgcolor="#f0f0f0"><h2>ADD, SEARCH, EDIT AND MANAGE STUDENT RECORDS</h2></td>
    </tr>
    <tr>
        <td colspan="2" class="content-container-td">
            <div class="info-box">
                <div id="student-management-message" class="data-status-msg" role="status" aria-live="polite"></div>
                <?php if ($databaseError !== ""): ?>
                    <div class="data-status-msg status-error" style="display: block;"><?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <h2><?php echo $editStudent ? "EDIT STUDENT" : "ADD STUDENT"; ?></h2>
                    <form id="student-management-form" method="post" action="student-management.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="student_id" value="<?php echo $editStudent ? (int) $editStudent["student_id"] : 0; ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8'); ?>">
                        <table class="info-table" width="100%" cellpadding="8" cellspacing="0">
                            <tr><td><b>NAME</b></td><td><input name="name" maxlength="50" required value="<?php echo htmlspecialchars($editStudent["name"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td><b>EMAIL</b></td><td><input type="email" name="email" maxlength="254" required value="<?php echo htmlspecialchars($editStudent["email"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td></tr>
                            <tr><td><b>MOBILE</b></td><td><input name="mobile" maxlength="10" required value="<?php echo htmlspecialchars($editStudent["mobile"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td><b>COURSE</b></td><td><select name="course" required><option value="">Select Course</option><?php foreach ($validCourses as $courseOption): ?><option value="<?php echo htmlspecialchars($courseOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editStudent["course"]) && $editStudent["course"] === $courseOption ? "selected" : ""; ?>><?php echo htmlspecialchars($courseOption, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td></tr>
                            <tr><td><b>YEAR</b></td><td><select name="year" required><option value="">Select Year</option><?php foreach ($validYears as $yearOption): ?><option value="<?php echo htmlspecialchars($yearOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editStudent["year"]) && $editStudent["year"] === $yearOption ? "selected" : ""; ?>><?php echo htmlspecialchars($yearOption, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                <td><b>GENDER</b></td><td><select name="gender" required><option value="">Select Gender</option><?php foreach ($validGenders as $genderOption): ?><option value="<?php echo htmlspecialchars($genderOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editStudent["gender"]) && $editStudent["gender"] === $genderOption ? "selected" : ""; ?>><?php echo htmlspecialchars($genderOption, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td></tr>
                            <tr><td><b>GPA</b></td><td><input type="number" name="gpa" min="0" max="10" step="0.01" value="<?php echo htmlspecialchars((string) ($editStudent["gpa"] ?? ""), ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td colspan="2"><button type="submit" class="page-btn active"><?php echo $editStudent ? "UPDATE STUDENT" : "ADD STUDENT"; ?></button> <?php if ($editStudent): ?><a class="page-btn" href="student-management.php">CANCEL</a><?php endif; ?></td></tr>
                        </table>
                    </form>
                    <br>
                    <h2>STUDENT DIRECTORY</h2>
                    <form id="student-management-filter" method="get" action="student-management.php" class="data-controls-bar">
                        <div class="control-group search-group"><label for="student-management-search"><b>SEARCH:</b></label><input id="student-management-search" type="search" name="search" placeholder="Name, email, or course" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"></div>
                        <div class="control-group"><label for="student-management-status"><b>STATUS:</b></label><select id="student-management-status" name="status"><option value="active" <?php echo $statusFilter === "active" ? "selected" : ""; ?>>Active</option><option value="deleted" <?php echo $statusFilter === "deleted" ? "selected" : ""; ?>>Deleted</option><option value="all" <?php echo $statusFilter === "all" ? "selected" : ""; ?>>All</option></select></div>
                        <button type="submit" class="page-btn active">SEARCH</button>
                        <a class="page-btn" href="student-management.php">CLEAR</a>
                        <a class="page-btn" href="student-management.php?<?php echo htmlspecialchars(http_build_query($exportParams), ENT_QUOTES, 'UTF-8'); ?>">EXPORT CSV</a>
                    </form>
                    <p id="student-management-pagination-info">Loading student records...</p>
                    <div id="student-management-list-status" class="data-status-msg status-loading" role="status" aria-live="polite" style="display: block;">Loading student records...</div>
                    <div style="overflow-x: auto;">
                            <table id="student-management-table" border="1" width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; text-align: left; font-size: 13px;">
                                <thead><tr bgcolor="#e8f5e9" style="color: #1b5e20;"><th>ID</th><th>NAME</th><th>EMAIL</th><th>MOBILE</th><th>COURSE</th><th>YEAR</th><th>GENDER</th><th>GPA</th><th>ROLE</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                                <tbody id="student-management-table-body"></tbody>
                            </table>
                        </div>
                    <div class="pagination-wrapper" style="display: flex;">
                        <div class="pagination-info" id="student-management-page-info"></div>
                        <div class="pagination-controls" id="student-management-pagination"></div>
                    </div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<script src="script.js"></script>
<script src="student-management.js"></script>
</body>
</html>
