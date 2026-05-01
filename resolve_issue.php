<?php
session_start();
require_once "db.php";

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$alertClass = '';

if (isset($_GET['id'])) {
    $issue_id = intval($_GET['id']);

    // Verify issue exists
    $stmt = $conn->prepare("SELECT * FROM issues WHERE id = ?");
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $update = $conn->prepare("UPDATE issues SET status = 'Resolved' WHERE id = ?");
        $update->bind_param("i", $issue_id);

        if ($update->execute()) {
            $message = "✅ Issue #{$issue_id} has been marked as resolved.";
            $alertClass = "success";
        } else {
            $message = "⚠️ Failed to update issue status.";
            $alertClass = "danger";
        }

        $update->close();
    } else {
        $message = "❌ Issue not found.";
        $alertClass = "warning";
    }

    $stmt->close();
} else {
    $message = "⚠️ Invalid request — no issue ID provided.";
    $alertClass = "danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resolve Issue | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0b0f19;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .message-box {
            background: #12172b;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,255,153,0.1);
            width: 450px;
            text-align: center;
        }
        .message-box h3 {
            color: #00ff99;
            margin-bottom: 20px;
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #00ff99, #008f5a);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #00e68a, #006f45);
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h3>🛠 Issue Resolution</h3>
        <div class="alert alert-<?php echo $alertClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <a href="view_issues.php" class="btn btn-primary mt-3">← Back to Issues</a>
    </div>
</body>
</html>
