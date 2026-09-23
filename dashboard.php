<?php
session_start();
if (!isset($_SESSION['student_id'])) { header("Location: login.php"); exit; }
require 'config.php';

$student_id = $_SESSION['student_id'];
$view = $_GET['view'] ?? 'mine';

if ($view == 'all') {
    // All students' feedback (not archived), with search + category filter
    $search = $_GET['search'] ?? '';
    $filter_category = $_GET['category'] ?? '';

    $sql = "SELECT f.*, s.full_name FROM feedback f JOIN students s ON f.student_id = s.student_id WHERE f.is_archived = 0 AND f.approval_status = 'Approved'";
    $params = [];
    $types = "";
    if ($search !== '') { $sql .= " AND f.title LIKE ?"; $params[] = "%$search%"; $types .= "s"; }
    if ($filter_category !== '') { $sql .= " AND f.category = ?"; $params[] = $filter_category; $types .= "s"; }
    $sql .= " ORDER BY f.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($types !== "") { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Only this student's own feedback (including archived - they should still see it)
    $stmt = $conn->prepare("SELECT * FROM feedback WHERE student_id=? ORDER BY created_at DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
  <header class="dashboard-header">
    <div>
      <h1>Welcome, <?= htmlspecialchars($_SESSION['student_name']) ?></h1>
      <p class="subtitle">Your Student ID: #<?= $student_id ?></p>
    </div>
    <a href="logout.php" class="btn-logout">Logout</a>
  </header>

  <div class="nav-links">
    <a href="submit_feedback.php" class="btn-primary" style="width:auto; display:inline-block;">+ Submit New Feedback</a>
    <a href="dashboard.php?view=mine">My Feedback</a>
    <a href="dashboard.php?view=all">All Feedback</a>
  </div>

  <?php if ($view == 'all') { ?>
    <form method="GET" class="filter-bar">
      <input type="hidden" name="view" value="all">
      <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>">
      <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat) { ?>
          <option value="<?= $cat ?>" <?= $filter_category == $cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php } ?>
      </select>
      <button type="submit" class="btn-primary" style="width:auto;">Filter</button>
    </form>
  <?php } ?>

  <div class="dashboard-main">
    <?php if ($view == 'all') { ?>

      <h2>All Feedback</h2>
      <?php if ($result->num_rows == 0) { ?>
        <p>No feedback found.</p>
      <?php } ?>

      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="feedback-card">
          <div class="feedback-top">
            <span class="badge badge-category"><?= htmlspecialchars($row['category']) ?></span>
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= $row['status'] ?></span>
          </div>
          <h3><?= htmlspecialchars($row['title']) ?></h3>
          <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>

          <?php if ($row['proof_file']) { ?>
            <p><a href="uploads/<?= htmlspecialchars($row['proof_file']) ?>" target="_blank">View attached proof</a></p>
          <?php } ?>

          <?php if ($row['admin_reply']) { ?>
            <div class="admin-reply-box">
              <strong>Admin Reply:</strong>
              <p><?= nl2br(htmlspecialchars($row['admin_reply'])) ?></p>
            </div>
          <?php } ?>

          <p class="muted">
            By <?= $row['is_anonymous'] ? 'Anonymous' : htmlspecialchars($row['full_name']) ?>
            &middot; <?= $row['created_at'] ?>
          </p>
        </div>
      <?php } ?>

    <?php } else { ?>

      <h2>My Feedback (<?= $result->num_rows ?>)</h2>
      <?php if ($result->num_rows == 0) { ?>
        <p>You haven't submitted any feedback yet.</p>
      <?php } else { ?>
        <table class="data-table">
          <tr>
            <th>Category</th>
            <th>Title</th>
            <th>Status</th>
            <th>Visibility</th>
            <th>Admin Reply</th>
            <th>Date</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= $row['status'] ?></span></td>
              <td>
                <?php if ($row['is_anonymous']) { ?>
                  <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['approval_status'])) ?>"><?= $row['approval_status'] ?></span>
                <?php } else { ?>
                  <span class="muted">Public</span>
                <?php } ?>
              </td>
              <td><?= $row['admin_reply'] ? htmlspecialchars($row['admin_reply']) : '<span class="muted">No reply yet</span>' ?></td>
              <td><?= $row['created_at'] ?></td>
            </tr>
          <?php } ?>
        </table>
      <?php } ?>

    <?php } ?>
  </div>
</div>
</body>
</html>
