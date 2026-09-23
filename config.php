<?php
// C:\xampp\htdocs\college_feedback_system\config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect specifically to college_feedback_system
$conn = new mysqli("localhost", "root", "", "college_feedback_system");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch categories dynamically
$categories = [];
$cat_result = $conn->query("SELECT name FROM categories ORDER BY name");

if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}
?>