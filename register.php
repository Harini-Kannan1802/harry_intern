<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "newdbs");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';
    $regno = isset($_POST['regno']) ? $conn->real_escape_string($_POST['regno']) : '';
    $city = isset($_POST['city']) ? $conn->real_escape_string($_POST['city']) : '';

    // Handle profile picture upload
    $profilePicPath = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $fileName = $_FILES['profile_pic']['name'];
        $tempName = $_FILES['profile_pic']['tmp_name'];
        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }

        $profilePicPath = $uploadDir . uniqid() . "_" . basename($fileName);
        if (!move_uploaded_file($tempName, $profilePicPath)) {
            echo "Failed to upload image.";
            exit();
        }
    }

    // Insert data into the database
    $sql = "INSERT INTO studentss (name, email, password, regno, city, profile_pic) 
            VALUES ('$name', '$email', '$password', '$regno', '$city', '$profilePicPath')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!-- Registration Form -->
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
</head>
<body>
  <h2>Register</h2>
  <form action="register.php" method="post" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="regno" placeholder="Register Number" required><br>
    <input type="text" name="city" placeholder="City" required><br>
    <input type="file" name="profile_pic" accept="image/*"><br><br>
    <input type="submit" value="Register">
  </form>
</body>
</html>

