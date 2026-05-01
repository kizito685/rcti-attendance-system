<?php
session_start();
require_once 'db.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $department = trim($_POST['department']);

    if ($name && $email && $password && $role) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed, $role, $department);

        if ($stmt->execute()) {
            $success = "✅ User added successfully!";
        } else {
            $error = "❌ Failed to add user. Please try again.";
        }
    } else {
        $error = "⚠️ Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .container {
            max-width: 700px;
            background: #12172b;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 255, 153, 0.1);
            margin-top: 60px;
        }
        .btn-custom {
            background: #00ff99;
            color: #000;
            border: none;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: #00cc7a;
            color: #fff;
        }
        a {
            color: #00ff99;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4"><i class="bi bi-person-plus"></i> Add New User</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="class_rep">Class Representative</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" placeholder="Enter department (optional)">
            </div>

            <button type="submit" class="btn btn-custom w-100 py-2"><i class="bi bi-check-circle"></i> Add User</button>
        </form>

        <div class="text-center mt-4">
            <a href="manage_users.php"><i class="bi bi-arrow-left-circle"></i> Back to Manage Users</a>
        </div>
    </div>
</body>
</html>
