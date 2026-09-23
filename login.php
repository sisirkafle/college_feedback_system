<?php
session_start();
require 'config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit;
}

$msg = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT student_id, full_name, password_hash FROM students WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
        if (password_verify($password, $student['password_hash'])) {
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['full_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h1>Student Login</h1>

    <?php if ($msg) { ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php } ?>

    <?php if ($error) { ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button type="submit" class="btn-primary">Login</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
    <p class="auth-switch"><a href="admin_login.php">Admin Login</a></p>
  </div>
</div>
</body>
</html>
