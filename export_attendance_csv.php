<?php
require_once "db.php";

// Get filter parameters
$filter_user = $_GET['user_id'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Build WHERE clause
$where = [];
$params = [];
$types = '';

if ($filter_user) {
    $where[] = "user_id = ?";
    $params[] = $filter_user;
    $types .= 'i';
}

if ($filter_date) {
    $where[] = "date = ?";
    $params[] = $filter_date;
    $types .= 's';
}

// Fetch records
$sql = "SELECT a.id, u.name AS user_name, a.date, a.time_in, a.time_out, a.status 
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY a.date DESC, a.time_in DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Send CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','User Name','Date','Time In','Time Out','Status']);

while($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
