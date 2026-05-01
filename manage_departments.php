<?php
session_start();
require_once 'db.php';

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Add new department
if (isset($_POST['add_department'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ Department added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Please enter a department name!</div>";
    }
}

// Delete department
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM departments WHERE id = $id");
    header("Location: manage_departments.php");
    exit;
}

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Departments | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-top: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h3 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background: #0d6efd;
            color: white;
        }
        .btn-add {
            background: #0d6efd;
            color: white;
        }
        .btn-add:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
<div class="container">
    <h3>🏫 Manage Departments</h3>
    <?= $message ?>

    <form method="POST" class="d-flex mb-3">
        <input type="text" name="name" class="form-control me-2" placeholder="Enter department name" required>
        <button type="submit" name="add_department" class="btn btn-add">Add Department</button>
    </form>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Department Name</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($departments->num_rows > 0): ?>
            <?php $i = 1; while ($row = $departments->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <a href="edit_department.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this department?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3" class="text-center text-muted">No departments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="text-center mt-3">
        <a href="admin_dashboard.php" class="btn btn-link">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
