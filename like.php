<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "newdbs");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

// Check if already liked
$stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $action = 'unliked';
} else {
    $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $action = 'liked';
}

// Get updated like count
$stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$likeCount = $result->fetch_assoc()['like_count'];

echo json_encode([
    'like_count' => $likeCount,
    'action' => $action
]);
?>
