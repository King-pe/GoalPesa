<?php
session_start();
require_once __DIR__ . '/config/functions.php';

$data = read_json();
$posts = $data['posts'] ?? [];

$loggedIn = isset($_SESSION['user_id']);
$currentUser = current_user($data);
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GoalPesa - Uwekezaji</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
  <div class="logo">
    <img src="assets/images/logo.png" alt="GoalPesa" height="40">
    <span class="brand">GoalPesa</span>
  </div>
  <nav>
    <?php if ($loggedIn): ?>
      <span class="welcome">Karibu, <?= htmlspecialchars($currentUser['jina'] ?? 'Mteja') ?></span>
      <a href="dashboard.php" class="btn">Dashboard</a>
      <a href="logout.php" class="btn">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn">Login</a>
      <a href="register.php" class="btn">Register</a>
    <?php endif; ?>
  </nav>
</header>

<section class="slider">
  <div class="slides">
    <div class="slide active">
      <img src="assets/images/slide1.jpg" alt="Invest smart">
      <div class="caption">
        <h2>Wekeza kwa Uhakika</h2>
        <p>Anza safari yako ya kifedha na GoalPesa leo!</p>
      </div>
    </div>
    <div class="slide">
      <img src="assets/images/slide2.jpg" alt="Grow money">
      <div class="caption">
        <h2>Ongeza Mitaji Yako</h2>
        <p>Fuatilia salio na faida kwa urahisi kupitia dashboard yako.</p>
      </div>
    </div>
    <div class="slide">
      <img src="assets/images/slide3.jpg" alt="Financial freedom">
      <div class="caption">
        <h2>Uhuru wa Kifedha</h2>
        <p>Kuwa sehemu ya mapinduzi ya uwekezaji kidigitali.</p>
      </div>
    </div>
  </div>
  <div class="controls">
    <span class="dot active"></span>
    <span class="dot"></span>
    <span class="dot"></span>
  </div>
</section>

<main class="dashboard">
  <section class="card">
    <h2>Karibu GoalPesa</h2>
    <p>
      GoalPesa ni mfumo rahisi wa uwekezaji. Unaweza kuwekeza kiasi kuanzia 
      <strong>5000 TZS</strong> na kufuatilia salio lako kupitia dashboard. 
      Kampuni hukata <strong>fee kulingana na mkataba</strong> wakati wa payout na <strong><?= (int)(compute_early_withdrawal_fee()*100) ?>%</strong> endapo utavunja mkataba mapema.
    </p>
  </section>

  <section class="card">
    <h3>Habari &amp; Makala za Uwekezaji</h3>
    <?php if (count($posts) > 0): ?>
      <?php foreach ($posts as $p): ?>
        <article style="margin-bottom:15px;">
          <h4 style="color:#273c75;"><?= htmlspecialchars($p['title']) ?></h4>
          <p><?= nl2br(htmlspecialchars($p['content'])) ?></p>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Hakuna post kwa sasa. Tafadhali rudi baadaye.</p>
    <?php endif; ?>
  </section>
</main>

<footer class="footer">
  <div>
    &copy; <?= date("Y") ?> GoalPesa. Haki zote zimehifadhiwa. 
    <a class="footer-link" href="mkataba.php">Mkataba wa Huduma</a>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>