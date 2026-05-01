<?php
session_start();
require_once "db.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $staff_id = trim($_POST['id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'lecturer');

    // Check if email or ID already exists
    $checkSql = "SELECT * FROM users WHERE email = ? OR id = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ss", $email, $staff_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck && $resultCheck->num_rows > 0) {
        $error = "Email or ID already registered!";
    } else {
        // Insert new user
        $insertSql = "INSERT INTO users (name, id, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("ssssss", $name, $staff_id, $email, $department, $password, $role);

        if ($stmtInsert->execute()) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RCMRD Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: url('https://www.rcmrd.org/images/2020/09/15/rcmrd_building.jpg') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.overlay {
    background: rgba(0,0,0,0.65);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.register-card {
    background: #fff;
    border-radius: 15px;
    padding: 40px 30px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.register-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.5);
}
.register-card h3 {
    font-weight: 600;
    margin-bottom: 25px;
    color: #333;
}
.form-control:focus {
    box-shadow: none;
    border-color: #0d6efd;
}
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: 500;
    padding: 10px;
    border-radius: 8px;
}
.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}
.alert { font-size: 0.95rem; }
.position-relative { position: relative; }
.toggle-password { cursor: pointer; position: absolute; top: 38px; right: 15px; font-size: 0.9rem; color: #555; }
</style>
</head>
<body>
<div class="overlay">
    <div class="register-card">
        <h3 class="text-center">RCMRD Registration</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" name="id" class="form-control" placeholder="Enter your staff/ID" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" placeholder="Enter department" required>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                <span class="toggle-password" onclick="togglePassword()">Show</span>
            </div>
            <div class="mb-4">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="lecturer" selected>Lecturer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="mt-3 text-center">
            Already have an account? <a href="login.php">Login here</a>
        </p>

        <p class="mt-1 text-center text-muted" style="font-size: 0.9rem;">
            &copy; <?php echo date("Y"); ?> RCMRD. All rights reserved.
        </p>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleText = document.querySelector('.toggle-password');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleText.textContent = 'Hide';
    } else {
        passwordField.type = 'password';
        toggleText.textContent = 'Show';
    }
}
</script>
</body>
</html>
