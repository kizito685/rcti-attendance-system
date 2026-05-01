<?php
session_start();
require_once 'db.php';

// Restrict access to admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle lecturer deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id AND role = 'lecturer'");
    header("Location: manage_lecturers.php");
    exit();
}

// Fetch all lecturers
$result = $conn->query("SELECT * FROM users WHERE role = 'lecturer' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lecturers | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f8;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            background: #fff;
        }
        .table thead {
            background: #198754;
            color: white;
        }
        .btn-edit {
            background-color: #0d6efd;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-add {
            background-color: #198754;
            color: white;
        }
        .profile-pic {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        .header {
            color: #198754;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="header">👨‍🏫 Manage Lecturers</h3>
            <a href="add_lecturer.php" class="btn btn-add">➕ Add Lecturer</a>
        </div>

        <table class="table table-bordered table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        $profile = !empty($row['profile_pic']) ? $row['profile_pic'] : 'default.png';
                        echo "<tr>
                                <td>{$i}</td>
                                <td><img src='uploads/{$profile}' class='profile-pic'></td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['department']}</td>
                                <td>{$row['password']}</td>
                                <td>
                                    <a href='edit_lecturer.php?id={$row['id']}' class='btn btn-sm btn-edit me-2'>✏ Edit</a>
                                    <a href='?delete={$row['id']}' class='btn btn-sm btn-delete' onclick=\"return confirm('Are you sure you want to delete this lecturer?');\">🗑 Delete</a>
                                </td>
                              </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-muted'>No lecturers found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="text-center mt-3">
            <a href="admin_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
