<?php
session_start();
require_once "db.php";

// Protect page: only logged-in users (Admin or Class Rep)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];

// Fetch all reported issues
$query = "SELECT * FROM issues ORDER BY date_reported DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Reported Issues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: "Poppins", sans-serif;
        }
        .navbar {
            background-color: #0d6efd;
            color: white;
        }
        .navbar-brand {
            color: white;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background: white;
        }
        .table thead {
            background-color: #0d6efd;
            color: white;
            font-size: 0.95rem;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .badge {
            font-size: 0.85rem;
            padding: 6px 10px;
        }
        .container {
            margin-top: 60px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h3 {
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
    <a class="navbar-brand" href="#">
        📋 RCTI Attendance System
    </a>
    <div class="ms-auto">
        <span class="me-3"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($role) ?>)</span>
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card p-4">
        <div class="header mb-4">
            <h3>Reported Issues</h3>
            <a href="<?= $role === 'admin' ? 'admin_dashboard.php' : 'classrep_dashboard.php' ?>" 
               class="btn btn-secondary btn-sm">← Back</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Issue Type</th>
                            <th>Description</th>
                            <th>Class</th>
                            <th>Unit</th>
                            <th>Reported By</th>
                            <th>Date Reported</th>
                            <th>Status</th>
                            <?php if ($role === 'admin'): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['issue_type']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($row['unit_name']) ?></td>
                                <td><?= htmlspecialchars($row['reported_by']) ?></td>
                                <td><?= htmlspecialchars($row['date_reported']) ?></td>
                                <td>
                                    <?php if (strtolower($row['status']) === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Resolved</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <?php if (strtolower($row['status']) === 'pending'): ?>
                                            <a href="resolve_issue.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                                Mark as Resolved
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Done</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No issues found.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
