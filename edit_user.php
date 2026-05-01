<?php
session_start();
require_once 'db.php';

// Allow only admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

// Ensure we have an ID in the URL
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    die("<h3 style='color:white;text-align:center;margin-top:40px;'>⚠️ User not found!</h3>");
}

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $department = trim($_POST['department']);
    $password = trim($_POST['password']);

    if ($name && $email && $role) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=?, department=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $email, $hashed, $role, $department, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, department=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $role, $department, $user_id);
        }

        if ($stmt->execute()) {
            $success = "✅ User updated successfully!";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "❌ Failed to update user. Please try again.";
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
    <title>Edit User | RCTI Attendance System</title>
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
        <h2 class="text-center mb-4"><i class="bi bi-pencil-square"></i> Edit User</h2>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin" <?php if ($userData['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="lecturer" <?php if ($userData['role'] === 'lecturer') echo 'selected'; ?>>Lecturer</option>
                    <option value="class_rep" <?php if ($userData['role'] === 'class_rep') echo 'selected'; ?>>Class Representative</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" value="<?php echo htmlspecialchars($userData['department']); ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password (optional)</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>

            <button type="submit" class="btn btn-custom w-100 py-2">
                <i class="bi bi-check-circle"></i> Update User
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="manage_users.php"><i class="bi bi-arrow-left-circle"></i> Back to Manage Users</a>
        </div>
    </div>
</body>
</html>
