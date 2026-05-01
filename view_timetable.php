<?php
session_start();
require_once "db.php";

// Only lecturers allowed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$lecturer_id = $user['id'];

// Fetch timetable
$query = $conn->prepare("
    SELECT t.*, c.name AS course_name, c.code, t.subject
    FROM timetable t
    JOIN courses c ON t.course_id = c.id
    WHERE t.lecturer_id = ?
    ORDER BY FIELD(t.day, 'Monday','Tuesday','Wednesday','Thursday','Friday'), t.start_time
");
$query->bind_param("i", $lecturer_id);
$query->execute();
$result = $query->get_result();

$timetable = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timetable[$row['day']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Timetable | RCTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1400&q=80') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            min-height: 100vh;
        }
        .overlay {
            background: rgba(0, 0, 0, 0.75);
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
        }
        .card {
            background: rgba(18, 23, 43, 0.9);
            border: none;
            border-radius: 20px;
            color: #fff;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: linear-gradient(90deg, #00b894, #0984e3);
            border-radius: 20px 20px 0 0;
            font-weight: 600;
        }
        .btn-print {
            background-color: #00b894;
            color: white;
            border: none;
        }
        .btn-print:hover {
            background-color: #019870;
        }
        .btn-light {
            border: none;
        }
        #printArea {
            background: #fff;
            color: #000;
            padding: 40px;
            border-radius: 15px;
        }
        .timetable-header {
            text-align: center;
            border-bottom: 3px solid #00b894;
            margin-bottom: 25px;
            padding-bottom: 10px;
        }
        .timetable-header img {
            height: 70px;
        }
        .timetable-header h2 {
            margin: 10px 0 0;
            color: #0b0f19;
        }
        .timetable-header h5 {
            color: #555;
            font-weight: 500;
        }
        table.timetable-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
        }
        table.timetable-table th, table.timetable-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table.timetable-table th {
            background: #00b894;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .no-data {
            text-align: center;
            color: #888;
            padding: 30px 0;
        }
        .footer-note {
            text-align: right;
            font-size: 0.9rem;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="container py-5">
    <div class="card shadow-lg border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa-solid fa-calendar-days"></i> My Official Timetable</h4>
            <div>
                <a href="lecturer_dashboard.php" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <button class="btn btn-print btn-sm" id="downloadPDF"><i class="fa-solid fa-file-pdf"></i> Download PDF</button>
            </div>
        </div>

        <div class="card-body">
            <div id="printArea">
                <!-- Header -->
                <div class="timetable-header">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/8/80/Example_image.png" alt="RCTI Logo">
                    <h2> Regional Centre for Mapping of Resources for Development (RCTI)</h2>
                    <h5>Official Lecturer Timetable</h5>
                    <p><strong>Lecturer:</strong> <?= htmlspecialchars($user['name']) ?> &nbsp; | &nbsp; 
                    <strong>Date:</strong> <?= date('d M Y') ?></p>
                </div>

                <?php if (!empty($timetable)): ?>
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Course</th>
                                <th>Subject</th>
                                <th>Code</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
                                <?php if (!empty($timetable[$day])): ?>
                                    <?php foreach ($timetable[$day] as $class): ?>
                                        <tr>
                                            <td><?= $day ?></td>
                                            <td><?= htmlspecialchars($class['course_name']) ?></td>
                                            <td><?= htmlspecialchars($class['subject']) ?></td>
                                            <td><?= htmlspecialchars($class['code']) ?></td>
                                            <td><?= date("g:i A", strtotime($class['start_time'])) ?></td>
                                            <td><?= date("g:i A", strtotime($class['end_time'])) ?></td>
                                            <td><?= strtoupper($class['room']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fa-solid fa-info-circle"></i> No timetable data available.
                    </div>
                <?php endif; ?>

                <div class="footer-note">
                    <p><strong>Generated on:</strong> <?= date('l, d M Y - h:i A') ?></p>
                    <p><em>System generated document - RCTI Attendance System</em></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("downloadPDF").addEventListener("click", async () => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF("p", "pt", "a4");
    const timetable = document.getElementById("printArea");

    const canvas = await html2canvas(timetable, { scale: 2 });
    const imgData = canvas.toDataURL("image/png");
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
    pdf.save("RCTI_Timetable.pdf");
});
</script>
</body>
</html>
