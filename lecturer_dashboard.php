<?php
session_start();
require_once "db.php";

// Allow only lecturers
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$lecturer = $_SESSION['user'];
$user_id = $lecturer['id'];

// Fetch updated lecturer info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$lecturer = $result->fetch_assoc();

$profile_pic = !empty($lecturer['profile_pic']) ? 'uploads/' . $lecturer['profile_pic'] : 'assets/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Lecturer Dashboard | RCTI Attendance System</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{
            --brand-dark:#002b5b;
            --brand-mid:#004e92;
            --accent:#ffc107;
        }
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #333;
            -webkit-font-smoothing:antialiased;
        }
        .dashboard-container {
            max-width: 1100px;
            margin: 48px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(90deg, var(--brand-dark), var(--brand-mid));
            color: #fff;
            padding: 36px 28px;
            text-align: center;
        }
        .header img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #fff;
            object-fit: cover;
        }
        .header h3 { margin-top:14px; font-weight:600; font-size:1.45rem; }
        .header p { margin:4px 0; opacity:0.95; }

        .content { padding: 32px; }

        /* clickable card anchor */
        .card-link {
            display:block;
            text-decoration: none;
            color: inherit;
            border-radius: 12px;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .card-link:focus, .card-link:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); text-decoration:none; }
        .summary-card {
            background: #f8fbff;
            border-radius: 12px;
            text-align: center;
            padding: 26px 18px;
            height:100%;
            display:flex;
            flex-direction:column;
            justify-content:center;
            gap:10px;
        }
        .summary-card i { font-size:36px; color:var(--brand-mid); }
        .summary-card h5 { margin:0; font-weight:600; }
        .summary-card p { margin:0; color:#6b7280; font-size:0.95rem; }

        .card-cta {
            margin-top:14px;
            display:inline-block;
            background:var(--brand-mid);
            color:#fff;
            padding:8px 16px;
            border-radius:999px;
            font-weight:600;
            font-size:0.92rem;
            text-decoration:none;
        }

        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px; }

        .footer {
            background: #f7fbff;
            padding: 18px;
            text-align:center;
            color:#556;
            border-top:1px solid rgba(0,0,0,0.03);
        }

        /* small screens */
        @media (max-width:576px){
            .header { padding:22px 18px; }
            .content { padding:18px; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">
        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
        <h3><?php echo htmlspecialchars($lecturer['name']); ?></h3>
        <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($lecturer['email']); ?></p>
        <p><i class="fa-solid fa-building"></i>
            <?php echo !empty($lecturer['department']) ? htmlspecialchars($lecturer['department']) : 'Lecturer'; ?>
        </p>
    </div>

    <div class="content">
        <h4 class="text-center mb-4" style="color:var(--brand-dark)">Welcome back</h4>

        <div class="grid">
            <!-- Mark Attendance (hidden for lecturer if admin-only) -->
            <!-- Since lecturers don't have access, omit or link to a page that informs them -->
            <a href="no_access.php?feature=mark_attendance" class="card-link" aria-label="Mark Attendance (no access)">
                <div class="summary-card">
                    <i class="fa-solid fa-check-circle"></i>
                    <h5>Mark Attendance</h5>
                    <p>Marked by Admin only. Click to view info.</p>
                    <span class="card-cta">Learn more</span>
                </div>
            </a>

            <a href="attendance_view.php" class="card-link" aria-label="Attendance Summary">
                <div class="summary-card">
                    <i class="fa-solid fa-chart-simple"></i>
                    <h5>Attendance Summary</h5>
                    <p>View a summary of your attendance (read-only).</p>
                    <span class="card-cta">View Summary</span>
                </div>
            </a>

            <a href="view_timetable.php" class="card-link" aria-label="Timetable">
                <div class="summary-card">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h5>Timetable</h5>
                    <p>See your weekly schedule and classroom allocations.</p>
                    <span class="card-cta">Open Timetable</span>
                </div>
            </a>

            <a href="view_courses.php" class="card-link" aria-label="Manage Courses">
                <div class="summary-card">
                    <i class="fa-solid fa-book-open"></i>
                    <h5>View Courses</h5>
                    <p>View course details.</p>
                    <span class="card-cta">Manage</span>
                </div>
            </a>
            <a href="profile.php" class="card-link" aria-label="Profile Settings">
                <div class="summary-card">
                    <i class="fa-solid fa-user-pen"></i>
                    <h5>Profile Settings</h5>
                    <p>Update your information, change password or upload a photo.</p>
                    <span class="card-cta">Edit Profile</span>
                </div>
            </a>
        </div>

        <div class="mt-4 text-center">
            <a href="logout.php" class="btn btn-danger btn-lg" style="border-radius:12px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> RCTI Attendance System — Designed by <strong>Felix</strong>
    </div>
</div>

</body>
</html>
