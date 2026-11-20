<?php
if (!isset($pageTitle)) { $pageTitle = "Jeremiah.dev"; }
$isAdminPage = strpos($_SERVER["SCRIPT_NAME"] ?? "", "/admin/") !== false;
$assetPrefix = $isAdminPage ? "../" : "";
$homePrefix = $isAdminPage ? "../" : "";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Jeremiah's developer portfolio: selected projects, technical skills, and contact information.">
  <meta name="theme-color" content="#080d1a">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, "UTF-8") ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/style.css">
  <script defer src="<?= $assetPrefix ?>assets/js/main.js"></script>
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="logo" href="<?= $homePrefix ?>index.php" aria-label="Jeremiah.dev home">Jeremiah<span>.dev</span></a>
    <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
    <nav class="main-nav" aria-label="Main navigation">
      <a href="<?= $homePrefix ?>index.php">Home</a>
      <a href="<?= $homePrefix ?>index.php#about">About</a>
      <a href="<?= $homePrefix ?>index.php#skills">Skills</a>
      <a href="<?= $homePrefix ?>index.php#projects">Projects</a>
      <a href="<?= $homePrefix ?>index.php#contact">Contact</a>
      <?php if (!$isAdminPage && file_exists(__DIR__ . "/../assets/files/cv.pdf")): ?>
        <a href="assets/files/cv.pdf" download>Download CV <span aria-hidden="true">↓</span></a>
      <?php endif; ?>
      <button class="theme-toggle" type="button" aria-label="Switch to light theme" title="Switch theme">
        <span class="theme-icon" aria-hidden="true">☼</span><span class="theme-label">Light mode</span>
      </button>
      <a class="admin-link" href="<?= $homePrefix ?>admin/login.php">Admin <span aria-hidden="true">↗</span></a>
    </nav>
  </div>
</header>
