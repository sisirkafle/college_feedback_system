<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
require 'config.php';

$search = $_GET['search'] ?? '';

$sql = "SELECT s.*, (SELECT COUNT(*) FROM feedback f WHERE f.student_id = s.student_id) AS feedback_count
        FROM students s WHERE 1=1";
$params = [];
$types = "";
if ($search !== '') {
    $sql .= " AND (s.full_name LIKE ? OR s.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
$total_count = $result->num_rows;
?>
<!DOCTYPE html>
<html>
<head>
<title>All Students</title>
        <link rel="icon" type="image/png" href="favicon.jpg">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
  <header class="dashboard-header">
    <h1>All Registered Students</h1>
    <a href="admin_dashboard.php" class="btn-logout" style="background:#4f46e5;">Back to Dashboard</a>
  </header>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn-primary" style="width:auto;">Search</button>
  </form>

  <div class="dashboard-main">
    <h2><?= $total_count ?> Student<?= $total_count == 1 ? '' : 's' ?></h2>

    <table class="data-table">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Faculty</th>
        <th>Semester</th>
        <th>Phone</th>
        <th>Feedback Submitted</th>
        <th>Registered On</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td>#<?= $row['student_id'] ?></td>
          <td><?= htmlspecialchars($row['full_name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['faculty']) ?></td>
          <td><?= htmlspecialchars($row['semester']) ?></td>
          <td><?= htmlspecialchars($row['phone_number']) ?></td>
          <td><?= $row['feedback_count'] ?></td>
          <td><?= $row['created_at'] ?></td>
        </tr>
      <?php } ?>
    </table>
  </div>
</div>
</body>
</html>
