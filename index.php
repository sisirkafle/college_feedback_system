<?php
session_start();
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Feedback System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-body">
<div class="landing-page">

  <!-- Header / Navigation -->
  <header class="landing-header">
    <div class="landing-nav-container">
      <a href="index.php" class="brand-logo">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span>College Feedback</span>
      </a>
      <nav class="nav-actions">
        <a href="login.php" class="nav-btn nav-btn-secondary">Student Login</a>
        <a href="register.php" class="nav-btn nav-btn-primary">Register</a>
        <a href="admin_login.php" class="nav-btn nav-btn-outline">Admin Portal</a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-backdrop"></div>
    <div class="hero-container">
      <div class="hero-badge">
        <span class="pulse-dot"></span> Student Voice & Resolution Platform
      </div>
      <h1 class="hero-title">Empowering Student Voices,<br><span class="gradient-text">Improving College Life</span></h1>
      <p class="hero-subtitle">
        Share feedback about teachers, bus services, canteen, library, infrastructure, and more — either with your name or anonymously. Every submission is reviewed by administration with real-time status tracking.
      </p>
      <div class="hero-cta">
        <a href="login.php" class="btn-hero-primary">
          <span>Student Login</span>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
        <a href="register.php" class="btn-hero-secondary">Create Student Account</a>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="landing-section-full">
    <div class="landing-container">
      <div class="section-header">
        <span class="section-tag">Simple Process</span>
        <h2>How It Works</h2>
        <p>Three straightforward steps to submit feedback and get official admin responses.</p>
      </div>

      <div class="steps-grid">
        <div class="step-card">
          <div class="step-icon-wrapper">
            <span class="step-num">1</span>
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
          </div>
          <h3>Submit Feedback</h3>
          <p>Choose a category, write your detailed feedback, and optionally attach image proof. Choose to stay anonymous or identified.</p>
        </div>

        <div class="step-card">
          <div class="step-icon-wrapper">
            <span class="step-num">2</span>
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
          </div>
          <h3>Admin Review</h3>
          <p>Every submission is reviewed by college administration. Anonymous feedback undergoes approval checks before publishing.</p>
        </div>

        <div class="step-card">
          <div class="step-icon-wrapper">
            <span class="step-num">3</span>
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          </div>
          <h3>Get a Response</h3>
          <p>Track submission progress in real-time (Pending, Reviewed, In Progress, Resolved) and read official replies from your dashboard.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="landing-section-full bg-light">
    <div class="landing-container">
      <div class="section-header">
        <span class="section-tag">Coverage</span>
        <h2>Feedback Categories</h2>
        <p>Voice your suggestions and concerns across all campus departments.</p>
      </div>

      <div class="category-grid">
        <?php foreach ($categories as $cat) { ?>
          <div class="category-card">
            <span class="cat-dot"></span>
            <span class="cat-name"><?= htmlspecialchars($cat) ?></span>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="landing-footer">
    <div class="landing-container footer-content">
      <div class="footer-brand">
        <div class="brand-logo">
          <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          <span>College Feedback System</span>
        </div>
        <p class="footer-desc">A transparent platform connecting students and college administration for continuous institutional improvement.</p>
      </div>
      <div class="footer-links">
        <div class="footer-col">
          <h4>Portals</h4>
          <a href="login.php">Student Login</a>
          <a href="register.php">Student Register</a>
          <a href="admin_login.php">Admin Login</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="landing-container flex-between">
        <p>&copy; <?= date('Y') ?> College Feedback System. All rights reserved.</p>
        <a href="admin_login.php" class="admin-link">Administrator Portal &rarr;</a>
      </div>
    </div>
  </footer>

</div>
</body>
</html>

