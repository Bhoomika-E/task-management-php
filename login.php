<?php
include __DIR__ . '/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && password_verify($password, $res['password'])) {
        $_SESSION['user_id'] = $res['id'];
        $_SESSION['username'] = $res['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - TaskHero</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-box">
  <h2>Login - TaskHero</h2>
  <?php if (!empty($_GET['registered'])) echo '<p class="success">Registration successful. Please login.</p>'; ?>
  <?php if (!empty($error)) echo '<p class="error">'.htmlspecialchars($error).'</p>'; ?>
  <form method="POST" action="">
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" required>
    <button type="submit">Login</button>
  </form>
  <p>No account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
