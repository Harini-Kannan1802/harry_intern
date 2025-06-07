<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "newdbs");

// Step 1: Check DB connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

// Step 2: Get all users
$sql = "SELECT id, name, email, city FROM studentss";
$result = $conn->query($sql);

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Step 3: Send JSON response
echo json_encode(["users" => $users]);
?>
