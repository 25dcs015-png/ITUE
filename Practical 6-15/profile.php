<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
studenthub_require_login();
$user = $_SESSION["logged_in_user"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Profile</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="centered-subpage">

<br><br>
<center>
<table border="1" width="650" cellpadding="14" cellspacing="0">
    <tr>
        <td align="left" width="70%">
            <h1>PROFILE</h1>
        </td>
        <td align="right" width="30%">
            <div class="top-nav-flex">
                <a href="home.php" class="back-button"><b>BACK</b></a>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" style="border-bottom: none !important;">
            <table border="1" width="130" height="130" cellpadding="10">
                <tr>
                    <td align="center" valign="middle">
                        <h2>PHOTO</h2>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="content-container-td">
            <div class="info-box">
                <table class="info-table" width="100%" cellpadding="10" cellspacing="0">
                    <tr>
                        <td width="40%"><b>NAME</b></td>
                        <td width="60%" id="profile-name"><?php echo htmlspecialchars($user["name"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td><b>EMAIL</b></td>
                        <td id="profile-email"><?php echo htmlspecialchars($user["email"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td><b>MOBILE NUMBER</b></td>
                        <td id="profile-mobile"><?php echo htmlspecialchars($user["mobile"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td><b>COURSE</b></td>
                        <td id="profile-course"><?php echo htmlspecialchars($user["course"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td><b>YEAR</b></td>
                        <td id="profile-year"><?php echo htmlspecialchars($user["year"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td><b>GENDER</b></td>
                        <td id="profile-gender"><?php echo htmlspecialchars($user["gender"], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                </table>
            </div>

            <br>
            <div class="info-box">
                <h2>STUDENT DIRECTORY &amp; PEER PROFILES</h2>
                <div class="data-controls-bar">
                    <div class="control-group search-group">
                        <label for="student-search"><b>SEARCH:</b></label>
                        <input type="text" id="student-search" placeholder="Search by name, ID, email...">
                    </div>
                    <div class="control-group">
                        <label for="student-course-filter"><b>COURSE:</b></label>
                        <select id="student-course-filter">
                            <option value="all">All Courses</option>
                            <option value="Computer Science and Engineering">Computer Science</option>
                            <option value="Information Technology">Information Tech</option>
                            <option value="Electronics and Communication">Electronics</option>
                            <option value="Mechanical Engineering">Mechanical</option>
                            <option value="Civil Engineering">Civil</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label for="student-year-filter"><b>YEAR:</b></label>
                        <select id="student-year-filter">
                            <option value="all">All Years</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label for="student-sort"><b>SORT:</b></label>
                        <select id="student-sort">
                            <option value="name-asc">Name (A - Z)</option>
                            <option value="name-desc">Name (Z - A)</option>
                            <option value="gpa-desc">GPA (Highest First)</option>
                            <option value="gpa-asc">GPA (Lowest First)</option>
                        </select>
                    </div>
                </div>

                <div id="student-status" class="data-status-msg">Loading student profiles from the database...</div>

                <div id="students-container" class="card-list-grid"></div>

                <div class="pagination-wrapper" id="students-pagination-wrapper">
                    <div class="pagination-info" id="students-pagination-info"></div>
                    <div class="pagination-controls" id="students-pagination"></div>
                </div>
            </div>
        </td>
    </tr>
</table>
</center>

<script src="script.js"></script>
</body>
</html>