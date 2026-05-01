<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$name = $_GET['name'] ?? '';
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$query = "SELECT attendance.id, users.name AS lecturer_name, attendance.date, attendance.class_name,
                 attendance.unit_name, attendance.time_in, attendance.time_out, attendance.status, attendance.marked_by
          FROM attendance 
          LEFT JOIN users ON attendance.user_id = users.id 
          WHERE 1";

if (!empty($name)) {
    $query .= " AND users.name LIKE '%" . $conn->real_escape_string($name) . "%'";
}
if (!empty($from) && !empty($to)) {
    $query .= " AND attendance.date BETWEEN '" . $conn->real_escape_string($from) . "' AND '" . $conn->real_escape_string($to) . "'";
}

$query .= " ORDER BY attendance.date DESC";
$result = $conn->query($query);

// Build HTML
$html = '<h2 style="text-align:center;color:#007bff;">Attendance Summary Report</h2>';
if (!empty($name)) $html .= '<p><strong>Lecturer:</strong> ' . htmlspecialchars($name) . '</p>';
if (!empty($from) && !empty($to)) $html .= '<p><strong>Date Range:</strong> ' . htmlspecialchars($from) . ' to ' . htmlspecialchars($to) . '</p>';
$html .= '<hr>';

$html .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr style="background:#007bff;color:white;">
                    <th>ID</th><th>Lecturer</th><th>Date</th><th>Class</th><th>Unit</th>
                    <th>Time In</th><th>Time Out</th><th>Status</th><th>Marked By</th>
                </tr>
            </thead><tbody>';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
                <td>'.$row['id'].'</td>
                <td>'.htmlspecialchars($row['lecturer_name']).'</td>
                <td>'.htmlspecialchars($row['date']).'</td>
                <td>'.htmlspecialchars($row['class_name']).'</td>
                <td>'.htmlspecialchars($row['unit_name']).'</td>
                <td>'.htmlspecialchars($row['time_in']).'</td>
                <td>'.htmlspecialchars($row['time_out']).'</td>
                <td>'.htmlspecialchars($row['status']).'</td>
                <td>'.htmlspecialchars($row['marked_by']).'</td>
            </tr>';
}

$html .= '</tbody></table>';
$html .= '<p style="text-align:right;margin-top:20px;">Generated on '.date("Y-m-d H:i:s").'</p>';

// PDF setup
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("Attendance_Report.pdf", ["Attachment" => true]);
exit;
