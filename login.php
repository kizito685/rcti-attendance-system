<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    switch ($_SESSION['user']['role']) {
        case 'admin':
            header("Location: admin_dashboard.php");
            exit;
        case 'lecturer':
            header("Location: lecturer_dashboard.php");
            exit;
        case 'class_rep':
            header("Location: classrep_dashboard.php");
            exit;
    }
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();

        if ($user['password'] === $password) {
            $_SESSION['user'] = $user;
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'lecturer':
                    header("Location: lecturer_dashboard.php");
                    break;
                case 'class_rep':
                    header("Location: classrep_dashboard.php");
                    break;
                default:
                    $error = "Role not recognized!";
                    break;
            }
            exit;
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No account found with that email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1920&q=80') 
                        no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .login-box {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            width: 400px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.3);
            color: #fff;
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-box h3 {
            color: #00ff9d;
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
        }
        .form-label {
            color: #ddd;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 8px;
            font-size: 15px;
            padding: 10px 12px;
        }
        .form-control:focus {
            outline: none;
            box-shadow: 0 0 5px #00ff9d;
        }
        .btn-login {
            background: linear-gradient(135deg, #00ff9d, #00cc7a);
            border: none;
            border-radius: 8px;
            color: #000;
            font-weight: bold;
            font-size: 16px;
            padding: 10px;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #00cc7a, #00995a);
            transform: scale(1.03);
        }
        .error {
            background: rgba(255, 80, 80, 0.15);
            border-left: 4px solid #ff5555;
            color: #ffb3b3;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            color: #ccc;
            font-size: 0.9em;
            z-index: 2;
        }
        .login-box small {
            color: #aaa;
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="login-box">
    <h3>📋 RCTI Attendance System</h3>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-login w-100 mt-2">Login</button>
    </form>

    <div class="text-center mt-3">
        <small>🔑 Admin, Lecturer & Class Rep Login</small>
    </div>
</div>

<footer>© <?= date("Y"); ?> RCTI Attendance System</footer>
</body>
</html>
