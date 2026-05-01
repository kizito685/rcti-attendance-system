<?php
session_start();
require_once 'db.php';

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: no_access.php");
    exit();
}

// Target date (today by default)
$date = date('Y-m-d');

// Fetch all lecturers
$lecturers = $conn->query("SELECT id, name FROM users WHERE role='lecturer'");

$class_name = "Class A"; // change dynamically if needed
$unit_name = "Unit 1";   // change dynamically if needed

$marked_by = $_SESSION['user']['name']; // Admin name

if ($lecturers->num_rows > 0) {
    while ($lecturer = $lecturers->fetch_assoc()) {
        $user_id = $lecturer['id'];

        // Check if attendance exists for this lecturer today
        $check = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND date=? AND class_name=? AND unit_name=?");
        $check->bind_param("isss", $user_id, $date, $class_name, $unit_name);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            // Insert Absent record
            $insert = $conn->prepare("INSERT INTO attendance (user_id, date, class_name, unit_name, status, marked_by) VALUES (?, ?, ?, ?, 'Absent', ?)");
            $insert->bind_param("issss", $user_id, $date, $class_name, $unit_name, $marked_by);
            $insert->execute();
        }
    }
}

echo "<div class='alert alert-success text-center'>✅ Auto-marking complete for all unmarked lecturers for $date!</div>";
?>
