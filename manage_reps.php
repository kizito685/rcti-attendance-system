<?php
session_start();
require_once 'db.php';

// Restrict access (Admin only)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// =====================
//  FETCH DATA
// =====================
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$class_reps = $conn->query("
    SELECT cr.id AS rep_id, u.name AS student_name, u.email, u.department, cr.assigned_at
    FROM class_reps cr
    JOIN users u ON cr.user_id = u.id
    ORDER BY cr.assigned_at DESC
");

// =====================
//  ADD CLASS REP HANDLER
// =====================
if (isset($_POST['add_rep'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'class_rep';
    $assigned_at = date('Y-m-d H:i:s');

    // ✅ Check for duplicate rep per department
    $check_rep = $conn->prepare("SELECT id FROM class_reps WHERE department = ?");
    $check_rep->bind_param("s", $department);
    $check_rep->execute();
    $check_result = $check_rep->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('⚠️ This department already has a Class Representative!'); window.location='manage_reps.php';</script>";
        exit();
    }

    // ✅ Create user first
    $insert_user = $conn->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
    $insert_user->bind_param("sssss", $name, $email, $password, $role, $department);

    if ($insert_user->execute()) {
        $user_id = $insert_user->insert_id;

        // ✅ Add to class_reps table
        $insert_rep = $conn->prepare("INSERT INTO class_reps (user_id, department, assigned_at) VALUES (?, ?, ?)");
        $insert_rep->bind_param("iss", $user_id, $department, $assigned_at);
        $insert_rep->execute();

        echo "<script>alert('✅ New Class Representative added successfully!'); window.location='manage_reps.php';</script>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . $conn->error . "</div>";
    }
}

// =====================
//  REMOVE CLASS REP HANDLER
// =====================
if (isset($_GET['remove'])) {
    $rep_id = intval($_GET['remove']);

    // Get user_id first
    $get_user = $conn->prepare("SELECT user_id FROM class_reps WHERE id = ?");
    $get_user->bind_param("i", $rep_id);
    $get_user->execute();
    $result = $get_user->get_result();

    if ($result->num_rows > 0) {
        $user_id = $result->fetch_assoc()['user_id'];

        // Delete from both tables
        $conn->query("DELETE FROM class_reps WHERE id = $rep_id");
        $conn->query("DELETE FROM users WHERE id = $user_id");

        echo "<script>alert('🗑️ Class Rep removed successfully!'); window.location='manage_reps.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Class Representatives | RCTI Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: url('images/rcmrd-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .table-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 25px;
            border-radius: 15px;
            margin-top: 60px;
        }
        .modal-content {
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            border-radius: 10px;
        }
        .btn-close-white {
            filter: invert(1);
        }
        th, td {
            vertical-align: middle !important;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="table-container shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-people-fill"></i> Manage Class Representatives</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRepModal">
                <i class="bi bi-person-plus"></i> Add New Class Rep
            </button>
        </div>

        <table class="table table-dark table-hover table-bordered align-middle text-center">
            <thead class="table-success text-dark">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Assigned At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($class_reps && $class_reps->num_rows > 0): $count = 1; ?>
                    <?php while ($rep = $class_reps->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($rep['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($rep['email']); ?></td>
                            <td><?php echo htmlspecialchars($rep['department']); ?></td>
                            <td><?php echo htmlspecialchars($rep['assigned_at']); ?></td>
                            <td>
                                <a href="edit_rep.php?id=<?php echo $rep['rep_id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="?remove=<?php echo $rep['rep_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this rep?');">
                                    <i class="bi bi-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No class representatives found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD NEW CLASS REP MODAL -->
<div class="modal fade" id="addRepModal" tabindex="-1" aria-labelledby="addRepLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="addRepLabel"><i class="bi bi-person-plus"></i> Add New Class Representative</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter student email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Department</label>
                    <select name="department" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        <?php
                        $dept_query = $conn->query("SELECT * FROM departments ORDER BY name ASC");
                        if ($dept_query && $dept_query->num_rows > 0):
                            while ($dept = $dept_query->fetch_assoc()):
                        ?>
                            <option value="<?php echo htmlspecialchars($dept['name']); ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="add_rep" class="btn btn-success">💾 Add Class Rep</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
