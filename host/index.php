<?php
/** StressGuard AI - landing page. */
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$results = results();
page_head('', 'home');
?>

  <!-- ================= Hero ================= -->
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy">
        <div class="eyebrow"><i></i>DETEKSI STRES AWAL BERDASARKAN DATASET</div>
        <h1 class="display">Cek sinyal stres<br>dari keseharian.</h1>
        <p class="lead">Isi catatan singkat tentang tidur, belajar, dan perasaanmu belakangan ini. Model machine learning kami memberi gambaran tingkat stres, lengkap dengan langkah kecil yang bisa dicoba. Ini bukan diagnosis medis.</p>
        <div class="actions">
          <a class="btn" href="cek.php">Mulai cek <?= icon('arrow-up-right', 18) ?></a>
          <a class="btn btn--ghost" href="#cara">Lihat cara kerja</a>
        </div>
        <p class="foot-note">Bukan ujian. Tidak ada jawaban baik atau buruk.</p>
      </div>

      <div class="hero-visual" aria-hidden="true">
        <?= result_card('Sedang', $results['Sedang'], true) ?>
      </div>
    </div>
  </section>

  <!-- ================= Quick facts ================= -->
  <section class="facts-strip">
    <div class="wrap facts-grid">
      <div><b class="display">8</b><span>indikator keseharian</span></div>
      <div><b class="display">3</b><span>kategori hasil</span></div>
      <div><b class="display">0</b><span>akun yang perlu dibuat</span></div>
    </div>
  </section>

  <!-- ================= How it works ================= -->
  <section class="section section--alt" id="cara">
    <div class="wrap">
      <header class="section-head">
        <div class="eyebrow">CARA KERJA</div>
        <h2 class="display">Tiga langkah, tanpa ribet.</h2>
        <p class="lead">Semuanya berjalan di satu halaman. Kamu hanya perlu menggeser beberapa slider sesuai yang kamu rasakan.</p>
      </header>

      <ol class="steps-grid">
        <li class="card">
          <span class="step-no display">1</span>
          <h3>Isi catatan keseharianmu</h3>
          <p>Atur delapan slider: kebiasaan tidur dan belajar, beban akademik, serta kondisi emosional.</p>
        </li>
        <li class="card">
          <span class="step-no display">2</span>
          <h3>Model membaca polanya</h3>
          <p>Model Random Forest yang dilatih dari dataset Student Early Stress Detection membandingkan jawabanmu dengan pola yang pernah dipelajarinya.</p>
        </li>
        <li class="card">
          <span class="step-no display">3</span>
          <h3>Lihat gambaran &amp; langkah kecil</h3>
          <p>Kamu mendapat kategori rendah, sedang, atau tinggi beserta saran sederhana yang bisa langsung dicoba.</p>
        </li>
      </ol>
    </div>
  </section>

  <!-- ================= Indicators ================= -->
  <section class="section">
    <div class="wrap">
      <header class="section-head">
        <div class="eyebrow">YANG DINILAI</div>
        <h2 class="display">Delapan indikator, tiga sisi kehidupan kampus.</h2>
        <p class="lead">Indikator dan rentang nilainya disesuaikan dengan kolom pada dataset yang dipakai melatih model.</p>
      </header>

      <div class="cols-3">
        <?php foreach (GROUPS as $gkey => $gname): ?>
        <article class="card">
          <h3><?= e($gname) ?></h3>
          <ul class="indicator-list">
            <?php foreach (FIELDS as $f): if ($f['group'] !== $gkey) { continue; } ?>
              <li><b><?= e($f['label']) ?></b><span><?= e($f['caption']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ================= Categories ================= -->
  <section class="section section--alt">
    <div class="wrap">
      <header class="section-head">
        <div class="eyebrow">HASILNYA</div>
        <h2 class="display">Sebuah gambaran, bukan label.</h2>
        <p class="lead">Hasil dibagi menjadi tiga kategori. Masing-masing disertai kalimat yang menenangkan dan langkah kecil yang realistis.</p>
      </header>

      <div class="cols-3">
        <?php foreach ($results as $name => $r): ?>
        <article class="card category">
          <div class="category-top">
            <h3 class="display"><?= e($name) ?></h3>
            <?= icon($r['icon'], 36, 'var(--pink)') ?>
          </div>
          <div class="scale" aria-hidden="true"><?php for ($n = 0; $n < 3; $n++): ?><i class="<?= $n === $r['level'] ? 'on' : '' ?>"></i><?php endfor; ?></div>
          <p><?= e($r['summary']) ?></p>
          <p class="muted"><?= e($r['tips'][0]) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ================= Team ================= -->
  <section class="section">
    <div class="wrap">
      <div class="card team">
        <div>
          <div class="eyebrow">DI BALIK PROYEK</div>
          <h2 class="display"><?= e(TEAM) ?></h2>
          <p class="team-campus"><?= e(CAMPUS) ?></p>
        </div>
        <p>StressGuard AI kami buat untuk menunjukkan bagaimana data bisa membantu mengenali tanda stres sejak dini, dengan bahasa yang ramah dan transparan. Dataset berasal dari
          <a href="<?= e(DATASET_URL) ?>" target="_blank" rel="noopener">Student Early Stress Detection</a> oleh Rishav Raj (Kaggle).</p>
      </div>
    </div>
  </section>

  <!-- ================= Disclaimer + CTA ================= -->
  <section class="section section--alt">
    <div class="wrap cta">
      <h2 class="display">Siap melihat gambaranmu?</h2>
      <p class="lead">Butuh kurang dari satu menit. Hasilnya adalah titik awal untuk memahami dirimu, bukan penilaian.</p>
      <a class="btn" href="cek.php">Mulai cek <?= icon('arrow-up-right', 18) ?></a>
      <p class="callout"><?= icon('info', 18, 'var(--pink)') ?><span><?= e(DISCLAIMER) ?> Jika kamu merasa kewalahan, hubungi layanan konseling <?= e(CAMPUS) ?> atau orang yang kamu percaya.</span></p>
    </div>
  </section>

<?php page_foot();
