<?php
session_start();
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $faculty = $_POST['faculty'];
    $semester = $_POST['semester'];
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($full_name == "" || $email == "" || $phone_number == "" || $password == "") {
        $error = "Please fill all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT student_id FROM students WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (full_name, email, faculty, semester, phone_number, password_hash) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $full_name, $email, $faculty, $semester, $phone_number, $password_hash);
            $stmt->execute();

            $_SESSION['msg'] = "Registration successful. Please login.";
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Registration</title>
    <link rel="icon" type="image/png" href="favicon.jpg">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h1>Student Registration</h1>

    <?php if ($error) { ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Faculty</label>
        <select name="faculty" required>
          <option value="">-- Select Faculty --</option>
          <option value="BCA">BCA</option>
          <option value="BITM">BITM</option>
          <option value="B.Sc. CSIT">B.Sc. CSIT</option>
        </select>
      </div>

      <div class="form-group">
        <label>Semester</label>
        <select name="semester" required>
          <option value="">-- Select Semester --</option>
          <?php for ($i = 1; $i <= 8; $i++) { ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone_number" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
      </div>

      <button type="submit" class="btn-primary">Register</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>
</body>
</html>
