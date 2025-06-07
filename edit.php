<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "newdbs");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if id is provided for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM studentss WHERE id='$id'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
} else {
    echo "No user specified for edit.";
    exit();
}

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $regno = $_POST['regno'];
    $city = $_POST['city'];

    $update_sql = "UPDATE studentss SET name='$name', email='$email', regno='$regno', city='$city' WHERE id='$id'";
    if ($conn->query($update_sql) === TRUE) {
        header("Location: dashboard.php");
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit User</title>
</head>
<body>
  <h2>Edit User</h2>
  <form action="edit.php?id=<?php echo $user['id']; ?>" method="post">
    <input type="text" name="name" value="<?php echo $user['name']; ?>" required><br>
    <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>
    <input type="text" name="regno" value="<?php echo $user['regno']; ?>" required><br>
    <input type="text" name="city" value="<?php echo $user['city']; ?>" required><br>
    <input type="submit" value="Update">
  </form>
</body>
</html>
