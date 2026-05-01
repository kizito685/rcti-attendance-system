<?php
session_start();
require_once 'db.php';

// ✅ Ensure ID is provided
if (!isset($_GET['id'])) {
    die("Lecturer ID missing!");
}

$id = intval($_GET['id']);

// ✅ Fetch lecturer record
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lecturer not found!");
}

$lecture = $result->fetch_assoc();

// ✅ Fetch department list from departments table
$departments = $conn->query("SELECT id, name FROM departments");

// ✅ Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $department_id = intval($_POST['department']); // department ID

    // Get department name from departments table
    $dept_stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
    $dept_stmt->bind_param("i", $department_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    $dept_row = $dept_result->fetch_assoc();
    $department_name = $dept_row['name'] ?? '';

    // ✅ Update record
    $update = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=?, department=? WHERE id=?");
    $update->bind_param("sssssi", $name, $email, $password, $role, $department_name, $id);

    if ($update->execute()) {
        echo "<script>alert('Lecturer updated successfully!'); window.location='lecturer_dashboard.php';</script>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error updating lecturer: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lecturer | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/rcmrd-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .edit-container {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-top: 60px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        }
        .form-label {
            color: #00ff99;
            font-weight: 600;
        }
        .btn-success {
            background-color: #00c97f;
            border: none;
        }
        .btn-success:hover {
            background-color: #00ff99;
        }
    </style>
</head>
<body>
<div class="container col-md-6">
    <div class="edit-container shadow">
        <h3 class="text-center mb-4">✏️ Edit Lecturer Details</h3>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($lecture['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($lecture['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="text" name="password" class="form-control" 
                       value="<?= htmlspecialchars($lecture['password']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin" <?= $lecture['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="lecturer" <?= $lecture['role'] == 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                    <option value="student" <?= $lecture['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="hod" <?= $lecture['role'] == 'hod' ? 'selected' : ''; ?>>Head of Department</option>
                    <option value="class_rep" <?= $lecture['role'] == 'class_rep' ? 'selected' : ''; ?>>Class Rep</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-select" required>
                    <option value="">-- Select Department --</option>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?= $dept['id']; ?>" 
                            <?= (strcasecmp(trim($lecture['department']), trim($dept['name'])) == 0) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="lecturer_dashboard.php" class="btn btn-secondary">⬅ Back</a>
                <button type="submit" class="btn btn-success">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
