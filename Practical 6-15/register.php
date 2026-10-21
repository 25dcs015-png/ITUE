<?php
session_start();

$errors = [];
$success = false;
$submittedData = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = isset($_POST["csrf_token"]) && is_string($_POST["csrf_token"]) ? $_POST["csrf_token"] : "";
    $csrfValid = !empty($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $submittedToken);
    if (!$csrfValid) {
        $errors[] = "Your form session has expired or is invalid. Please reload the registration form and try again.";
    }

    $rawName = isset($_POST["name"]) && is_string($_POST["name"]) ? trim($_POST["name"]) : "";
    $rawEmail = isset($_POST["email"]) && is_string($_POST["email"]) ? trim($_POST["email"]) : "";
    $rawMobile = isset($_POST["mobile"]) && is_string($_POST["mobile"]) ? trim($_POST["mobile"]) : "";
    $rawPassword = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
    $rawConfirmPassword = isset($_POST["confirm_password"]) && is_string($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";
    $rawCourse = isset($_POST["course"]) && is_string($_POST["course"]) ? trim($_POST["course"]) : "";
    $rawYear = isset($_POST["year"]) && is_string($_POST["year"]) ? trim($_POST["year"]) : "";
    $rawGender = isset($_POST["gender"]) && is_string($_POST["gender"]) ? trim($_POST["gender"]) : "";
    $rawTerms = isset($_POST["terms"]) && is_string($_POST["terms"]) ? $_POST["terms"] : "";

    $name = $rawName;
    $email = strtolower($rawEmail);
    $mobile = $rawMobile;
    $course = $rawCourse;
    $year = $rawYear;
    $gender = $rawGender;

    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (!preg_match("/^[A-Za-z\s]{2,50}$/", $name)) {
        $errors[] = "Name must only contain letters and spaces (2 to 50 characters).";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (strlen($email) > 254 || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
        $errors[] = "Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.";
    }

    if (empty($rawPassword)) {
        $errors[] = "Password is required.";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,30}$/", $rawPassword)) {
        $errors[] = "Use 6 to 30 letters, numbers, or @$!%*#?&, including a letter and a number.";
    }

    if (empty($rawConfirmPassword)) {
        $errors[] = "Please confirm your password.";
    } elseif ($rawPassword !== $rawConfirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    $validCourses = [
        "Computer Science and Engineering",
        "Information Technology",
        "Electronics and Communication",
        "Mechanical Engineering",
        "Civil Engineering"
    ];
    if (empty($course) || !in_array($course, $validCourses, true)) {
        $errors[] = "Please select a valid engineering course.";
    }

    $validYears = ["1st Year", "2nd Year", "3rd Year", "4th Year"];
    if (empty($year) || !in_array($year, $validYears, true)) {
        $errors[] = "Please select a valid academic year.";
    }

    $validGenders = ["Male", "Female", "Other"];
    if (empty($gender) || !in_array($gender, $validGenders, true)) {
        $errors[] = "Please select a valid gender option.";
    }

    if (empty($rawTerms)) {
        $errors[] = "You must accept the Terms and Conditions.";
    }

    try {
        require __DIR__ . DIRECTORY_SEPARATOR . "db.php";
        $pdo->beginTransaction();
        $outcome = $csrfValid ? (empty($errors) ? "created" : "validation_failed") : "csrf_rejected";
        $auditEmail = substr($rawEmail, 0, 254);

        if ($csrfValid && empty($errors)) {
            $duplicateCheck = $pdo->prepare("SELECT student_id FROM students WHERE email = :email LIMIT 1");
            $duplicateCheck->execute(["email" => $email]);
            if ($duplicateCheck->fetch()) {
                $errors[] = "An account with this email address is already registered.";
                $outcome = "duplicate_email";
            } else {
                $studentCode = "STU" . date("ymd") . strtoupper(bin2hex(random_bytes(3)));
                $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
                $statement = $pdo->prepare(
                    "INSERT INTO students
                        (student_code, name, email, mobile, password_hash, course, year, gender)
                     VALUES
                        (:student_code, :name, :email, :mobile, :password_hash, :course, :year, :gender)"
                );
                $statement->execute([
                    "student_code" => $studentCode,
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "password_hash" => $passwordHash,
                    "course" => $course,
                    "year" => $year,
                    "gender" => $gender
                ]);

                $outcome = "created";
                $submittedData = [
                    "regId" => $studentCode,
                    "timestamp" => date("Y-m-d H:i:s"),
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "course" => $course,
                    "year" => $year,
                    "gender" => $gender
                ];
            }
        }

        $audit = $pdo->prepare(
            "INSERT INTO registration_audit (email, outcome, ip_address)
             VALUES (:email, :outcome, :ip_address)"
        );
        $audit->execute([
            "email" => $auditEmail,
            "outcome" => $outcome,
            "ip_address" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null
        ]);
        $pdo->commit();
        $success = $outcome === "created";
    } catch (PDOException $error) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("StudentHub registration database error: " . $error->getMessage());
        if ($error->getCode() === "23000") {
            $errors[] = "An account with this email address is already registered.";
        } else {
            $errors[] = "Registration could not be saved or audited in the database. Please try again later.";
        }
    } catch (Throwable $error) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("StudentHub registration error: " . $error->getMessage());
        $errors[] = "Registration could not be saved or audited. Please try again later.";
    }
} else {
    header("Location: signup.php");
    exit;
}

