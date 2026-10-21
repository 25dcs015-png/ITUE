<?php
header("Content-Type: application/json; charset=utf-8");

function student_api_response($statusCode, $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";

    $adminApi = isset($_GET["admin"]) && $_GET["admin"] === "1";
    if ($adminApi && (empty($_SESSION["logged_in_user"]) || $_SESSION["logged_in_user"]["role"] !== "admin")) {
        student_api_response(401, ["ok" => false, "error" => "Administrator login is required."]);
    }

    require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET") {
        if (!$adminApi) {
            $statement = $pdo->prepare(
                "SELECT student_code AS id, name, course, year, email, mobile, gender, gpa
                 FROM students
                 WHERE status = 'active'
                 ORDER BY student_code"
            );
            $statement->execute();
            echo json_encode($statement->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $search = isset($_GET["search"]) && is_string($_GET["search"]) ? trim($_GET["search"]) : "";
        $status = isset($_GET["status"]) && is_string($_GET["status"]) ? $_GET["status"] : "active";
        if (!in_array($status, ["active", "deleted", "all"], true)) {
            student_api_response(400, ["ok" => false, "error" => "Invalid student status filter."]);
        }
        $page = isset($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
        $pageSize = 10;
        $pattern = "%" . $search . "%";
        $where = "(name LIKE :name OR email LIKE :email OR course LIKE :course)";
        $params = [":name" => $pattern, ":email" => $pattern, ":course" => $pattern];
        if ($status !== "all") {
            $where .= " AND status = :status";
            $params[":status"] = $status;
        }
        $count = $pdo->prepare("SELECT COUNT(*) FROM students WHERE " . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $pageSize));
        $page = min($page, $totalPages);
        $statement = $pdo->prepare(
            "SELECT student_id, student_code, name, email, mobile, course, year, gender, gpa, role, status
             FROM students
             WHERE " . $where . "
             ORDER BY student_id DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(":limit", $pageSize, PDO::PARAM_INT);
        $statement->bindValue(":offset", ($page - 1) * $pageSize, PDO::PARAM_INT);
        $statement->execute();
        student_api_response(200, [
            "ok" => true,
            "data" => $statement->fetchAll(),
            "meta" => ["page" => $page, "page_size" => $pageSize, "total" => $total, "total_pages" => $totalPages]
        ]);
    }

    if (!in_array($method, ["POST", "PUT", "DELETE"], true)) {
        header("Allow: GET, POST, PUT, DELETE");
        student_api_response(405, ["ok" => false, "error" => "This HTTP method is not supported."]);
    }

    if (empty($_SESSION["logged_in_user"]) || $_SESSION["logged_in_user"]["role"] !== "admin") {
        student_api_response(401, ["ok" => false, "error" => "Administrator login is required."]);
    }
    $csrfToken = isset($_SERVER["HTTP_X_CSRF_TOKEN"]) ? $_SERVER["HTTP_X_CSRF_TOKEN"] : "";
    if (!studenthub_valid_csrf($csrfToken)) {
        student_api_response(403, ["ok" => false, "error" => "The request token is invalid or expired."]);
    }

    $rawBody = file_get_contents("php://input");
    $input = json_decode($rawBody, true);
    if (!is_array($input)) {
        student_api_response(400, ["ok" => false, "error" => "Request body must contain valid JSON."]);
    }

    if ($method === "POST" || $method === "PUT") {
        $studentId = isset($input["student_id"]) ? (int) $input["student_id"] : 0;
        $name = isset($input["name"]) && is_string($input["name"]) ? trim($input["name"]) : "";
        $email = isset($input["email"]) && is_string($input["email"]) ? strtolower(trim($input["email"])) : "";
        $mobile = isset($input["mobile"]) && is_string($input["mobile"]) ? trim($input["mobile"]) : "";
        $course = isset($input["course"]) && is_string($input["course"]) ? $input["course"] : "";
        $year = isset($input["year"]) && is_string($input["year"]) ? $input["year"] : "";
        $gender = isset($input["gender"]) && is_string($input["gender"]) ? $input["gender"] : "";
        $gpaInput = isset($input["gpa"]) && is_string($input["gpa"]) ? trim($input["gpa"]) : "";
        $gpa = $gpaInput === "" ? null : filter_var($gpaInput, FILTER_VALIDATE_FLOAT);
        $courses = [
            "Computer Science and Engineering",
            "Information Technology",
            "Electronics and Communication",
            "Mechanical Engineering",
            "Civil Engineering"
        ];
        $years = ["1st Year", "2nd Year", "3rd Year", "4th Year"];
        $genders = ["Male", "Female", "Other"];

        if (!preg_match("/^[A-Za-z\s]{2,50}$/", $name)) {
            student_api_response(422, ["ok" => false, "error" => "Name must contain 2 to 50 letters and spaces."]);
        }
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            student_api_response(422, ["ok" => false, "error" => "Enter a valid email address."]);
        }
        if (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
            student_api_response(422, ["ok" => false, "error" => "Mobile number must be a valid 10-digit number."]);
        }
        if (!in_array($course, $courses, true) || !in_array($year, $years, true) || !in_array($gender, $genders, true)) {
            student_api_response(422, ["ok" => false, "error" => "Select valid course, year, and gender values."]);
        }
        if ($gpaInput !== "" && ($gpa === false || $gpa < 0 || $gpa > 10)) {
            student_api_response(422, ["ok" => false, "error" => "GPA must be between 0 and 10."]);
        }
        if (($method === "POST" && $studentId !== 0) || ($method === "PUT" && $studentId < 1)) {
            student_api_response(400, ["ok" => false, "error" => "The student ID does not match this operation."]);
        }

        $duplicate = $pdo->prepare("SELECT student_id FROM students WHERE email = :email AND student_id <> :student_id LIMIT 1");
        $duplicate->execute([":email" => $email, ":student_id" => $studentId]);
        if ($duplicate->fetch()) {
            student_api_response(409, ["ok" => false, "error" => "That email address is already in use."]);
        }

        if ($method === "POST") {
            $studentCode = "ADM" . date("ymd") . strtoupper(bin2hex(random_bytes(3)));
            $emptyPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
            $statement = $pdo->prepare(
                "INSERT INTO students
                    (student_code, name, email, mobile, password_hash, course, year, gender, gpa)
                 VALUES
                    (:student_code, :name, :email, :mobile, :password_hash, :course, :year, :gender, :gpa)"
            );
            $statement->execute([
                ":student_code" => $studentCode,
                ":name" => $name,
                ":email" => $email,
                ":mobile" => $mobile,
                ":password_hash" => $emptyPasswordHash,
                ":course" => $course,
                ":year" => $year,
                ":gender" => $gender,
                ":gpa" => $gpa
            ]);
            $newStudentId = (int) $pdo->lastInsertId();
            studenthub_log_admin_action($pdo, "created", "student", $newStudentId, "Created student " . $email . ".");
            student_api_response(201, ["ok" => true, "message" => "Student record added successfully.", "student_id" => $newStudentId]);
        }

        $statement = $pdo->prepare(
            "UPDATE students
             SET name = :name, email = :email, mobile = :mobile, course = :course, year = :year, gender = :gender, gpa = :gpa
             WHERE student_id = :student_id AND status = 'active'"
        );
        $statement->execute([
            ":name" => $name,
            ":email" => $email,
            ":mobile" => $mobile,
            ":course" => $course,
            ":year" => $year,
            ":gender" => $gender,
            ":gpa" => $gpa,
            ":student_id" => $studentId
        ]);
        if ($statement->rowCount() === 0) {
            $exists = $pdo->prepare("SELECT student_id FROM students WHERE student_id = :student_id AND status = 'active'");
            $exists->execute([":student_id" => $studentId]);
            if (!$exists->fetch()) {
                student_api_response(404, ["ok" => false, "error" => "Active student record was not found."]);
            }
        }
        studenthub_log_admin_action($pdo, "updated", "student", $studentId, "Updated student " . $email . ".");
        student_api_response(200, ["ok" => true, "message" => "Student record updated successfully."]);
    }

    $studentId = isset($input["student_id"]) ? (int) $input["student_id"] : 0;
    $action = isset($input["action"]) && is_string($input["action"]) ? $input["action"] : "delete";
    if ($studentId < 1 || !in_array($action, ["delete", "restore"], true)) {
        student_api_response(400, ["ok" => false, "error" => "A valid student ID and action are required."]);
    }
    $target = $pdo->prepare("SELECT role FROM students WHERE student_id = :student_id LIMIT 1");
    $target->execute([":student_id" => $studentId]);
    $targetStudent = $target->fetch();
    if (!$targetStudent) {
        student_api_response(404, ["ok" => false, "error" => "Student record was not found."]);
    }
    if ($action === "delete" && ($studentId === (int) $_SESSION["logged_in_user"]["id"] || $targetStudent["role"] === "admin")) {
        student_api_response(403, ["ok" => false, "error" => "Admin accounts cannot be deleted from student management."]);
    }
    $status = $action === "delete" ? "deleted" : "active";
    $statement = $pdo->prepare("UPDATE students SET status = :status WHERE student_id = :student_id");
    $statement->execute([":status" => $status, ":student_id" => $studentId]);
    studenthub_log_admin_action($pdo, $action === "delete" ? "deleted" : "restored", "student", $studentId, ucfirst($action) . " student record.");
    student_api_response(200, [
        "ok" => true,
        "message" => $action === "delete" ? "Student record moved to deleted records." : "Student record restored successfully."
    ]);
} catch (Throwable $error) {
    error_log("StudentHub students API error: " . $error->getMessage());
    student_api_response(500, ["ok" => false, "error" => "The student request could not be completed."]);
}
