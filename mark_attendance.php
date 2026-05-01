<?php
session_start();
require_once "db.php";

// Only Admins and Class Reps can access
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'class_rep'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$message = "";

// Fetch lecturers for dropdown
if ($user['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT id, name, department FROM users WHERE role='lecturer'");
    $stmt->execute();
    $lecturers = $stmt->get_result();
} else {
    // Get the Class Rep's department from class_reps table
    $cr_stmt = $conn->prepare("SELECT department, id AS class_rep_id FROM class_reps WHERE user_id=?");
    $cr_stmt->bind_param("i", $user['id']);
    $cr_stmt->execute();
    $cr_result = $cr_stmt->get_result();

    if ($cr_result->num_rows > 0) {
        $cr_row = $cr_result->fetch_assoc();
        $department = $cr_row['department'];
        $class_rep_id = $cr_row['class_rep_id'];

        // Fetch lecturers in this department
        $stmt = $conn->prepare("SELECT id, name, department FROM users WHERE role='lecturer' AND department=?");
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $lecturers = $stmt->get_result();
    } else {
        $lecturers = null;
        $message = "<div class='alert alert-warning'>⚠️ No department assigned for this class rep.</div>";
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_id = $_POST['lecturer_id'];
    $class_name = $_POST['class_name'];
    $unit_name = $_POST['unit_name'];
    $date = $_POST['date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $status = $_POST['status'];

    // Who is marking
    $marked_by = $user['name'] . " (" . ucfirst($user['role']) . ")";

    // For class_rep, use their class_rep_id; for admin, set null
    $cr_id = $user['role'] === 'class_rep' ? $class_rep_id : null;

    $insert = $conn->prepare("
        INSERT INTO attendance (user_id, class_rep_id, date, class_name, unit_name, time_in, time_out, status, marked_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param("iisssssss", $lecturer_id, $cr_id, $date, $class_name, $unit_name, $time_in, $time_out, $status, $marked_by);

    if ($insert->execute()) {
        $message = "<div class='alert alert-success'>✅ Attendance marked successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Failed to mark attendance: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mark Attendance | RCTI Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #10063dff, #060325ff); color:#fff; font-family:'Poppins',sans-serif; min-height:100vh;}
.form-container { max-width:700px; margin:60px auto; background:rgba(173, 146, 146, 0.07); padding:35px; border-radius:18px; box-shadow:0 8px 30px rgba(0,0,0,0.35);}
.form-title { color:#00ff99; font-weight:600; margin-bottom:25px;}
label { color:#ccc; font-weight:500;}
.form-control, .form-select { background:rgba(18, 18, 19, 0.08); color:#fff; border:1px solid rgba(41, 6, 80, 0.2);}
.btn-success { background:#00ff99; border:none; color:#000; font-weight:600;}
.btn-success:hover { background:#00cc7a;}
a.back-link { color:#00ff99; text-decoration:none; display:inline-block; margin-bottom:15px;}
a.back-link:hover { text-decoration:underline;}
</style>
</head>
<body>
<div class="form-container">
<a href="<?= $user['role']==='admin'?'admin_dashboard.php':'classrep_dashboard.php'; ?>" class="back-link">
<i class="bi bi-arrow-left-circle"></i> Back to Dashboard
</a>
<h3 class="form-title"><i class="bi bi-check2-square"></i> Mark Lecturer Attendance</h3>

<?= $message; ?>

<form method="POST">
    <div class="mb-3">
        <label for="lecturer_id" class="form-label">Select Lecturer</label>
        <select class="form-select" name="lecturer_id" required>
            <option value="">-- Choose Lecturer --</option>
            <?php if ($lecturers && $lecturers->num_rows > 0): ?>
                <?php while ($row = $lecturers->fetch_assoc()): ?>
                    <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['name']); ?> (<?= htmlspecialchars($row['department']); ?>)</option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No lecturers found in your department</option>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Class Name</label>
        <input type="text" name="class_name" class="form-control" value="<?= htmlspecialchars($department ?? 'General'); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Name</label>
        <input type="text" name="unit_name" class="form-control" placeholder="e.g., Advanced Surveying" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" required>
    </div>

    <div class="row mb-3">
        <div class="col">
            <label class="form-label">Time In</label>
            <input type="time" name="time_in" class="form-control" required>
        </div>
        <div class="col">
            <label class="form-label">Time Out</label>
            <input type="time" name="time_out" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="">-- Select Status --</option>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
            <option value="Late">Late</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success w-100 py-2 mt-3">Submit Attendance</button>
</form>
</div>
</body>
</html>
