<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: login.php"); exit; }
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $message = trim($_POST['message']);
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $proof_file = null;

    $student_id = $_SESSION['student_id'];

    if ($title == "" || $category == "" || $message == "") {
        $error = "Please fill all required fields.";
    } else {
        // Has this student already submitted feedback in this category within the last 7 days?
        $check = $conn->prepare("SELECT created_at FROM feedback WHERE student_id=? AND category=? ORDER BY created_at DESC LIMIT 1");
        $check->bind_param("is", $student_id, $category);
        $check->execute();
        $last = $check->get_result()->fetch_assoc();

        if ($last) {
            $days_since = (time() - strtotime($last['created_at'])) / 86400;
            if ($days_since < 7) {
                $days_left = ceil(7 - $days_since);
                $error = "You already submitted feedback for '$category' recently. Please wait $days_left more day(s) before submitting again for this category.";
            }
        }
    }

    if ($error == "" && !empty($_FILES['proof']['name'])) {
        $allowed = ["jpg", "jpeg", "png", "pdf"];
        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, JPEG or PDF files are allowed.";
        } elseif ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
            $error = "File must be smaller than 5MB.";
        } else {
            $proof_file = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['proof']['tmp_name'], "uploads/" . $proof_file);
        }
    }

    if ($error == "") {
        // Anonymous feedback needs admin approval before it's public.
        // Non-anonymous feedback is approved automatically.
        $approval_status = $is_anonymous ? "Pending Approval" : "Approved";

        $stmt = $conn->prepare("INSERT INTO feedback (student_id, category, title, message, is_anonymous, proof_file, approval_status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isssiss", $student_id, $category, $title, $message, $is_anonymous, $proof_file, $approval_status);
        $stmt->execute();

        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Submit Feedback</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h1>Submit Feedback</h1>

    <?php if ($error) { ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" required>
      </div>

      <div class="form-group">
        <label>Category</label>
        <select name="category" required>
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat) { ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group">
        <label>Feedback Message</label>
        <textarea name="message" rows="5" required></textarea>
      </div>

      <div class="form-group">
        <label>Upload Proof (optional - JPG, PNG, PDF, max 5MB)</label>
        <input type="file" name="proof">
      </div>

      <div class="form-group checkbox-group">
        <label><input type="checkbox" name="is_anonymous"> Submit anonymously</label>
        <p class="muted" style="margin-top:6px;">Anonymous feedback needs admin approval before it appears publicly. Your identity is never shown to other students, but the admin can always see it for accountability.</p>
      </div>

      <button type="submit" class="btn-primary">Submit Feedback</button>
    </form>

    <p class="auth-switch"><a href="dashboard.php">Back to Dashboard</a></p>
  </div>
</div>
</body>
</html>
