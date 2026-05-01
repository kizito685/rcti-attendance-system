<?php
session_start();
require_once 'db.php';

// Ensure lecturer is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$lecturer = $_SESSION['user'];
$msg = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $password = $conn->real_escape_string($_POST['password']);

    $profilePic = $lecturer['profile_pic']; // default existing photo

    // Handle image upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetFilePath = $targetDir . $fileName;

        // Validate image type
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowed)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
                $profilePic = $fileName;
            }
        }
    }

    // Update database
    $update = $conn->prepare("UPDATE users SET name=?, email=?, department=?, password=?, profile_pic=? WHERE id=?");
    $update->bind_param("sssssi", $name, $email, $department, $password, $profilePic, $lecturer['id']);
    if ($update->execute()) {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['department'] = $department;
        $_SESSION['user']['password'] = $password;
        $_SESSION['user']['profile_pic'] = $profilePic;
        $msg = "<div class='alert alert-success text-center'>✅ Profile updated successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger text-center'>❌ Error updating profile.</div>";
    }
}

$profilePicPath = !empty($lecturer['profile_pic']) ? 'uploads/' . $lecturer['profile_pic'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Lecturer Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/rcmrd-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .profile-box {
            background: rgba(0, 0, 0, 0.85);
            padding: 35px;
            border-radius: 15px;
            max-width: 700px;
            margin: 60px auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        .profile-box h3 {
            text-align: center;
            margin-bottom: 25px;
        }
        .profile-pic {
            display: block;
            margin: 0 auto 20px auto;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        label {
            color: #fff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        a.back-btn {
            text-decoration: none;
            color: #fff;
            display: inline-block;
            margin-top: 15px;
        }
        a.back-btn:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="profile-box">
        <h3><i class="fa-solid fa-user-pen"></i> Edit Your Profile</h3>
        <?php echo $msg; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture" class="profile-pic">
            </div>

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($lecturer['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($lecturer['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Department</label>
                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($lecturer['department']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($lecturer['password']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control" accept="image/*">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">💾 Update Profile</button>
            </div>
        </form>

        <div class="text-center">
            <a href="lecturer_dashboard.php" class="back-btn mt-3"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
