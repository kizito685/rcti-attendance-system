<?php
session_start();
require_once 'db.php';

// Allow only the system admin to access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch all departments from database
$departments = [];
$dept_query = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
if ($dept_query && $dept_query->num_rows > 0) {
    while ($row = $dept_query->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Handle lecturer addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $department = $_POST['department'];
    $role = 'lecturer';

    // Handle profile picture upload
    $profilePic = null;
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
        $targetFilePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
            $profilePic = $fileName;
        }
    }

    // Insert new lecturer into users table
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, department, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $email, $password, $role, $department, $profilePic);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ Lecturer added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error adding lecturer: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>❌ SQL Error: " . htmlspecialchars($conn->error) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lecturer | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1605379399642-870262d3d051?auto=format&fit=crop&w=1400&q=80') no-repeat center center fixed;
            background-size: cover;
            font-family: "Poppins", sans-serif;
        }
        .overlay {
            background: rgba(0, 0, 0, 0.65);
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            max-width: 650px;
            background: rgba(255, 255, 255, 0.95);
            margin-top: 70px;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.2);
        }
        .title {
            text-align: center;
            font-weight: 600;
            margin-bottom: 25px;
            color: #0d6efd;
        }
        .btn-custom {
            background: #0d6efd;
            color: white;
            width: 100%;
            font-weight: 500;
            border: none;
        }
        .btn-custom:hover {
            background: #0b5ed7;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="container">
    <h3 class="title">Add New Lecturer</h3>
    <?= $message ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter lecturer full name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department" class="form-select" required>
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['name']) ?>">
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Set Password</label>
            <input type="text" name="password" class="form-control" placeholder="Temporary password" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_pic" class="form-control">
        </div>

        <button type="submit" class="btn btn-custom">Add Lecturer</button>
    </form>

    <div class="back-link">
        <a href="admin_dashboard.php" class="btn btn-link text-decoration-none">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
