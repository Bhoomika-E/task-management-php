<?php
include __DIR__ . '/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];

    if (!$username || !$email || !$password_plain) {
        $error = 'Please fill all fields.';
    } else {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)"); 
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = 'Registration failed. Email may already exist.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register - TaskHero</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-box">
  <h2>Register - TaskHero</h2>
  <?php if (!empty($error)) echo '<p class="error">'.htmlspecialchars($error).'</p>'; ?>
  <form method="POST" action="">
    <label>Username</label><input type="text" name="username" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" required>
    <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
