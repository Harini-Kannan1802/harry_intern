<?php
session_start();
$conn = new mysqli("localhost", "root", "", "newdbs");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $regno = $conn->real_escape_string($_POST['regno']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM studentss WHERE regno='$regno' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_city'] = $user['city'];

        // Redirect based on profile_pic
        if (empty($user['profile_pic'])) {
            header("Location: upload_pic.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid Register Number or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="login.php" method="post">
        <input type="text" name="regno" placeholder="Register Number" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
