<?php
session_start();
require_once "db.php";

// Allow only logged-in users
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['lecturer', 'admin'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Fetch all courses
$coursesQuery = "
    SELECT c.id, c.name, c.code, c.description, d.name AS department_name
    FROM courses c
    LEFT JOIN departments d ON c.department_id = d.id
    ORDER BY c.name ASC
";
$courses = $conn->query($coursesQuery);

// Handle add course (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course']) && $user['role'] === 'admin') {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $description = trim($_POST['description']);
    $department_id = intval($_POST['department_id']);

    if ($name && $code) {
        $stmt = $conn->prepare("INSERT INTO courses (name, code, description, department_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $code, $description, $department_id);
        $stmt->execute();
        header("Location: view_courses.php?success=1");
        exit();
    }
}

// Fetch departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: "Segoe UI", sans-serif;
        }
        .container {
            margin-top: 70px;
        }
        .header-bar {
            background-color: #0d6efd;
            color: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
        }
        .btn-add {
            background-color: #198754;
            color: white;
        }
        .btn-add:hover {
            background-color: #157347;
        }
        .modal-content {
            border-radius: 15px;
        }
        footer {
            text-align: center;
            color: #888;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-bar mb-4">
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-book"></i> Manage Courses</h4>
        <?php if ($user['role'] === 'admin'): ?>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fa-solid fa-plus"></i> Add New Course
            </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> Course added successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm p-3">
        <h5><i class="fa-solid fa-list"></i> Available Courses</h5>
        <hr>
        <table class="table table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course Name</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses && $courses->num_rows > 0): ?>
                    <?php $i = 1; while ($row = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['code']) ?></td>
                            <td><?= htmlspecialchars($row['department_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['description'] ?: 'No description') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-muted">No courses found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Course Modal (Admin only) -->
    <?php if ($user['role'] === 'admin'): ?>
        <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addCourseModalLabel"><i class="fa fa-plus-circle"></i> Add New Course</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Object Oriented Programming" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. OOP101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Short description of the course..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php while ($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_course" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <p>© <?= date('Y') ?> RCTI Attendance System — Manage Courses</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
