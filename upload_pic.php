<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $conn = new mysqli("localhost", "root", "", "newdbs");

    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $fileName = $_FILES['profile_pic']['name'];
    $tempName = $_FILES['profile_pic']['tmp_name'];
    $uploadDir = "uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir);
    }

    $targetPath = $uploadDir . uniqid() . "_" . basename($fileName);

    if (move_uploaded_file($tempName, $targetPath)) {
        $stmt = $conn->prepare("UPDATE studentss SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $targetPath, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Failed to update profile picture in database.";
        }
    } else {
        echo "Failed to upload image.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Profile Picture</title>
</head>
<body>
    <h2>Upload Your Profile Picture</h2>
    <form method="POST" enctype="multipart/form-data" action="upload_pic.php">
        <input type="file" name="profile_pic" accept="image/*" required><br><br>
        <input type="submit" value="Upload">
    </form>
</body>
</html>
