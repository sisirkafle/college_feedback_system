<?php
session_start();
$is_admin = isset($_SESSION['admin_id']);
session_destroy();
header("Location: " . ($is_admin ? "admin_login.php" : "login.php"));
exit;
