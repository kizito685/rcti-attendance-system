<?php
session_start();
require_once 'db.php';

// ✅ Allow only admin users
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Initialize counters safely
$totalLecturers = $totalAttendance = $todayAttendance = $totalIssues = 0;

if ($res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='lecturer'")) {
    $row = $res->fetch_assoc();
    $totalLecturers = $row['total'];
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM attendance")) {
    $row = $res->fetch_assoc();
    $totalAttendance = $row['total'];
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM attendance WHERE date = CURDATE()")) {
    $row = $res->fetch_assoc();
    $todayAttendance = $row['total'];
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM issues")) {
    $row = $res->fetch_assoc();
    $totalIssues = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .sidebar {
            background: #12172b;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 25px;
            overflow-y: auto;
        }
        .sidebar h4 {
            color: #00ff99;
            text-align: center;
            margin-bottom: 35px;
            font-weight: 700;
        }
        .sidebar a {
            color: #ccc;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: 0.3s;
            font-size: 15px;
        }
        .sidebar a:hover {
            background: #1a1f3a;
            border-left: 3px solid #00ff99;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 40px;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }
        .card {
            background: #1b203d;
            border: none;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 255, 153, 0.25);
        }
        .card h2 {
            color: #00ff99;
            font-size: 2.3rem;
            margin-bottom: 8px;
        }
        .card p {
            color: #ccc;
            margin: 0;
        }
        footer {
            text-align: center;
            color: #888;
            margin-top: 60px;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            width: 90%;
            margin: 15px auto;
            display: block;
            border-radius: 8px;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background-color: #bb2d3b;
        }
        .quick-actions a {
            border-radius: 10px;
            transition: 0.3s;
        }
        .quick-actions a:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>RCTI Admin</h4>
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="add_lecturer.php"><i class="bi bi-person-plus"></i> Add Lecturer</a>
        <a href="manage_lecturers.php"><i class="bi bi-people"></i> Manage Lecturers</a>
        <a href="manage_users.php"><i class="bi bi-gear-wide-connected"></i> Manage Users</a>
        <a href="mark_attendance.php"><i class="bi bi-clock-history"></i> Mark Attendance</a>
        <a href="timetable.php"><i class="bi bi-calendar-week"></i> Manage Timetable</a>
        <a href="manage_courses.php"><i class="bi bi-book"></i> Manage Courses</a>
        <a href="attendance_records.php"><i class="bi bi-journal-check"></i> Attendance Records</a>
        <a href="attendance_reports.php"><i class="bi bi-bar-chart-line"></i> Attendance Reports</a>
        <a href="manage_departments.php" class="nav-link">🏫 Manage Departments</a>
        <a href="manage_reps.php" class="nav-link">Manage class_reps</a>
        <a href="view_issues.php"><i class="bi bi-exclamation-circle"></i> Class Rep Issues</a>
        <a href="logout.php" class="btn logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="content">
        <h2 class="fw-bold mb-1">👋 Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <p class="text-secondary mb-4">You are logged in as <strong>Administrator</strong>.</p>

        <div class="dashboard-cards">
            <div class="card">
                <h2><?php echo $totalLecturers; ?></h2>
                <p>Total Lecturers</p>
            </div>
            <div class="card">
                <h2><?php echo $totalAttendance; ?></h2>
                <p>All Attendance Records</p>
            </div>
            <div class="card">
                <h2><?php echo $todayAttendance; ?></h2>
                <p>Today's Attendance</p>
            </div>
            <div class="card">
                <h2><?php echo $totalIssues; ?></h2>
                <p>Class Rep Issues Reported</p>
            </div>
        </div>

        <div class="mt-5">
            <h4 class="mb-3">⚡ Quick Actions</h4>
            <div class="d-flex flex-wrap gap-3 quick-actions">
                <a href="add_lecturer.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Register New Lecturer
                </a>
                <a href="manage_lecturers.php" class="btn btn-primary">
                    <i class="bi bi-people"></i> View Lecturers
                </a>
                <a href="mark_attendance.php" class="btn btn-warning">
                    <i class="bi bi-clock-history"></i> Mark Attendance
                </a>
                <a href="timetable.php" class="btn btn-secondary text-white">
                    <i class="bi bi-calendar-week"></i> Manage Timetable
                </a>
                <a href="manage_courses.php" class="btn btn-info text-white">
                    <i class="bi bi-book"></i> Manage Courses
                </a>
                <a href="view_issues.php" class="btn btn-danger">
                    <i class="bi bi-exclamation-triangle"></i> Review Class Rep Issues
                </a>
            </div>
        </div>

        <footer>
            <p>© <?php echo date("Y"); ?> RCTI Attendance System — Managed by Admin Only</p>
        </footer>
    </div>
</body>
</html>
