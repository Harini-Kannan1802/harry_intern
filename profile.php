<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "newdbs");
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $regno = $_POST['regno'];
    $city = $_POST['city'];

    $sql = "UPDATE studentss SET name='$name', email='$email', regno='$regno', city='$city' WHERE id=$user_id";
    $conn->query($sql);
}

$result = $conn->query("SELECT * FROM studentss WHERE id=$user_id");
$user = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Profile</title>
</head>
<body>
  <h2>Your Profile</h2>
  <form method="post" action="profile.php">
    <table border="1" cellpadding="10">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Register No</th>
        <th>City</th>
        <th>Actions</th>
      </tr>
      <tr>
        <td><input type="text" name="name" value="<?php echo $user['name']; ?>"></td>
        <td><input type="email" name="email" value="<?php echo $user['email']; ?>"></td>
        <td><input type="text" name="regno" value="<?php echo $user['regno']; ?>"></td>
        <td><input type="text" name="city" value="<?php echo $user['city']; ?>"></td>
        <td>
          <input type="submit" value="Edit">
          <a href="delete.php" onclick="return confirm('Are you sure you want to delete your account?')">Delete</a>
        </td>
      </tr>
    </table>
  </form>

  <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
