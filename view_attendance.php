<?php
session_start();
require_once "db.php";

// Restrict access to class rep only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'class_rep') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Filter logic
$filter_date = $_GET['date'] ?? '';
$filter_class = $_GET['class'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT a.*, u.name AS lecturer_name 
          FROM attendance a 
          JOIN users u ON a.user_id = u.id 
          WHERE 1";

if ($filter_date) $query .= " AND a.date = '$filter_date'";
if ($filter_class) $query .= " AND a.class_name LIKE '%$filter_class%'";
if ($filter_status) $query .= " AND a.status = '$filter_status'";

$query .= " ORDER BY a.date DESC";
$records = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Attendance Records | RCTI Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c1f26, #283044);
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            background: rgba(255,255,255,0.08);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            margin-top: 60px;
        }
        h2 {
            color: #00ff99;
            text-align: center;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .table {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }
        .table th {
            background: rgba(0,255,153,0.15);
        }
        .filter-bar {
            margin-bottom: 20px;
        }
        .filter-bar input, .filter-bar select {
            border-radius: 8px;
        }
        a {
            text-decoration: none;
            color: #00ff99;
        }
        a:hover {
            text-decoration: underline;
        }
        .status-present {
            color: #00ff99;
            font-weight: 600;
        }
        .status-absent {
            color: #ff6b6b;
            font-weight: 600;
        }
        footer {
            text-align: center;
            color: #aaa;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Lecturer Attendance Records</h2>
        <a href="classrep_dashboard.php" class="text-light">&larr; Back to Dashboard</a>
    </div>

    <form method="GET" class="filter-bar row g-3">
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="class" class="form-control" placeholder="Class Name" value="<?= htmlspecialchars($filter_class) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="Present" <?= $filter_status === 'Present' ? 'selected' : '' ?>>Present</option>
                <option value="Absent" <?= $filter_status === 'Absent' ? 'selected' : '' ?>>Absent</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-success"><i class="bi bi-funnel"></i> Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Lecturer</th>
                    <th>Class</th>
                    <th>Unit</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Marked By</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records->num_rows > 0): 
                    $count = 1;
                    while ($row = $records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['lecturer_name']) ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['unit_name']) ?></td>
                            <td><?= $row['time_in'] ?: '<i class="text-muted">-</i>' ?></td>
                            <td><?= $row['time_out'] ?: '<i class="text-muted">-</i>' ?></td>
                            <td class="<?= strtolower($row['status']) === 'present' ? 'status-present' : 'status-absent' ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['marked_by']) ?></td>
                        </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No attendance records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>© <?= date('Y') ?> RCTI Attendance System</footer>
</body>
</html>
