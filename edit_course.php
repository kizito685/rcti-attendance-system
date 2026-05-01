<?php
session_start();
require_once 'db.php';

// ✅ Allow only Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ✅ Check if course ID provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_courses.php");
    exit;
}

$course_id = intval($_GET['id']);

// ✅ Fetch course data
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    echo "<script>alert('Course not found!'); window.location='view_courses.php';</script>";
    exit;
}

// ✅ Fetch departments for dropdown
$departments = $conn->query("SELECT id, name FROM departments");

// ✅ Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $description = trim($_POST['description']);
    $department_id = intval($_POST['department_id']);

    if ($name && $code && $department_id) {
        $update = $conn->prepare("UPDATE courses SET name=?, code=?, description=?, department_id=? WHERE id=?");
        $update->bind_param("sssii", $name, $code, $description, $department_id, $course_id);
        if ($update->execute()) {
            echo "<script>alert('✅ Course updated successfully!'); window.location='view_courses.php';</script>";
            exit;
        } else {
            $error = "Error updating course. Please try again.";
        }
    } else {
        $error = "All required fields must be filled.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 650px;
            background: #12172b;
            border-radius: 15px;
            padding: 35px;
            margin-top: 60px;
            box-shadow: 0 0 15px rgba(0, 255, 153, 0.15);
        }
        .form-label {
            color: #00ff99;
        }
        .btn-success {
            background-color: #00c37e;
            border: none;
        }
        .btn-success:hover {
            background-color: #00ff99;
            color: #000;
        }
        .btn-secondary {
            background-color: #1b203d;
            border: none;
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #2a3258;
        }
        .page-title {
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
            color: #00ff99;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="page-title"><i class="bi bi-pencil-square"></i> Edit Course Details</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Course Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($course['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Course Code</label>
                <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($course['code']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter course description..."><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select" required>
                    <option value="">-- Select Department --</option>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php if ($dept['id'] == $course['department_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="manage_courses.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update Course</button>
            </div>
        </form>
    </div>
</body>
</html>
