<?php
session_start();
require_once "db.php";

// ✅ Restrict access to class reps only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'class_rep') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$department = $user['department'];

// ✅ Fetch attendance stats for this class rep
$stats_query = $conn->prepare("
    SELECT 
        COUNT(*) AS total_marked,
        MAX(date) AS last_marked
    FROM attendance
    WHERE marked_by = ?
");
$stats_query->bind_param("i", $user['id']);
$stats_query->execute();
$stats_result = $stats_query->get_result()->fetch_assoc();

$total_marked = $stats_result['total_marked'] ?? 0;
$last_marked = $stats_result['last_marked'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Rep Dashboard | RCTI Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c1f26, #283044);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        .dashboard-container {
            max-width: 1150px;
            margin: 60px auto;
            padding: 35px;
            background: rgba(255,255,255,0.07);
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.35);
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-bar h3 {
            font-weight: 600;
            color: #00ff99;
        }
        .user-info {
            text-align: right;
        }
        .user-info span {
            display: block;
            font-size: 0.9em;
            color: #ccc;
        }
        .card {
            background: rgba(255,255,255,0.08);
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .card:hover {
            transform: translateY(-6px);
            background: rgba(0,255,153,0.12);
        }
        .card i {
            font-size: 2.5rem;
            color: #00ff99;
        }
        .card-title {
            margin-top: 15px;
            font-weight: 600;
        }
        .stats-box {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .stats-box span {
            color: #00ff99;
            font-weight: 600;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #aaa;
            font-size: 0.9em;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header-bar">
            <h3>🎓 Class Representative Dashboard</h3>
            <div class="user-info">
                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
                <span>Department: <?php echo htmlspecialchars($department ?: 'N/A'); ?></span>
            </div>
        </div>

        <!-- ✅ Department Stats -->
        <div class="stats-box">
            <p><strong>Department:</strong> <span><?php echo htmlspecialchars($department); ?></span></p>
            <p><strong>Total Attendance Marked:</strong> <span><?php echo $total_marked; ?></span></p>
            <p><strong>Last Attendance Marked:</strong> <span><?php echo htmlspecialchars($last_marked); ?></span></p>
        </div>

        <!-- ✅ Actions -->
        <div class="row g-4">
            <div class="col-md-4">
                <a href="mark_attendance.php">
                    <div class="card p-4 text-center">
                        <i class="bi bi-check2-square"></i>
                        <h5 class="card-title">Mark Attendance</h5>
                        <p class="text-muted">Record lecturer attendance for your class</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="report_issue.php">
                    <div class="card p-4 text-center">
                        <i class="bi bi-exclamation-circle"></i>
                        <h5 class="card-title">Report Issue</h5>
                        <p class="text-muted">Report missing or incorrect attendance</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="logout.php" class="btn btn-danger px-4 py-2 rounded-3">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <footer>© <?php echo date('Y'); ?> RCTI Attendance System</footer>
</body>
</html>
