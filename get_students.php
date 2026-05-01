<?php
require_once "db.php";

$dept_id = intval($_GET['department_id']);
$result = $conn->query("SELECT id, name FROM users WHERE role = 'student' AND department = (SELECT name FROM departments WHERE id = $dept_id)");
$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode($students);
?>
