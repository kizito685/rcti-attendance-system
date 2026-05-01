<?php
session_start();
require_once 'db.php';

// Protect admin route
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle new timetable assignment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $course_id = $_POST['course_id'];
    $subject = $_POST['subject'];
    $lecturer_id = $_POST['lecturer_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];

    $stmt = $conn->prepare("INSERT INTO timetable (course_id, subject, lecturer_id, day, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isissss", $course_id, $subject, $lecturer_id, $day, $start_time, $end_time, $room);
        if ($stmt->execute()) {
            $success = "✅ Lecture assigned successfully!";
        } else {
            $error = "❌ Error assigning lecture: " . $stmt->error;
        }
    } else {
        $error = "❌ Error preparing statement: " . $conn->error;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM timetable WHERE id = $id");
    $success = "🗑️ Timetable record deleted successfully.";
}

// Fetch courses
$courses = $conn->query("SELECT * FROM courses ORDER BY name ASC");

// Fetch lecturers
$lecturers = $conn->query("SELECT id, name FROM users WHERE role='lecturer' ORDER BY name ASC");

// Fetch timetable records
$timetable_query = "
    SELECT t.*, 
           c.name AS course_name, 
           c.code AS course_code, 
           u.name AS lecturer_name
    FROM timetable t
    LEFT JOIN courses c ON t.course_id = c.id
    LEFT JOIN users u ON t.lecturer_id = u.id
    ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
";
$timetable = $conn->query($timetable_query);
if (!$timetable) {
    $timetable_error = $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Timetable | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            background: #12172b;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 25px;
        }
        .sidebar h4 {
            color: #00ff99;
            text-align: center;
            margin-bottom: 35px;
        }
        .sidebar a {
            color: #bbb;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #1a1f3a;
            border-left: 3px solid #00ff99;
            color: #fff;
        }
        .content {
            margin-left: 260px;
            padding: 40px;
        }
        .card {
            background: #1b203d;
            border: none;
            border-radius: 12px;
            padding: 25px;
        }
        table {
            background: #1b203d;
            border-radius: 10px;
        }
        th {
            color: #00ff99;
        }
        footer {
            text-align: center;
            color: #888;
            margin-top: 40px;
        }
        .btn-custom {
            background-color: #00ff99;
            color: #000;
            font-weight: 600;
        }
        .btn-custom:hover {
            background-color: #00cc7a;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #000;
            font-weight: 500;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
            font-weight: 500;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>RCTI Admin</h4>
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="add_lecturer.php"><i class="bi bi-person-plus"></i> Add Lecturer</a>
        <a href="manage_lecturers.php"><i class="bi bi-people"></i> Manage Lecturers</a>
        <a href="mark_attendance.php"><i class="bi bi-clock-history"></i> Mark Attendance</a>
        <a href="attendance_records.php"><i class="bi bi-journal-check"></i> Attendance Records</a>
        <a href="timetable.php" class="active"><i class="bi bi-calendar-event"></i> Assign Timetable</a>
        <a href="manage_departments.php"><i class="bi bi-building"></i> Manage Departments</a>
        <a href="view_issues.php"><i class="bi bi-exclamation-circle"></i> Class Rep Issues</a>
        <a href="logout.php" class="btn btn-danger mt-4 mx-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2><i class="bi bi-calendar-event"></i> Assign Lectures Timetable</h2>
        <p class="text-secondary">Create and manage lecturer-class schedules for the institution.</p>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Assign New Lecture -->
        <div class="card mb-4">
            <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Assign New Lecture</h5>
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-3">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['code']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Enter subject name" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Lecturer</label>
                    <select name="lecturer_id" class="form-select" required>
                        <option value="">Select Lecturer</option>
                        <?php while ($row = $lecturers->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Day</label>
                    <select name="day" class="form-select" required>
                        <option value="">Select Day</option>
                        <option>Monday</option>
                        <option>Tuesday</option>
                        <option>Wednesday</option>
                        <option>Thursday</option>
                        <option>Friday</option>
                        <option>Saturday</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Room</label>
                    <input type="text" name="room" class="form-control" placeholder="e.g. B10" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-custom w-100"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>

        <!-- Assigned Timetables Table -->
        <h5 class="mb-3"><i class="bi bi-list-ul"></i> Assigned Timetables</h5>
        <?php if ($timetable && $timetable->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course</th>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Lecturer</th>
                            <th>Day</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = $timetable->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= htmlspecialchars($row['lecturer_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['day']) ?></td>
                            <td><?= htmlspecialchars($row['start_time']) ?></td>
                            <td><?= htmlspecialchars($row['end_time']) ?></td>
                            <td><?= htmlspecialchars($row['room']) ?></td>
                            <td>
                                <a href="edit_timetable.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this record?')"><i class="bi bi-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">No timetable records found.</div>
        <?php endif; ?>

        <footer>
            <p>© <?= date("Y") ?> RCTI Attendance System — Timetable Management</p>
        </footer>
    </div>

</body>
</html>
