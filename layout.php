<?php
/** Shared page shell: page_head() opens the page (header), page_foot() closes it (footer + script). */
declare(strict_types=1);

function page_head(string $title, string $active, string $description = ''): void
{
    $nav = [
        'home'    => ['index.php',   'Beranda'],
        'cek'     => ['cek.php',     'Cek stres'],
        'tentang' => ['tentang.php', 'Tentang model'],
    ];
    $full = $title === '' ? SITE_NAME : $title . ' · ' . SITE_NAME;
    $description = $description !== '' ? $description
        : 'Cek sinyal stres dari keseharian berdasarkan dataset Student Early Stress Detection. Bukan diagnosis medis.';
    ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($full) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <meta name="theme-color" content="#f9f6f0">
  <link rel="preload" href="static/fonts/amatic-sc-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
<a class="skip" href="#main">Lewati ke konten</a>

<header class="site-header">
  <div class="wrap bar">
    <a class="brand" href="index.php" aria-label="<?= e(SITE_NAME) ?> - beranda">
      <?= icon('flower-2', 28, 'var(--pink)') ?><span class="display"><?= e(SITE_NAME) ?></span>
    </a>
    <nav class="nav" aria-label="Menu utama">
      <?php foreach ($nav as $key => [$href, $label]): ?>
        <a href="<?= e($href) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn--sm" href="cek.php">Mulai cek</a>
    </nav>
  </div>
</header>

<main id="main">
<?php
}

function page_foot(): void
{ ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="brand brand--light"><?= icon('flower-2', 26, 'var(--pink2)') ?><span class="display"><?= e(SITE_NAME) ?></span></div>
        <p class="display foot-tagline">Setiap langkah kecil, tetap berarti.</p>
      </div>
      <div>
        <h6>HALAMAN</h6>
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="cek.php">Cek stres</a></li>
          <li><a href="tentang.php">Tentang model</a></li>
        </ul>
      </div>
      <div>
        <h6>DI BALIK PROYEK</h6>
        <ul>
          <li><?= e(TEAM) ?></li>
          <li><?= e(CAMPUS) ?></li>
        </ul>
      </div>
      <div>
        <h6>REFERENSI</h6>
        <ul>
          <li>Student Early Stress Detection</li>
          <li>Rishav Raj · Kaggle</li>
          <li><a href="<?= e(DATASET_URL) ?>" rel="noopener" target="_blank">Buka dataset</a></li>
        </ul>
      </div>
    </div>
    <hr>
    <p class="foot-end">© 2026 <?= e(TEAM) ?> · <?= e(CAMPUS) ?> · Bukan alat diagnosis medis.</p>
  </div>
</footer>

<script src="static/js/app.js"></script>
</body>
</html>
<?php
}
