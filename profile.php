<?php
session_start();
require_once 'db.php';

// Ensure lecturer is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$lecturer = $_SESSION['user'];
$profilePicPath = !empty($lecturer['profile_pic']) ? 'uploads/' . $lecturer['profile_pic'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Lecturer Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/rcmrd-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .profile-card {
            background: rgba(0, 0, 0, 0.85);
            border-radius: 15px;
            max-width: 700px;
            margin: 70px auto;
            padding: 35px;
            box-shadow: 0 0 25px rgba(0,0,0,0.6);
            text-align: center;
        }
        .profile-card img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            margin-bottom: 20px;
        }
        .profile-card h3 {
            margin-bottom: 5px;
            color: #fff;
        }
        .profile-card p {
            color: #aaa;
            margin-bottom: 15px;
        }
        .profile-info {
            text-align: left;
            margin-top: 20px;
        }
        .profile-info h6 {
            color: #0d6efd;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            margin: 10px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            margin: 10px;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture">
        <h3><?php echo htmlspecialchars($lecturer['name']); ?></h3>
        <p><?php echo htmlspecialchars($lecturer['role']); ?> at RCTI</p>

        <div class="profile-info">
            <h6><i class="fa-solid fa-envelope"></i> Email</h6>
            <p><?php echo htmlspecialchars($lecturer['email']); ?></p>

            <h6><i class="fa-solid fa-building"></i> Department</h6>
            <p><?php echo htmlspecialchars($lecturer['department']); ?></p>

            <h6><i class="fa-solid fa-lock"></i> Password</h6>
            <p><?php echo htmlspecialchars($lecturer['password']); ?></p>
        </div>

        <div class="mt-4">
            <a href="edit_profile.php" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> Edit Profile</a>
            <a href="lecturer_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
