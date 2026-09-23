<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
require 'config.php';

$id = $_GET['id'] ?? 0;
$error = "";

// Toggle archive/unarchive
if (isset($_GET['archive'])) {
    $aid = (int)$_GET['archive'];
    $conn->query("UPDATE feedback SET is_archived = 1 - is_archived WHERE feedback_id = $aid");
    header("Location: admin_feedback.php");
    exit;
}

// Quick re-approve link, in case a rejection needs to be reversed
if (isset($_GET['force_approve'])) {
    $fid = (int)$_GET['force_approve'];
    $conn->query("UPDATE feedback SET approval_status='Approved' WHERE feedback_id = $fid");
    header("Location: admin_feedback.php?id=$fid");
    exit;
}

if ($id) {
    // ---- Single feedback: view + approve/reject (if anonymous & pending) + reply + status ----
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $admin_reply = trim($_POST['admin_reply']);
        $action = $_POST['action'] ?? 'save';

        if ($action == 'approve') {
            $stmt = $conn->prepare("UPDATE feedback SET approval_status='Approved', admin_reply=?, replied_at=NOW() WHERE feedback_id=?");
            $stmt->bind_param("si", $admin_reply, $id);
            $stmt->execute();
            header("Location: admin_feedback.php?id=$id");
            exit;

        } elseif ($action == 'reject') {
            if ($admin_reply == "") {
                $error = "Please write a reply message explaining why this feedback is being rejected.";
            } else {
                $stmt = $conn->prepare("UPDATE feedback SET approval_status='Rejected', status='Resolved', admin_reply=?, replied_at=NOW() WHERE feedback_id=?");
                $stmt->bind_param("si", $admin_reply, $id);
                $stmt->execute();
                header("Location: admin_feedback.php?id=$id");
                exit;
            }

        } else {
            // Normal reply + status update (already-approved / non-anonymous feedback)
            $status = $_POST['status'];
            $stmt = $conn->prepare("UPDATE feedback SET admin_reply=?, status=?, replied_at=NOW() WHERE feedback_id=?");
            $stmt->bind_param("ssi", $admin_reply, $status, $id);
            $stmt->execute();
            header("Location: admin_feedback.php?id=$id");
            exit;
        }
    }

    $stmt = $conn->prepare("SELECT f.*, s.full_name, s.email FROM feedback f JOIN students s ON f.student_id = s.student_id WHERE f.feedback_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $feedback = $stmt->get_result()->fetch_assoc();

    if (!$feedback) { die("Feedback not found."); }
    $needs_approval = ($feedback['is_anonymous'] && $feedback['approval_status'] == 'Pending Approval');

} else {
    // ---- List of all feedback, with search + category filter ----
    $search = $_GET['search'] ?? '';
    $filter_category = $_GET['category'] ?? '';

    $sql = "SELECT f.*, s.full_name FROM feedback f JOIN students s ON f.student_id = s.student_id WHERE 1=1";
    $params = [];
    $types = "";
    if ($search !== '') { $sql .= " AND (f.title LIKE ? OR s.full_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= "ss"; }
    if ($filter_category !== '') { $sql .= " AND f.category = ?"; $params[] = $filter_category; $types .= "s"; }
    $sql .= " ORDER BY (f.approval_status = 'Pending Approval') DESC, f.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($types !== "") { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Feedback</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
  <header class="dashboard-header">
    <h1><?= $id ? 'Feedback Details' : 'Manage Feedback' ?></h1>
    <a href="<?= $id ? 'admin_feedback.php' : 'admin_dashboard.php' ?>" class="btn-logout" style="background:#4f46e5;">Back</a>
  </header>

  <?php if ($id) { ?>

    <div class="dashboard-main">
      <?php if ($error) { ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php } ?>

      <span class="badge badge-category"><?= htmlspecialchars($feedback['category']) ?></span>
      <span class="badge badge-<?= strtolower(str_replace(' ', '-', $feedback['status'])) ?>"><?= $feedback['status'] ?></span>
      <?php if ($feedback['is_anonymous']) { ?>
        <span class="badge badge-<?= strtolower(str_replace(' ', '-', $feedback['approval_status'])) ?>"><?= $feedback['approval_status'] ?></span>
      <?php } ?>
      <?php if ($feedback['is_archived']) { ?>
        <span class="badge badge-archived">Archived</span>
      <?php } ?>

      <h2><?= htmlspecialchars($feedback['title']) ?></h2>
      <p><?= nl2br(htmlspecialchars($feedback['message'])) ?></p>

      <p class="muted">
        Submitted by: <?= htmlspecialchars($feedback['full_name']) ?> (<?= htmlspecialchars($feedback['email']) ?>)
        <?php if ($feedback['is_anonymous']) { ?><span class="badge badge-archived">Posted as Anonymous</span><?php } ?>
        &middot; <?= $feedback['created_at'] ?>
      </p>
      <p class="muted">This student's identity is only visible to admin. Other students always see "Anonymous" for this post.</p>

      <?php if ($feedback['proof_file']) { ?>
        <p><a href="uploads/<?= htmlspecialchars($feedback['proof_file']) ?>" target="_blank">View attached proof</a></p>
      <?php } ?>

      <hr>

      <?php if ($needs_approval) { ?>

        <h3>This anonymous feedback needs approval</h3>
        <p class="muted">It will not appear on the public feedback list until you approve it. If you reject it, you must explain why - the student will see your reply on their dashboard.</p>

        <form method="POST">
          <div class="form-group">
            <label>Reply Message (required if rejecting)</label>
            <textarea name="admin_reply" rows="4"><?= htmlspecialchars($feedback['admin_reply']) ?></textarea>
          </div>
          <button type="submit" name="action" value="approve" class="btn-primary" style="width:auto; background:#059669;">Approve & Publish</button>
          <button type="submit" name="action" value="reject" class="btn-primary" style="width:auto; background:#dc2626;">Reject</button>
        </form>

      <?php } else { ?>

        <h3>Reply & Update Status</h3>
        <form method="POST">
          <div class="form-group">
            <label>Reply Message</label>
            <textarea name="admin_reply" rows="4"><?= htmlspecialchars($feedback['admin_reply']) ?></textarea>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <option value="Pending" <?= $feedback['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Reviewed" <?= $feedback['status'] == 'Reviewed' ? 'selected' : '' ?>>Reviewed</option>
              <option value="In Progress" <?= $feedback['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="Resolved" <?= $feedback['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
          </div>

          <button type="submit" name="action" value="save" class="btn-primary" style="width:auto;">Save Reply</button>
        </form>

        <?php if ($feedback['is_anonymous'] && $feedback['approval_status'] == 'Rejected') { ?>
          <p style="margin-top:14px;">
            <a href="admin_feedback.php?force_approve=<?= $feedback['feedback_id'] ?>"
               onclick="return confirm('Approve this feedback instead? It will become visible on the public list.')">
              Approve this feedback instead
            </a>
          </p>
        <?php } ?>

        <p style="margin-top:10px;">
          <a href="admin_feedback.php?archive=<?= $feedback['feedback_id'] ?>"
             onclick="return confirm('<?= $feedback['is_archived'] ? 'Unarchive' : 'Archive' ?> this feedback?')">
            <?= $feedback['is_archived'] ? 'Unarchive this feedback' : 'Archive this feedback (hide from public list)' ?>
          </a>
        </p>

      <?php } ?>
    </div>

  <?php } else { ?>

    <form method="GET" class="filter-bar">
      <input type="text" name="search" placeholder="Search by title or student..." value="<?= htmlspecialchars($search) ?>">
      <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat) { ?>
          <option value="<?= $cat ?>" <?= $filter_category == $cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php } ?>
      </select>
      <button type="submit" class="btn-primary" style="width:auto;">Filter</button>
    </form>

    <div class="dashboard-main">
      <table class="data-table">
        <tr>
          <th>Category</th>
          <th>Title</th>
          <th>Student</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?><?php if ($row['is_anonymous']) { ?> <span class="muted">(Anon)</span><?php } ?></td>
            <td>
              <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>"><?= $row['status'] ?></span>
              <?php if ($row['is_anonymous']) { ?>
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['approval_status'])) ?>"><?= $row['approval_status'] ?></span>
              <?php } ?>
              <?php if ($row['is_archived']) { ?><span class="badge badge-archived">Archived</span><?php } ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <a href="admin_feedback.php?id=<?= $row['feedback_id'] ?>">View / Reply</a> |
              <a href="admin_feedback.php?archive=<?= $row['feedback_id'] ?>"
                 onclick="return confirm('<?= $row['is_archived'] ? 'Unarchive' : 'Archive' ?> this feedback?')">
                <?= $row['is_archived'] ? 'Unarchive' : 'Archive' ?>
              </a>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>

  <?php } ?>
</div>
</body>
</html>
