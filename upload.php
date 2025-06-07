<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "newdbs");
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file is uploaded
    if (isset($_FILES['post_file']) && $_FILES['post_file']['error'] == 0) {
        $file = $_FILES['post_file'];
        $description = $_POST['description'];
        
        // Get file extension
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
        
        // Check if the file type is allowed
        if (in_array($file_ext, $allowed_types)) {
            $upload_dir = 'uploads/';
            $file_name = uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            // Move the uploaded file to the upload directory
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Save post details to the database
                $file_type = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
                $stmt = $conn->prepare("INSERT INTO posts (user_id, file_name, file_type, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $file_name, $file_type, $description);
                $stmt->execute();
                $stmt->close();
                
                header("Location: dashboard.php");
            } else {
                echo "Failed to upload file.";
            }
        } else {
            echo "Invalid file type.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Post</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center mb-4">Upload a New Post</h3>
        <form method="POST" enctype="multipart/form-data" class="shadow p-4 rounded bg-light">
            <div class="mb-3">
                <label for="post_file" class="form-label">Choose Image or Video</label>
                <input type="file" class="form-control" id="post_file" name="post_file" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Caption</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Say something about this post..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload</button>
            <div class="text-center mt-3">
                <a href="dashboard.php" class="text-decoration-none">Back to Dashboard</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
