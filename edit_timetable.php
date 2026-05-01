<?php
session_start();
require_once 'db.php';

// Protect admin route
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: timetable.php");
    exit();
}

$id = intval($_GET['id']);
$message = "";

// Fetch the timetable record
$stmt = $conn->prepare("SELECT * FROM timetable WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$timetable = $result->fetch_assoc();
if (!$timetable) {
    die("<div class='alert alert-danger text-center mt-5'>Invalid timetable ID.</div>");
}

// Fetch all courses
$courses = $conn->query("SELECT * FROM courses ORDER BY name ASC");

// Fetch all lecturers
$lecturers = $conn->query("SELECT id, name FROM users WHERE role='lecturer' ORDER BY name ASC");

// Handle update submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_id = $_POST['course_id'];
    $subject = $_POST['subject'];
    $lecturer_id = $_POST['lecturer_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $update = $conn->prepare("UPDATE timetable SET course_id=?, subject=?, lecturer_id=?, day=?, start_time=?, end_time=?, room=? WHERE id=?");
    if ($update) {
        $update->bind_param("isissssi", $course_id, $subject, $lecturer_id, $day, $start_time, $end_time, $room, $id);
        if ($update->execute()) {
            $message = "<div class='alert alert-success'>✅ Timetable updated successfully!</div>";
            // Refresh record
            $stmt = $conn->prepare("SELECT * FROM timetable WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $timetable = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "<div class='alert alert-danger'>❌ Error updating record: " . $update->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ SQL Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Timetable | RCTI Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            background: #1b203d;
            border-radius: 12px;
            padding: 40px;
            max-width: 800px;
            margin-top: 60px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .btn-custom {
            background-color: #00ff99;
            color: #000;
            font-weight: 600;
        }
        .btn-custom:hover {
            background-color: #00cc7a;
        }
        a.back-link {
            color: #00ff99;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Timetable Entry</h3>

    <?= $message ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Course</label>
            <select name="course_id" class="form-select" required>
                <option value="">Select Course</option>
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($timetable['course_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['code']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($timetable['subject']) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Lecturer</label>
            <select name="lecturer_id" class="form-select" required>
                <option value="">Select Lecturer</option>
                <?php while ($row = $lecturers->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($timetable['lecturer_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Day</label>
            <select name="day" class="form-select" required>
                <?php
                $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                foreach ($days as $d): ?>
                    <option value="<?= $d ?>" <?= ($timetable['day'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($timetable['start_time']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($timetable['end_time']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Room</label>
            <input type="text" name="room" class="form-control" value="<?= htmlspecialchars($timetable['room']) ?>" required>
        </div>

        <div class="col-md-12 d-flex justify-content-between mt-4">
            <a href="timetable.php" class="back-link"><i class="bi bi-arrow-left-circle"></i> Back to Timetable</a>
            <button type="submit" class="btn btn-custom px-4"><i class="bi bi-save"></i> Update</button>
        </div>
    </form>
</div>

</body>
</html>
