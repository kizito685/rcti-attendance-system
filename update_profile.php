<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$message = "";

// Create uploads folder if missing
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = !empty($_POST['password']) ? $conn->real_escape_string($_POST['password']) : $user['password'];

    // Handle profile picture
    $profile_pic = $user['profile_pic'] ?? '';

    if (!empty($_FILES['profile_pic']['name'])) {
        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetFile = "uploads/" . $fileName;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
            $profile_pic = $targetFile;
        }
    }

    // Update user data
    $sql = "UPDATE users SET name='$name', email='$email', password='$password', profile_pic='$profile_pic' WHERE id={$user['id']}";
    if ($conn->query($sql)) {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['password'] = $password;
        $_SESSION['user']['profile_pic'] = $profile_pic;
        $message = "<div class='alert alert-success'>✅ Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Profile | RCMRD Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #e3f2fd, #f1f8e9);
    font-family: 'Segoe UI', sans-serif;
}
.profile-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 3px 12px rgba(0,0,0,0.15);
    max-width: 600px;
    margin: 70px auto;
}
.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #00796b;
    margin-bottom: 10px;
}
.footer {
    text-align: center;
    margin-top: 50px;
    font-size: 14px;
    color: #555;
}
.footer a { color: #00796b; text-decoration: none; }
</style>
</head>
<body>

<div class="profile-card text-center">
    <h3 class="text-success mb-3">Update Profile</h3>
    <?= $message ?>
    <img src="<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'uploads/default.png' ?>" 
         alt="Profile Picture" class="profile-pic">

    <form method="POST" enctype="multipart/form-data" class="text-start mt-3">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password">
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_pic" class="form-control">
        </div>
        <button type="submit" class="btn btn-success w-100">Update Profile</button>
    </form>
</div>

<div class="footer">
    <p>RCMRD Attendance System © 2025 | All rights reserved</p>
    <p><a href="lecturer_dashboard.php">Back to Dashboard</a> | <a href="#">Help</a> | <a href="#">Privacy</a></p>
    <p>Developed by <strong>FELIX MULI</strong> | RCMRD</p>
</div>

</body>
</html>
