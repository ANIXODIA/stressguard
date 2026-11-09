<?php
/** StressGuard AI - model transparency page. Everything here is read from model/model_meta.json. */
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$about = about_info();
$m = $about['metrics'];
page_head('Tentang model', 'tentang', 'Algoritma, dataset, validasi, dan hasil evaluasi di balik StressGuard AI.');
?>

  <section class="page-hero">
    <div class="wrap">
      <div class="eyebrow"><i></i>TRANSPARANSI ITU PENTING</div>
      <h1 class="display">Kenali model di balik hasilnya.</h1>
      <p class="lead">StressGuard AI memakai prediksi berbasis machine learning yang dilatih dari dataset publik. Berikut detail yang perlu kamu ketahui.</p>
    </div>
  </section>

  <!-- ================= Facts + evaluation ================= -->
  <section class="section section--alt section--tight">
    <div class="wrap about-grid">
      <div class="card">
        <h2>Ringkasan model</h2>
        <dl class="facts">
          <div><dt>Algoritma</dt><dd><?= e($about['algorithm']) ?></dd></div>
          <div><dt>Dataset</dt><dd>Student Early Stress Detection<small>Rishav Raj · Kaggle · <?= e($about['columns']) ?> atribut</small></dd></div>
          <div><dt>Data latih &amp; uji</dt><dd><?= e($about['rows']) ?> baris<small><?= e($about['test_rows']) ?> baris dipisahkan sebagai data uji</small></dd></div>
          <div><dt>Validasi</dt><dd><?= e($about['validation']) ?></dd></div>
          <div><dt>Keluaran</dt><dd>Rendah · Sedang · Tinggi</dd></div>
        </dl>
      </div>

      <div class="card">
        <div class="card-title"><?= icon('chart-no-axes-combined', 24, 'var(--pink)') ?><h2>Akurasi model</h2></div>
        <?php if ($m): ?>
          <div class="metrics">
            <div><b class="display"><?= e($m['accuracy']) ?></b><span>AKURASI</span></div>
            <div><b class="display"><?= e($m['macro_f1']) ?></b><span>MACRO-F1</span></div>
          </div>
          <p class="muted">Dihitung pada <?= e($about['test_rows']) ?> baris data uji yang tidak dipakai saat pelatihan. Angka ini menunjukkan kecocokan dengan label dataset, bukan ketepatan diagnosis.</p>
          <?php if ($m['accuracy_raw'] >= 0.98): ?>
          <p class="callout"><?= icon('info', 18, 'var(--pink)') ?><span>Skor setinggi ini biasanya berarti label dalam dataset sangat mudah ditebak dari indikator yang dipakai. Jangan dibaca sebagai jaminan ketepatan pada kondisi nyata.</span></p>
          <?php endif; ?>
        <?php else: ?>
          <p class="display pending-title">Belum dievaluasi</p>
          <p class="muted">Metrik akan muncul setelah model dilatih dan <code>model/model_meta.json</code> diunggah.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ================= Indicators ================= -->
  <section class="section">
    <div class="wrap">
      <header class="section-head">
        <div class="eyebrow">INDIKATOR</div>
        <h2 class="display">Apa saja yang dibaca model.</h2>
        <p class="lead">Nama kolom pada dataset dan rentang nilai yang muncul di data latih.</p>
      </header>

      <div class="card table-card">
        <table class="table">
          <thead><tr><th>Indikator</th><th>Kolom dataset</th><th>Rentang data latih</th></tr></thead>
          <tbody>
          <?php foreach (FIELDS as $f):
              $r = $about['ranges'][$f['name']] ?? null; ?>
            <tr>
              <td><b><?= e($f['label']) ?></b><small><?= e($f['caption']) ?></small></td>
              <td><code><?= e($f['name']) ?></code></td>
              <td><?= $r ? e(str_replace('.', ',', (string) $r[0]) . ' – ' . str_replace('.', ',', (string) $r[1])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ================= Limits ================= -->
  <section class="section section--alt">
    <div class="wrap">
      <header class="section-head">
        <div class="eyebrow">KETERBATASAN</div>
        <h2 class="display">Yang perlu diingat.</h2>
      </header>

      <div class="cols-3">
        <article class="card"><h3>Bukan diagnosis</h3><p>Hasil tidak menggantikan penilaian psikolog atau tenaga kesehatan. Gunakan sebagai bahan refleksi.</p></article>
        <article class="card"><h3>Hanya delapan indikator</h3><p>Banyak hal yang memengaruhi stres tidak tertangkap oleh delapan slider ini, seperti keluarga, kesehatan, atau keuangan.</p></article>
        <article class="card"><h3>Bergantung pada dataset</h3><p>Model hanya mengenali pola dari dataset yang dipakai melatihnya, sehingga belum tentu cocok untuk semua mahasiswa.</p></article>
      </div>

      <div class="actions actions--center">
        <a class="btn" href="cek.php">Coba cek sekarang <?= icon('arrow-up-right', 18) ?></a>
        <a class="btn btn--ghost" href="<?= e(DATASET_URL) ?>" target="_blank" rel="noopener">Lihat dataset di Kaggle</a>
      </div>
    </div>
  </section>

<?php page_foot();
