<?php
session_start();
require_once 'db.php';

// Allow only admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 🟦 Fetch all attendance records, including lecturer and class rep names
$sql = "
    SELECT 
        a.id,
        a.date,
        a.class_name,
        a.unit_name,
        a.time_in,
        a.time_out,
        a.status,
        a.marked_by,
        u.name AS lecturer_name,
        cr.name AS class_rep_name
    FROM attendance a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN users cr ON a.class_rep_id = cr.id
    ORDER BY a.date DESC, a.id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Records | Admin Dashboard</title>
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
        .table th {
            background: #007bff;
            color: #fff;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-back {
            background: #007bff;
            color: #fff;
            border-radius: 30px;
            padding: 8px 18px;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #0056b3;
            color: #fff;
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
        <h3><i class="fa-solid fa-clipboard-check text-primary"></i> Attendance Records</h3>
        <a href="admin_dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back</a>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['lecturer_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= htmlspecialchars($row['unit_name']) ?></td>
                        <td><?= htmlspecialchars($row['time_in']) ?></td>
                        <td><?= htmlspecialchars($row['time_out']) ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($row['status']) == 'present' ? 'present' : 'absent' ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                                if ($row['marked_by'] === 'Admin') {
                                    echo "<span class='badge bg-primary'>Admin</span>";
                                } elseif ($row['marked_by'] === 'Class_rep') {
                                    echo htmlspecialchars($row['class_rep_name'] ?? 'Class Rep');
                                } else {
                                    echo "<span class='text-muted'>Unknown</span>";
                                }
                            ?>
                        </td>
                        <td>
                            <a href="delete_attendance.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this attendance record?')">
                               <i class="fa-solid fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center mb-0">No attendance records found.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
