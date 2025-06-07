<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "newdbs");

// Step 1: Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method allowed"]);
    exit();
}

// Step 2: Get ID from request
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    echo json_encode(["error" => "User ID is required"]);
    exit();
}

$id = $conn->real_escape_string($data['id']);

// Step 3: Delete user
$sql = "DELETE FROM studentss WHERE id='$id'";
if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "User deleted successfully"]);
} else {
    echo json_encode(["error" => "Error: " . $conn->error]);
}
?>
