<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "newdbs");

// Step 1: Allow only POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method allowed"]);
    exit();
}

// Step 2: Get the input data
$data = json_decode(file_get_contents("php://input"), true);

// Step 3: Check if required fields are present
if (empty($data['id']) || empty($data['name']) || empty($data['email']) || empty($data['city'])) {
    echo json_encode(["error" => "All fields are required"]);
    exit();
}

// Step 4: Escape input
$id = $conn->real_escape_string($data['id']);
$name = $conn->real_escape_string($data['name']);
$email = $conn->real_escape_string($data['email']);
$city = $conn->real_escape_string($data['city']);

// Step 5: Check if email already exists (but not for the same user)
$check = $conn->query("SELECT * FROM studentss WHERE email = '$email' AND id != '$id'");
if ($check->num_rows > 0) {
    echo json_encode(["error" => "Email already exists"]);
    exit();
}

// Step 6: Update the user
$sql = "UPDATE studentss SET name='$name', email='$email', city='$city' WHERE id='$id'";
if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "User updated successfully"]);
} else {
    echo json_encode(["error" => "Error: " . $conn->error]);
}
?>
