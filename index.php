<?php
require __DIR__ . '/config/functions.php';
$data = gp_load_data();
$user = gp_current_user();
?>
<!doctype html>
<html lang="sw">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoalPesa - Wekeza kwa urahisi</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <header>
      <div class="container nav">
        <div class="brand">
          <span style="width:10px;height:10px;border-radius:999px;background:#22c55e;display:inline-block"></span>
          <span>GoalPesa</span>
          <span class="pill">Digital Investment</span>
        </div>
        <div>
          <?php if ($user): ?>
            <a class="btn" href="/dashboard.php">Dashibodi</a>
            <a class="btn" href="/logout.php">Toka</a>
          <?php else: ?>
            <a class="btn" href="/login.php">Ingia</a>
            <a class="btn primary" href="/register.php">Jisajili</a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <main class="container">
      <section class="hero">
        <div class="card">
          <div class="slider">
            <div class="slides">
              <div class="slide"><h2>Wekeza kwa urahisi na GoalPesa</h2></div>
              <div class="slide"><h2>Makato madogo wakati wa kutoa tu</h2></div>
              <div class="slide"><h2>Dashboard binafsi kwa kila mtumiaji</h2></div>
            </div>
            <div class="dots">
              <div class="dot active"></div>
              <div class="dot"></div>
              <div class="dot"></div>
            </div>
          </div>
        </div>
        <div class="card">
          <h2>Karibu GoalPesa</h2>
          <p class="muted">Mfumo rahisi wa uwekezaji wa kidigitali. Weka fedha zako, fuatilia salio, na toa kwa uwazi.</p>
          <div class="row" style="margin-top:16px">
            <a class="btn primary" href="/register.php">Anza Sasa</a>
            <a class="btn" href="/login.php">Nina akaunti tayari</a>
          </div>
        </div>
      </section>

      <section class="grid" style="grid-template-columns:2fr 1fr">
        <div class="card">
          <h3>Makala na Vidokezo</h3>
          <div class="grid" style="grid-template-columns:1fr 1fr">
            <?php foreach ($data['posts'] as $p): ?>
              <div class="card">
                <h4><?= gp_sanitize($p['title']) ?></h4>
                <p class="muted"><?= gp_sanitize($p['content']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <aside class="card">
          <h3>Taarifa Fupi</h3>
          <ul>
            <li><span class="muted">Makato ya kutoa:</span> 6% kawaida, 2% endapo salio ≥ 3× ya uwekeji wa kwanza.</li>
            <li><span class="muted">Amana:</span> Hakuna makato kwenye kuweka.</li>
            <li><span class="muted">Usalama:</span> Session + CSRF ulinzi.</li>
          </ul>
        </aside>
      </section>
    </main>

    <footer>
      <div class="container footer">
        <div>© <?= date('Y') ?> GoalPesa</div>
        <div><a href="/admin/">Admin</a></div>
      </div>
    </footer>
    <script src="/assets/js/main.js" defer></script>
  </body>
  </html>