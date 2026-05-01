<?php
session_start();
require_once 'db.php';

// Fetch courses for dropdown
$courses = $conn->query("SELECT * FROM courses ORDER BY name ASC");

// Handle upload
if (isset($_POST['upload'])) {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $file = $_FILES['material'];

    // Create upload folder if it doesn’t exist
    $uploadDir = "uploads/materials/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Validate file
    $allowed = ['pdf', 'docx', 'pptx', 'xlsx', 'zip'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . "_" . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (in_array(strtolower($ext), $allowed)) {
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO materials (course_id, title, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $course_id, $title, $targetPath);

            if ($stmt->execute()) {
                echo "<script>alert('Material uploaded successfully!'); window.location='upload_materials.php';</script>";
            } else {
                echo "<div class='alert alert-danger text-center'>Database Error: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Error uploading file. Please try again.</div>";
        }
    } else {
        echo "<div class='alert alert-warning text-center'>Invalid file type. Allowed: PDF, DOCX, PPTX, XLSX, ZIP.</div>";
    }
}

// Fetch uploaded materials
$materials = $conn->query("SELECT m.*, c.name AS course_name FROM materials m 
                           JOIN courses c ON m.course_id = c.id 
                           ORDER BY m.uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Materials | RCTI Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/rcmrd-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .container-box {
            background: rgba(0, 0, 0, 0.85);
            border-radius: 15px;
            padding: 30px;
            margin-top: 50px;
        }
        .form-label { color: #fff; }
        .table thead th {
            background-color: #198754;
            color: #fff;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(25,135,84,0.3);
        }
    </style>
</head>
<body>

<div class="container col-md-10">
    <div class="container-box shadow">
        <h3 class="text-center mb-4">📚 Upload Learning Materials</h3>

        <!-- Upload Form -->
        <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Select Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Choose Course --</option>
                    <?php while ($c = $courses->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Material Title</label>
                <input type="text" name="title" class="form-control" placeholder="Enter material title" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Select File</label>
                <input type="file" name="material" class="form-control" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" name="upload" class="btn btn-success w-100">⬆️ Upload</button>
            </div>
        </form>

        <!-- Materials Table -->
        <table class="table table-dark table-striped table-hover text-center align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Title</th>
                    <th>File</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($materials->num_rows > 0) {
                    $i = 1;
                    while ($row = $materials->fetch_assoc()) {
                        echo "<tr>
                                <td>{$i}</td>
                                <td>{$row['course_name']}</td>
                                <td>{$row['title']}</td>
                                <td><a href='{$row['file_path']}' class='btn btn-sm btn-primary' target='_blank'>📂 View</a></td>
                                <td>{$row['uploaded_at']}</td>
                              </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-warning'>No materials uploaded yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="text-center mt-3">
            <a href="lecturer_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
