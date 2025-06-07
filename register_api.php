<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "newdbs");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method allowed"]);
    exit();
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$regno = $_POST['regno'] ?? '';
$city = $_POST['city'] ?? '';
$profile_pic = '';

if (empty($name) || empty($email) || empty($password) || empty($regno) || empty($city)) {
    echo json_encode(["error" => "All fields are required"]);
    exit();
}

// Check if email or regno already exists
$check = $conn->query("SELECT * FROM studentss WHERE email = '$email' OR regno = '$regno'");
if ($check->num_rows > 0) {
    echo json_encode(["error" => "Email or Registration Number already exists"]);
    exit();
}

// Handle profile_pic upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);
    $fileName = uniqid() . "_" . basename($_FILES['profile_pic']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filePath)) {
        $profile_pic = $filePath;
    } else {
        echo json_encode(["error" => "File upload failed"]);
        exit();
    }
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO studentss (name, email, password, regno, city, profile_pic) 
        VALUES ('$name', '$email', '$hashed_password', '$regno', '$city', '$profile_pic')";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Registered successfully"]);
} else {
    echo json_encode(["error" => "Database error: " . $conn->error]);
}
?>
