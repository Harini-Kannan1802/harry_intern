<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You need to be logged in']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "newdbs");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
$comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : null;

if (empty($comment_text)) {
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit();
}

// Insert the comment into the database
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $post_id, $user_id, $comment_text);

if ($stmt->execute()) {
    // Fetch the user's name for the comment
    $stmt = $conn->prepare("SELECT name FROM studentss WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_name = $result->fetch_assoc()['name'];

    echo json_encode([
        'success' => true,
        'user_name' => $user_name,
        'comment_text' => htmlspecialchars($comment_text)
    ]);
} else {
    echo json_encode(['error' => 'Failed to add comment']);
}

$stmt->close();
$conn->close();
?>


