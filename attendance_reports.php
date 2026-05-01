<?php
session_start();
require_once 'db.php';

// Ensure only one system admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$filter_name = $_GET['name'] ?? '';
$filter_from = $_GET['from'] ?? '';
$filter_to   = $_GET['to'] ?? '';

$query = "SELECT attendance.id, users.name AS lecturer_name, attendance.date, attendance.class_name,
                 attendance.unit_name, attendance.time_in, attendance.time_out, attendance.status, attendance.marked_by
          FROM attendance 
          LEFT JOIN users ON attendance.user_id = users.id 
          WHERE 1";

if (!empty($filter_name)) {
    $query .= " AND users.name LIKE '%" . $conn->real_escape_string($filter_name) . "%'";
}
if (!empty($filter_from) && !empty($filter_to)) {
    $query .= " AND attendance.date BETWEEN '" . $conn->real_escape_string($filter_from) . "' AND '" . $conn->real_escape_string($filter_to) . "'";
}

$query .= " ORDER BY attendance.date DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Reports | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2d9d6b72a.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-blue {
            background: #007bff;
            color: #fff;
            border-radius: 30px;
            padding: 8px 18px;
            text-decoration: none;
        }
        .btn-blue:hover {
            background: #0056b3;
        }
        .filter-form input {
            border-radius: 30px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .present { background: #d4edda; color: #155724; }
        .absent { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-bar">
        <h3><i class="fa-solid fa-chart-line text-primary"></i> Attendance Reports</h3>
        <div>
            <a href="admin_dashboard.php" class="btn-blue me-2"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <a href="export_attendance_pdf.php?name=<?= urlencode($filter_name) ?>&from=<?= urlencode($filter_from) ?>&to=<?= urlencode($filter_to) ?>" class="btn-blue"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form class="row g-2 filter-form" method="get" action="">
            <div class="col-md-4">
                <input type="text" name="name" value="<?= htmlspecialchars($filter_name) ?>" class="form-control" placeholder="Search by Lecturer Name">
            </div>
            <div class="col-md-3">
                <input type="date" name="from" value="<?= htmlspecialchars($filter_from) ?>" class="form-control">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lecturer</th>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Unit</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['lecturer_name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= htmlspecialchars($row['unit_name']) ?></td>
                        <td><?= htmlspecialchars($row['time_in']) ?></td>
                        <td><?= htmlspecialchars($row['time_out']) ?></td>
                        <td><span class="status-badge <?= strtolower($row['status']) == 'present' ? 'present' : 'absent' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td><?= htmlspecialchars($row['marked_by']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center mb-0">No attendance data found for selected filters.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
