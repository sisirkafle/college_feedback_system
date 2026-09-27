<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
require 'config.php';

$error = "";

// Add a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);

    if ($name == "") {
        $error = "Category name cannot be empty.";
    } else {
        $check = $conn->prepare("SELECT category_id FROM categories WHERE name=?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This category already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            header("Location: manage_categories.php");
            exit;
        }
    }
}

// Delete a category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE category_id=$id");
    header("Location: manage_categories.php");
    exit;
}

$result = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Categories</title>
    <link rel="icon" type="image/png" href="favicon.jpg">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-wrapper">
  <header class="dashboard-header">
    <h1>Manage Categories</h1>
    <a href="admin_dashboard.php" class="btn-logout" style="background:#4f46e5;">Back to Dashboard</a>
  </header>

  <div class="dashboard-main">
    <?php if ($error) { ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST" style="display:flex; gap:10px; margin-bottom:20px;">
      <input type="text" name="name" placeholder="New category name" required>
      <button type="submit" class="btn-primary" style="width:auto;">Add Category</button>
    </form>

    <table class="data-table">
      <tr><th>Category</th><th></th></tr>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <a href="manage_categories.php?delete=<?= $row['category_id'] ?>"
               onclick="return confirm('Delete this category? Existing feedback already using it will keep the old name as text.')">
              Delete
            </a>
          </td>
        </tr>
      <?php } ?>
    </table>
  </div>
</div>
</body>
</html>
