<?php
require __DIR__ . DIRECTORY_SEPARATOR . "auth.php";
studenthub_require_login("admin");

$categories = ["Technical", "Cultural", "Sports", "General"];
$formats = ["Offline", "Online", "Hybrid"];
$statuses = ["open", "closed"];
$message = "";
$messageType = "status-empty";
$search = isset($_GET["search"]) && is_string($_GET["search"]) ? trim($_GET["search"]) : "";
$categoryFilter = isset($_GET["category"]) && is_string($_GET["category"]) ? $_GET["category"] : "all";
$statusFilter = isset($_GET["status"]) && is_string($_GET["status"]) ? $_GET["status"] : "all";
if (!in_array($categoryFilter, array_merge(["all"], $categories), true)) {
    $categoryFilter = "all";
}
if (!in_array($statusFilter, array_merge(["all"], $statuses, ["deleted"]), true)) {
    $statusFilter = "all";
}
$adminId = (int) $_SESSION["logged_in_user"]["id"];
$uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "event-posters";
$uploadUrlPrefix = "uploads/event-posters/";
$maxUploadSize = 2 * 1024 * 1024;

function event_management_log($mysqli, $eventId, $adminId, $action, $details)
{
    $statement = $mysqli->prepare(
        "INSERT INTO event_audit (event_id, admin_id, action, details)
         VALUES (?, ?, ?, ?)"
    );
    $statement->bind_param("iiss", $eventId, $adminId, $action, $details);
    $statement->execute();
    studenthub_log_admin_action($mysqli, $action, "event", $eventId, $details);
}

