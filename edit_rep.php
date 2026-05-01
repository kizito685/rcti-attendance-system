<?php
require_once "db.php";

// ✅ Ensure a valid rep ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$rep_id = intval($_GET['id']);

// ✅ Fetch the class rep details
$sql = "SELECT cr.*, u.name AS user_name, u.id AS user_id, d.name AS dept_name 
        FROM class_reps cr
        JOIN users u ON cr.user_id = u.id
        LEFT JOIN departments d ON cr.department = d.id
        WHERE cr.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rep_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Class representative not found.");
}

$rep = $result->fetch_assoc();

// ✅ Fetch all available departments
$departments = $conn->query("SELECT id, name FROM departments");

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = intval($_POST['department']);

    // Update the class_reps table
    $update = $conn->prepare("UPDATE class_reps SET department = ? WHERE id = ?");
    $update->bind_param("ii", $department_id, $rep_id);
    if ($update->execute()) {
        header("Location: manage_reps.php?msg=Class rep updated successfully");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating class rep: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class Representative</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #10121b;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 80px;
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.5);
        }
        label { color: #00ff99; font-weight: 600; }
        .btn-success { background-color: #00c97f; border: none; }
        .btn-success:hover { background-color: #00ff99; }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center mb-4">Edit Class Representative</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" class="form-control" 
                   value="<?php echo htmlspecialchars($rep['user_name']); ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Department:</label>
            <input type="text" class="form-control" 
                   value="<?php echo htmlspecialchars($rep['dept_name'] ?? 'Not Assigned'); ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Select New Department:</label>
            <select name="department" class="form-select" required>
                <option value="">-- Choose Department --</option>
                <?php while ($d = $departments->fetch_assoc()): ?>
                    <option value="<?= $d['id']; ?>" <?= ($d['id'] == $rep['department']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($d['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success px-4">Update</button>
            <a href="manage_reps.php" class="btn btn-light px-4">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
