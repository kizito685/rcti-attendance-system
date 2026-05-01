<?php
session_start();
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $_SESSION['error'] = "Department name cannot be empty!";
        header("Location: manage_departments.php");
        exit;
    }

    // Check if department already exists
    $stmtCheck = $conn->prepare("SELECT * FROM departments WHERE name = ? LIMIT 1");
    $stmtCheck->bind_param("s", $name);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck && $resultCheck->num_rows > 0) {
        $_SESSION['error'] = "Department already exists!";
        header("Location: manage_departments.php");
        exit;
    }

    // Insert new department
    $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Department added successfully!";
    } else {
        $_SESSION['error'] = "Error adding department: " . $conn->error;
    }

    header("Location: manage_departments.php");
    exit;
}
?>