if ($success) {
    header("Location: login-form.php?registered=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Registration Status</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<br><br>
<center>
<table border="1" width="650" cellpadding="16" cellspacing="0">
    <tr>
        <td align="center" bgcolor="#f0f0f0">
            <h1><?php echo $success ? "REGISTRATION SUCCESSFUL" : "REGISTRATION FAILED"; ?></h1>
        </td>
    </tr>
    <tr>
        <td align="center" class="content-container-td">
            <div class="form-box">
                <?php if ($success): ?>
                    <div style="background: #e8f5e9; border: 1.5px solid #a5d6a7; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <h3 style="color: #1b5e20; margin-bottom: 6px;">Registration Saved to Database</h3>
                        <p style="margin: 0; color: #2e7d32; font-size: 14px;">Your registration details have passed server-side validation and are stored in the StudentHub database.</p>
                    </div>

                    <table border="0" width="100%" class="form-table info-table" cellpadding="8" cellspacing="4">
                        <tr>
                            <td width="40%" align="right"><b>REGISTRATION ID:</b></td>
                            <td width="60%" align="left"><span class="tag-badge tag-technical"><?php echo htmlspecialchars($submittedData["regId"]); ?></span></td>
                        </tr>
                        <tr>
                            <td align="right"><b>TIMESTAMP:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["timestamp"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>NAME:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["name"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>EMAIL:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["email"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>MOBILE:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["mobile"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>COURSE:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["course"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>YEAR:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["year"]); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><b>GENDER:</b></td>
                            <td align="left"><?php echo htmlspecialchars($submittedData["gender"]); ?></td>
                        </tr>
                    </table>

                    <div style="margin-top: 24px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <a href="login-form.php" class="page-btn" style="text-decoration: none; padding: 8px 16px;">PROCEED TO LOGIN</a>
                        <a href="login-form.php" class="page-btn" style="text-decoration: none; padding: 8px 16px;">BACK TO LOGIN</a>
                    </div>
                <?php else: ?>
                    <div style="background: #ffebee; border: 1.5px solid #ffcdd2; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <h3 style="color: #c62828; margin-bottom: 10px;">Server Validation Errors Found</h3>
                        <ul style="text-align: left; color: #b71c1c; font-size: 14px; margin: 0; padding-left: 24px;">
                            <?php foreach ($errors as $err): ?>
                                <li style="margin-bottom: 4px;"><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div style="margin-top: 20px;">
                        <a href="signup.php" class="page-btn active" style="text-decoration: none; padding: 8px 20px;">« GO BACK &amp; EDIT FORM</a>
                    </div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
</center>

<script src="script.js"></script>
</body>
</html>
