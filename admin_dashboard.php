<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
require 'config.php';

$total = $conn->query("SELECT COUNT(*) AS c FROM feedback")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE status='Pending'")->fetch_assoc()['c'];
$reviewed = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE status='Reviewed'")->fetch_assoc()['c'];
$in_progress = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE status='In Progress'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE status='Resolved'")->fetch_assoc()['c'];
$awaiting_approval = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE approval_status='Pending Approval'")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];

$recent = $conn->query("SELECT f.*, s.full_name FROM feedback f JOIN students s ON f.student_id = s.student_id ORDER BY f.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
  <header class="dashboard-header">
    <h1>Admin Dashboard</h1>
    <a href="logout.php" class="btn-logout">Logout</a>
  </header>

  <div class="nav-links">
    <a href="admin_feedback.php">Manage All Feedback</a>
    <a href="manage_categories.php">Manage Categories</a>
    <a href="admin_students.php">View All Students</a>
  </div>

  <div class="stat-cards">
    <div class="stat-card stat-total">
      <h2><?= $total ?></h2>
      <p>Total Feedback</p>
    </div>
    <div class="stat-card stat-pending">
      <h2><?= $pending ?></h2>
      <p>Pending</p>
    </div>
    <div class="stat-card stat-reviewed">
      <h2><?= $reviewed ?></h2>
      <p>Reviewed</p>
    </div>
    <div class="stat-card stat-progress">
      <h2><?= $in_progress ?></h2>
      <p>In Progress</p>
    </div>
    <div class="stat-card stat-resolved">
      <h2><?= $resolved ?></h2>
      <p>Resolved</p>
    </div>
    <div class="stat-card stat-approval">
      <h2><?= $awaiting_approval ?></h2>
      <p>Awaiting Approval</p>
    </div>
    <div class="stat-card stat-students">
      <h2><?= $total_students ?></h2>
      <p>Registered Students</p>
    </div>
  </div>

  <div class="dashboard-main">
    <h2>Recent Feedback</h2>
    <table class="data-table">
      <tr>
        <th>Category</th>
        <th>Title</th>
        <th>Student</th>
        <th>Status</th>
        <th>Date</th>
        <th></th>
      </tr>
      <?php while ($row = $recent->fetch_assoc()) { ?>
        <tr>
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['full_name']) ?><?php if ($row['is_anonymous']) { ?> <span class="muted">(Anon)</span><?php } ?></td>
          <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= $row['status'] ?></span></td>
          <td><?= $row['created_at'] ?></td>
          <td><a href="admin_feedback.php?id=<?= $row['feedback_id'] ?>">View</a></td>
        </tr>
      <?php } ?>
    </table>
  </div>
</div>
</body>
</html>
