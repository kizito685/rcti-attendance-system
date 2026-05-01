<?php
session_start();
require_once 'db.php';

// ✅ Allow only admin users
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Check if ID is passed
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: attendance_records.php?error=invalid_id");
    exit();
}

$id = intval($_GET['id']);

// ✅ Delete the record
$stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect with success message
    header("Location: attendance_records.php?msg=deleted");
    exit();
} else {
    // Redirect with error
    header("Location: attendance_records.php?error=delete_failed");
    exit();
}

$stmt->close();
$conn->close();
?>