function event_management_remove_poster($posterPath)
{
    if (!$posterPath || strpos($posterPath, "uploads/event-posters/") !== 0) {
        return;
    }
    $fileName = basename($posterPath);
    if ($fileName !== "" && $fileName !== "." && $fileName !== "..") {
        $fullPath = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "event-posters" . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($fullPath) && !unlink($fullPath)) {
            error_log("StudentHub could not remove event poster: " . $fullPath);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search = isset($_POST["search"]) && is_string($_POST["search"]) ? trim($_POST["search"]) : $search;
    $categoryFilter = isset($_POST["category_filter"]) && is_string($_POST["category_filter"]) ? $_POST["category_filter"] : $categoryFilter;
    $statusFilter = isset($_POST["status_filter"]) && is_string($_POST["status_filter"]) ? $_POST["status_filter"] : $statusFilter;
    if (!in_array($categoryFilter, array_merge(["all"], $categories), true)) {
        $categoryFilter = "all";
    }
    if (!in_array($statusFilter, array_merge(["all"], $statuses, ["deleted"]), true)) {
        $statusFilter = "all";
    }

    if (!studenthub_valid_csrf(isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : null)) {
        $message = "Your form session has expired or is invalid. Please reload and try again.";
        $messageType = "status-error";
    } else {
        $action = isset($_POST["action"]) && is_string($_POST["action"]) ? $_POST["action"] : "";
        $eventId = isset($_POST["event_id"]) ? (int) $_POST["event_id"] : 0;
        $newPosterPath = null;
        $oldPosterPath = null;
        $transactionStarted = false;

        try {
            require __DIR__ . DIRECTORY_SEPARATOR . "db-mysqli.php";

            if ($action === "save") {
                $title = isset($_POST["title"]) && is_string($_POST["title"]) ? trim($_POST["title"]) : "";
                $category = isset($_POST["category"]) && is_string($_POST["category"]) ? $_POST["category"] : "";
                $eventDate = isset($_POST["event_date"]) && is_string($_POST["event_date"]) ? $_POST["event_date"] : "";
                $eventTime = isset($_POST["event_time"]) && is_string($_POST["event_time"]) ? trim($_POST["event_time"]) : "";
                $venue = isset($_POST["venue"]) && is_string($_POST["venue"]) ? trim($_POST["venue"]) : "";
                $format = isset($_POST["format"]) && is_string($_POST["format"]) ? $_POST["format"] : "";
                $description = isset($_POST["description"]) && is_string($_POST["description"]) ? trim($_POST["description"]) : "";
                $status = isset($_POST["event_status"]) && is_string($_POST["event_status"]) ? $_POST["event_status"] : "";
                $removePoster = isset($_POST["remove_poster"]) && $_POST["remove_poster"] === "1";
                $upload = isset($_FILES["poster"]) ? $_FILES["poster"] : null;
                $date = DateTime::createFromFormat("Y-m-d", $eventDate);

                if ($title === "" || strlen($title) > 150) {
                    $message = "Event title is required and must be at most 150 characters.";
                } elseif (!in_array($category, $categories, true)) {
                    $message = "Select a valid event category.";
                } elseif (!$date || $date->format("Y-m-d") !== $eventDate) {
                    $message = "Enter a valid event date.";
                } elseif ($eventTime === "" || strlen($eventTime) > 30 || $venue === "" || strlen($venue) > 150) {
                    $message = "Enter a valid event time and venue.";
                } elseif (!in_array($format, $formats, true) || !in_array($status, $statuses, true)) {
                    $message = "Select a valid event format and status.";
                } elseif ($description === "") {
                    $message = "Event description is required.";
                } elseif ($upload && $upload["error"] !== UPLOAD_ERR_NO_FILE && $upload["error"] !== UPLOAD_ERR_OK) {
                    $message = $upload["error"] === UPLOAD_ERR_INI_SIZE || $upload["error"] === UPLOAD_ERR_FORM_SIZE
                        ? "Poster image must not exceed 2 MB."
                        : "Poster upload failed. Please choose another image and try again.";
                } elseif ($upload && $upload["error"] === UPLOAD_ERR_OK && (empty($upload["tmp_name"]) || !is_uploaded_file($upload["tmp_name"]))) {
                    $message = "The uploaded poster could not be verified.";
                } elseif ($upload && $upload["error"] === UPLOAD_ERR_OK && $upload["size"] > $maxUploadSize) {
                    $message = "Poster image must not exceed 2 MB.";
                } else {
                    $existingPoster = null;
                    if ($eventId > 0) {
                        $existing = $mysqli->prepare("SELECT poster_path FROM events WHERE event_id = ? AND status <> 'deleted' LIMIT 1");
                        $existing->bind_param("i", $eventId);
                        $existing->execute();
                        $existingEvent = $existing->get_result()->fetch_assoc();
                        if (!$existingEvent) {
                            throw new RuntimeException("Event record was not found.");
                        }
                        $existingPoster = $existingEvent["poster_path"];
                    }

                    if ($upload && $upload["error"] === UPLOAD_ERR_OK) {
                        if (!class_exists("finfo")) {
                            throw new RuntimeException("File type validation is unavailable on this server.");
                        }
                        $imageInfo = @getimagesize($upload["tmp_name"]);
                        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload["tmp_name"]);
                        $extensions = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
                        if (!$imageInfo || !isset($extensions[$mime])) {
                            $message = "Poster must be a valid JPG, PNG, or WebP image.";
                        } elseif (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                            throw new RuntimeException("Poster upload directory could not be created.");
                        } elseif (!is_writable($uploadDirectory)) {
                            throw new RuntimeException("Poster upload directory is not writable.");
                        } else {
                            $fileName = bin2hex(random_bytes(16)) . "." . $extensions[$mime];
                            if (!move_uploaded_file($upload["tmp_name"], $uploadDirectory . DIRECTORY_SEPARATOR . $fileName)) {
                                throw new RuntimeException("Poster image could not be saved.");
                            }
                            $newPosterPath = $uploadUrlPrefix . $fileName;
                        }
                    }

                    if ($message === "") {
                        $posterPath = $newPosterPath !== null ? $newPosterPath : ($removePoster ? null : $existingPoster);
                        $mysqli->begin_transaction();
                        $transactionStarted = true;
                        if ($eventId > 0) {
                            $statement = $mysqli->prepare(
                                "UPDATE events
                                 SET title = ?, category = ?, event_date = ?, event_time = ?, venue = ?,
                                     event_format = ?, description = ?, status = ?, poster_path = ?
                                 WHERE event_id = ? AND status <> 'deleted'"
                            );
                            $statement->bind_param("sssssssssi", $title, $category, $eventDate, $eventTime, $venue, $format, $description, $status, $posterPath, $eventId);
                            $statement->execute();
                            $auditAction = "updated";
                            $message = "Event updated successfully.";
                            $oldPosterPath = ($newPosterPath !== null || $removePoster) ? $existingPoster : null;
                        } else {
                            $statement = $mysqli->prepare(
                                "INSERT INTO events
                                    (title, category, event_date, event_time, venue, event_format, description, status, poster_path)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            $statement->bind_param("sssssssss", $title, $category, $eventDate, $eventTime, $venue, $format, $description, $status, $posterPath);
                            $statement->execute();
                            $eventId = $mysqli->insert_id;
                            $auditAction = "created";
                            $message = "Event added successfully.";
                        }
                        event_management_log($mysqli, $eventId, $adminId, $auditAction, $title);
                        $mysqli->commit();
                        $transactionStarted = false;
                        $messageType = "status-success";
                        if ($oldPosterPath) {
                            event_management_remove_poster($oldPosterPath);
                        }
                    }
                }
            } elseif ($action === "delete" || $action === "restore") {
                if ($eventId < 1) {
                    $message = "Select a valid event.";
                } else {
                    $newStatus = $action === "delete" ? "deleted" : "open";
                    $mysqli->begin_transaction();
                    $transactionStarted = true;
                    $statement = $mysqli->prepare("UPDATE events SET status = ? WHERE event_id = ? AND status <> 'deleted'");
                    if ($action === "restore") {
                        $statement = $mysqli->prepare("UPDATE events SET status = 'open' WHERE event_id = ? AND status = 'deleted'");
                        $statement->bind_param("i", $eventId);
                    } else {
                        $statement->bind_param("si", $newStatus, $eventId);
                    }
                    $statement->execute();
                    if ($statement->affected_rows > 0) {
                        event_management_log($mysqli, $eventId, $adminId, $action === "delete" ? "deleted" : "restored", "");
                        $mysqli->commit();
                        $transactionStarted = false;
                        $message = $action === "delete" ? "Event moved to deleted records." : "Event restored and set to open.";
                        $messageType = "status-success";
                    } else {
                        $mysqli->rollback();
                        $transactionStarted = false;
                        $message = "No event record was changed.";
                    }
                }
            } else {
                $message = "Unknown event management action.";
            }
        } catch (Throwable $error) {
            if (isset($mysqli) && $transactionStarted) {
                $mysqli->rollback();
            }
            if ($newPosterPath !== null) {
                event_management_remove_poster($newPosterPath);
            }
            error_log("StudentHub event management error: " . $error->getMessage());
            $message = "The event could not be saved. Check the database and upload folder, then try again.";
            $messageType = "status-error";
        }
    }
}

$events = [];
$editEvent = null;
$databaseError = "";
try {
    require __DIR__ . DIRECTORY_SEPARATOR . "db-mysqli.php";
    $where = ["(title LIKE ? OR category LIKE ? OR venue LIKE ?)"];
    $params = ["%" . $search . "%", "%" . $search . "%", "%" . $search . "%"];
    $types = "sss";
    if ($categoryFilter !== "all") {
        $where[] = "category = ?";
        $params[] = $categoryFilter;
        $types .= "s";
    }
    if ($statusFilter !== "all") {
        $where[] = "status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }
    $sql = "SELECT event_id, title, category, event_date, event_time, venue, event_format, description, status, poster_path
            FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date DESC, event_id DESC";
    $statement = $mysqli->prepare($sql);
    $statement->bind_param($types, ...$params);
    $statement->execute();
    $events = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

    if (isset($_GET["edit"])) {
        $editId = (int) $_GET["edit"];
        $edit = $mysqli->prepare("SELECT event_id, title, category, event_date, event_time, venue, event_format, description, status, poster_path FROM events WHERE event_id = ? AND status <> 'deleted' LIMIT 1");
        $edit->bind_param("i", $editId);
        $edit->execute();
        $editEvent = $edit->get_result()->fetch_assoc() ?: null;
    }
} catch (Throwable $error) {
    error_log("StudentHub event list error: " . $error->getMessage());
    $databaseError = "Events could not be loaded. Apply the latest studenthub.sql schema and check the database connection.";
}

$adminName = $_SESSION["logged_in_user"]["name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Event Management</title>
    <link id="theme-style" rel="stylesheet" href="style.css">
</head>
<body class="portal-subpage">
<table border="1" width="100%" cellpadding="10" cellspacing="0">
    <tr>
        <td align="left" width="60%"><h1>EVENT MANAGEMENT</h1></td>
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
    <tr><td colspan="2" align="center" bgcolor="#f0f0f0"><h2>MANAGE EVENTS AND POSTERS</h2></td></tr>
    <tr>
        <td colspan="2" class="content-container-td">
            <div class="info-box">
                <?php if ($message !== ""): ?><div class="data-status-msg <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>" style="display: block;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if ($databaseError !== ""): ?>
                    <div class="data-status-msg status-error" style="display: block;"><?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <h2><?php echo $editEvent ? "EDIT EVENT" : "ADD EVENT"; ?></h2>
                    <form method="post" action="event-management.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="event_id" value="<?php echo (int) ($editEvent["event_id"] ?? 0); ?>">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="category_filter" value="<?php echo htmlspecialchars($categoryFilter, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxUploadSize; ?>">
                        <table class="info-table" width="100%" cellpadding="8" cellspacing="0">
                            <tr><td><b>TITLE</b></td><td><input name="title" maxlength="150" required value="<?php echo htmlspecialchars($editEvent["title"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td><b>CATEGORY</b></td><td><select name="category" required><option value="">Select Category</option><?php foreach ($categories as $option): ?><option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editEvent["category"]) && $editEvent["category"] === $option ? "selected" : ""; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td></tr>
                            <tr><td><b>DATE</b></td><td><input type="date" name="event_date" required value="<?php echo htmlspecialchars($editEvent["event_date"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td><b>TIME</b></td><td><input name="event_time" maxlength="30" required value="<?php echo htmlspecialchars($editEvent["event_time"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td></tr>
                            <tr><td><b>VENUE</b></td><td><input name="venue" maxlength="150" required value="<?php echo htmlspecialchars($editEvent["venue"] ?? "", ENT_QUOTES, 'UTF-8'); ?>"></td>
                                <td><b>FORMAT</b></td><td><select name="format" required><?php foreach ($formats as $option): ?><option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editEvent["event_format"]) && $editEvent["event_format"] === $option ? "selected" : ""; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td></tr>
                            <tr><td><b>STATUS</b></td><td><select name="event_status" required><?php foreach ($statuses as $option): ?><option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo isset($editEvent["status"]) && $editEvent["status"] === $option ? "selected" : ($option === "open" && !$editEvent ? "selected" : ""); ?>><?php echo strtoupper($option); ?></option><?php endforeach; ?></select></td>
                                <td><b>POSTER</b></td><td><input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><?php if (!empty($editEvent["poster_path"])): ?><br><img src="<?php echo htmlspecialchars($editEvent["poster_path"], ENT_QUOTES, 'UTF-8'); ?>" alt="Current event poster" style="max-width: 140px; margin-top: 8px;"><br><label><input type="checkbox" name="remove_poster" value="1"> Remove current poster</label><?php endif; ?><div>JPG, PNG, or WebP; maximum 2 MB.</div></td></tr>
                            <tr><td><b>DESCRIPTION</b></td><td colspan="3"><textarea name="description" rows="4" maxlength="5000" required style="width: 100%; box-sizing: border-box;"><?php echo htmlspecialchars($editEvent["description"] ?? "", ENT_QUOTES, 'UTF-8'); ?></textarea></td></tr>
                            <tr><td colspan="4" align="center"><button type="submit" class="page-btn active"><?php echo $editEvent ? "UPDATE EVENT" : "ADD EVENT"; ?></button> <?php if ($editEvent): ?><a href="event-management.php" class="page-btn">CANCEL</a><?php endif; ?></td></tr>
                        </table>
                    </form>
                    <br>
                    <h2>EVENT LIST</h2>
                    <form method="get" action="event-management.php" class="data-controls-bar">
                        <div class="control-group search-group"><label for="event-management-search"><b>SEARCH:</b></label><input type="search" id="event-management-search" name="search" placeholder="Title, category, venue" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"></div>
                        <div class="control-group"><label for="event-management-category"><b>CATEGORY:</b></label><select id="event-management-category" name="category"><option value="all">All Categories</option><?php foreach ($categories as $option): ?><option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $categoryFilter === $option ? "selected" : ""; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
                        <div class="control-group"><label for="event-management-status"><b>STATUS:</b></label><select id="event-management-status" name="status"><option value="all" <?php echo $statusFilter === "all" ? "selected" : ""; ?>>All</option><option value="open" <?php echo $statusFilter === "open" ? "selected" : ""; ?>>Open</option><option value="closed" <?php echo $statusFilter === "closed" ? "selected" : ""; ?>>Closed</option><option value="deleted" <?php echo $statusFilter === "deleted" ? "selected" : ""; ?>>Deleted</option></select></div>
                        <button class="page-btn active" type="submit">FILTER</button><a href="event-management.php" class="page-btn">CLEAR</a>
                    </form>
                    <?php if (empty($events)): ?><div class="data-status-msg status-empty" style="display: block;">No events match the selected filters.</div><?php else: ?>
                        <div style="overflow-x: auto;"><table border="1" width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; text-align: left; font-size: 13px;">
                            <thead><tr bgcolor="#e8f5e9" style="color: #1b5e20;"><th>POSTER</th><th>TITLE</th><th>CATEGORY</th><th>DATE</th><th>VENUE</th><th>FORMAT</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
                            <tbody><?php foreach ($events as $event): ?><tr>
                                <td><?php if ($event["poster_path"]): ?><img src="<?php echo htmlspecialchars($event["poster_path"], ENT_QUOTES, 'UTF-8'); ?>" alt="Event poster" style="max-width: 80px; max-height: 60px;"><?php else: ?>—<?php endif; ?></td>
                                <td><?php echo htmlspecialchars($event["title"], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($event["category"], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($event["event_date"], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($event["venue"], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($event["event_format"], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($event["status"], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php if ($event["status"] !== "deleted"): ?><a class="page-btn" href="event-management.php?<?php echo htmlspecialchars(http_build_query(["search" => $search, "category" => $categoryFilter, "status" => $statusFilter, "edit" => $event["event_id"]]), ENT_QUOTES, 'UTF-8'); ?>">EDIT</a><?php endif; ?>
                                    <form method="post" action="event-management.php" style="display: inline;" onsubmit="return confirm('<?php echo $event["status"] === "deleted" ? "Restore this event?" : "Move this event to deleted records?"; ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(studenthub_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="event_id" value="<?php echo (int) $event["event_id"]; ?>"><input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="category_filter" value="<?php echo htmlspecialchars($categoryFilter, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="<?php echo $event["status"] === "deleted" ? "restore" : "delete"; ?>"><button class="page-btn active" type="submit"><?php echo $event["status"] === "deleted" ? "RESTORE" : "DELETE"; ?></button>
                                    </form>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                        </table></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<script src="script.js"></script>
</body>
</html>
