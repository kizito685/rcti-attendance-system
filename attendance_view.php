<?php
session_start();
require_once "db.php";
require_once "vendor/autoload.php";
use Dompdf\Dompdf;

// ✅ Lecturers only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// ✅ Handle PDF download
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $stmt = $conn->prepare("SELECT class_name, unit_name, date, time_in, time_out, status, marked_by 
                            FROM attendance WHERE user_id=? ORDER BY date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '<h2 style="text-align:center;">My Attendance Records</h2>';
    $html .= '<table border="1" width="100%" cellspacing="0" cellpadding="5">
                <thead>
                    <tr style="background:#0d6efd;color:white;">
                        <th>#</th>
                        <th>Class</th>
                        <th>Unit</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>';

    $count = 1;
    while ($row = $result->fetch_assoc()) {
        $statusColor = $row['status'] === 'Present' ? '#28a745' : ($row['status'] === 'Absent' ? '#dc3545' : '#ffc107');
        $html .= "<tr>
                    <td>{$count}</td>
                    <td>{$row['class_name']}</td>
                    <td>{$row['unit_name']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['time_in']}</td>
                    <td>{$row['time_out']}</td>
                    <td style='color:white;background:{$statusColor};text-align:center;'>{$row['status']}</td>
                    <td>{$row['marked_by']}</td>
                  </tr>";
        $count++;
    }
    $html .= '</tbody></table>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("My_Attendance.pdf", ["Attachment" => true]);
    exit();
}

// ✅ Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM attendance 
                        WHERE user_id=? AND (unit_name LIKE ? OR class_name LIKE ?) 
                        ORDER BY date DESC");
$searchTerm = "%$search%";
$stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Attendance | Lecturer Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: #f5f7fa;
    font-family: 'Poppins', sans-serif;
}
.container {
    margin-top: 50px;
}
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.table thead {
    background: #0d6efd;
    color: white;
}
.btn-download {
    background: #6610f2;
    color: white;
    border-radius: 25px;
}
.btn-download:hover {
    background: #520dc2;
    color: white;
}
.search-bar {
    border-radius: 25px;
    padding: 8px 15px;
}
.badge-present {background-color:#28a745;}
.badge-absent {background-color:#dc3545;}
.badge-late {background-color:#ffc107;color:#000;}
</style>
</head>
<body>
<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-primary"><i class="bi bi-calendar-check"></i> My Attendance</h3>
            <div>
                <a href="?download=pdf" class="btn btn-download"><i class="bi bi-file-earmark-pdf"></i> Download PDF</a>
                <a href="lecturer_dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control search-bar" placeholder="Search by class or unit..." value="<?= htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Unit</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): $count=1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $count++; ?></td>
                                <td><?= htmlspecialchars($row['class_name']); ?></td>
                                <td><?= htmlspecialchars($row['unit_name']); ?></td>
                                <td><?= htmlspecialchars($row['date']); ?></td>
                                <td><?= htmlspecialchars($row['time_in']); ?></td>
                                <td><?= htmlspecialchars($row['time_out']); ?></td>
                                <td>
                                    <?php
                                        $status = $row['status'];
                                        if($status==='Present') echo "<span class='badge badge-present'>Present</span>";
                                        elseif($status==='Absent') echo "<span class='badge badge-absent'>Absent</span>";
                                        else echo "<span class='badge badge-late'>Late</span>";
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['marked_by']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No attendance records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
