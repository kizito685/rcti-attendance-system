<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .access-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
            width: 400px;
        }
        .access-card h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .access-card p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        .btn-custom {
            background-color: white;
            color: #0d6efd;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background-color: #0d6efd;
            color: white;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="access-card">
        <h1>🚫</h1>
        <h3>Access Denied</h3>
        <p>Sorry <strong><?php echo htmlspecialchars($user['name']); ?></strong>, you don't have permission to view this page.</p>
        <a href="lecturer_dashboard.php" class="btn btn-custom">Go Back</a>
    </div>

</body>
</html>
