<?php
session_start();
require_once 'db.php';

// ✅ Restrict to admin only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Check if department ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_departments.php");
    exit();
}

$dept_id = intval($_GET['id']);

// ✅ Fetch department details
$stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->bind_param("i", $dept_id);
$stmt->execute();
$dept = $stmt->get_result()->fetch_assoc();

if (!$dept) {
    echo "<div class='alert alert-danger text-center mt-3'>Department not found!</div>";
    exit();
}

// ✅ Handle update request
if (isset($_POST['update_department'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        // Prevent duplicate names
        $check = $conn->prepare("SELECT * FROM departments WHERE name = ? AND id != ?");
        $check->bind_param("si", $name, $dept_id);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows > 0) {
            $error = "A department with that name already exists!";
        } else {
            $update = $conn->prepare("UPDATE departments SET name = ? WHERE id = ?");
            $update->bind_param("si", $name, $dept_id);
            if ($update->execute()) {
                $success = "Department updated successfully!";
                // Refresh department info
                $stmt->execute();
                $dept = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Error updating department!";
            }
        }
    } else {
        $error = "Please enter a department name!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Department</h5>
            <a href="manage_departments.php" class="btn btn-light btn-sm">← Back</a>
        </div>

        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($dept['name']); ?>" required>
                </div>

                <button type="submit" name="update_department" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
