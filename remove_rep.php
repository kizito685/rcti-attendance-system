<?php
require_once "db.php";

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$rep_id = intval($_GET['id']);

// Fetch class rep details first
$query = "SELECT * FROM class_reps WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rep_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Class rep not found.");
}

$rep = $result->fetch_assoc();
$user_id = $rep['user_id'];

// Step 1: Delete from class_reps
$delete_sql = "DELETE FROM class_reps WHERE id = ?";
$del_stmt = $conn->prepare($delete_sql);
$del_stmt->bind_param("i", $rep_id);
$del_stmt->execute();

// Step 2: Update user role back to 'student'
$update_user_sql = "UPDATE users SET role = 'student' WHERE id = ?";
$update_stmt = $conn->prepare($update_user_sql);
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();

header("Location: manage_reps.php?msg=Class rep removed successfully");
exit();
?>
