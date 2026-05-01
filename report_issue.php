<?php
session_start();
require_once "db.php";

// Allow only logged-in users
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $issue_type = $_POST['issue_type'];
    $description = $_POST['description'];
    $class_name = $_POST['class_name'];
    $unit_name = $_POST['unit_name'];
    $reported_by = $user['name'];
    $date_reported = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO issues (issue_type, description, class_name, unit_name, reported_by, date_reported, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssss", $issue_type, $description, $class_name, $unit_name, $reported_by, $date_reported);

    if ($stmt->execute()) {
        echo "<script>alert('Issue reported successfully! The admin will review it soon.');</script>";
    } else {
        echo "<script>alert('Error submitting issue. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Attendance Issue | RCTI Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1c1f26, #283044);
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 60px;
            background: rgba(255,255,255,0.08);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            max-width: 700px;
        }
        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 25px;
        }
        label {
            font-weight: 500;
        }
        .btn-submit {
            background: #0d6efd;
            border: none;
            color: #fff;
            padding: 10px 25px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }
        textarea {
            resize: none;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .header-bar a {
            color: white;
            text-decoration: none;
        }
        .header-bar a:hover {
            text-decoration: underline;
        }
        .form-select, .form-control {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h2><i class="bi bi-exclamation-circle"></i> Report Attendance Issue</h2>
            <a href="classrep_dashboard.php">&larr; Back to Dashboard</a>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label for="issue_type">Issue Type</label>
                <select name="issue_type" id="issue_type" class="form-select" required>
                    <option value="">Select issue type</option>
                    <option value="Missing Attendance">Missing Attendance</option>
                    <option value="Wrong Mark">Wrong Mark (Marked Absent by mistake)</option>
                    <option value="Technical Problem">Technical Problem</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Class Name</label>
                    <input type="text" name="class_name" class="form-control" required placeholder="e.g. B5">
                </div>
                <div class="col-md-6">
                    <label>Unit Name</label>
                    <input type="text" name="unit_name" class="form-control" required placeholder="e.g. OOP">
                </div>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" rows="4" class="form-control" placeholder="Explain what happened..." required></textarea>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn-submit">📨 Submit Issue</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>
</html>
