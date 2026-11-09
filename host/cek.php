<?php
/** StressGuard AI - check page (form + result) and the prediction endpoint (?api=predict). */
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

if (($_GET['api'] ?? '') === 'predict') {
    handle_predict_api();            // prints the result card and exits
}

require __DIR__ . '/inc/layout.php';
page_head('Cek stres', 'cek', 'Isi catatan keseharianmu dan lihat gambaran tingkat stres dari model machine learning.');
?>

  <section class="page-hero">
    <div class="wrap">
      <div class="eyebrow"><i></i>FORM PREDIKSI</div>
      <h1 class="display">Bagaimana kabarmu belakangan ini?</h1>
      <p class="lead">Sesuaikan slider dengan yang kamu rasakan, bukan yang diharapkan orang lain. Nilai awal hanyalah contoh. Ini bukan diagnosis medis.</p>
    </div>
  </section>

  <section class="section section--alt section--tight">
    <div class="wrap check-grid">

      <!-- ---------- form ---------- -->
      <form class="card form" id="predict-form" data-endpoint="cek.php?i=1&amp;api=predict" autocomplete="off" novalidate>
        <div class="form-head">
          <h2>Catatan keseharianmu</h2>
          <?= icon('pencil', 22, 'var(--pink)') ?>
        </div>

        <?php foreach (GROUPS as $gkey => $gname): ?>
        <fieldset class="group">
          <legend><?= e($gname) ?></legend>
          <div class="group-fields">
            <?php foreach (FIELDS as $f): if ($f['group'] !== $gkey) { continue; } $id = 'f-' . $f['name']; ?>
            <div class="field" data-kind="<?= e($f['kind']) ?>" data-default="<?= e($f['default']) ?>">
              <div class="field-top">
                <label for="<?= e($id) ?>"><?= e($f['label']) ?></label>
                <output for="<?= e($id) ?>" class="val"><?= e(format_value($f, $f['default'])) ?></output>
              </div>
              <p class="hint"><?= e($f['caption']) ?></p>
              <input type="range" id="<?= e($id) ?>" name="<?= e($f['name']) ?>"
                     min="<?= e($f['min']) ?>" max="<?= e($f['max']) ?>" step="<?= e($f['step']) ?>" value="<?= e($f['default']) ?>">
              <div class="ends"><span><?= e(end_label($f, $f['min'])) ?></span><span><?= e(end_label($f, $f['max'])) ?></span></div>
            </div>
            <?php endforeach; ?>
          </div>
        </fieldset>
        <?php endforeach; ?>

        <div class="form-actions">
          <button class="btn btn--grow" type="submit" id="predict-btn">
            <span class="btn-text">Prediksi</span> <?= icon('arrow-up-right', 18) ?>
          </button>
          <button class="btn btn--ghost" type="button" id="reset-btn">Setel ulang</button>
        </div>
        <p class="foot-note">Hasil dihitung oleh model machine learning yang dilatih dari dataset. Bukan diagnosis medis.</p>
      </form>

      <!-- ---------- result ---------- -->
      <aside class="result-panel" aria-live="polite">
        <div class="eyebrow">RUANG UNTUK MEMAHAMI DIRI</div>
        <h2 class="display">Sebuah gambaran, bukan label.</h2>
        <div id="result"><?= result_card('Sedang', results()['Sedang'], true) ?></div>
      </aside>

    </div>
  </section>

<?php page_foot();
