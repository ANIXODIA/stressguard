<?php
/**
 * StressGuard AI - shared config, helpers and the model engine.
 * Included by index.php, cek.php and tentang.php. Compatible with PHP 7.4+.
 */
declare(strict_types=1);

// ============================================================ settings ==
const SITE_NAME   = 'StressGuard AI';
const TEAM        = 'Kelompok 2';
const CAMPUS      = 'Universitas Bunda Mulia';
const DATASET_URL = 'https://www.kaggle.com/datasets/rishavraj1232/student-early-stress-detection';

// Counseling contact shown on the "Tinggi" result (phone / e-mail / URL).
// Leave '' until you have the official one; the card then points to the campus' official channels.
const COUNSELING_CONTACT = '';

const MODEL_PATH = __DIR__ . '/../model/stress_model.json';
const META_PATH  = __DIR__ . '/../model/model_meta.json';

const DISCLAIMER = 'Ini bukan diagnosis medis. Hasil tidak menggantikan penilaian psikolog atau tenaga kesehatan.';

// ========================================================== indicators ==
// 'name' must match the feature names inside model/stress_model.json.
const GROUPS = [
    'habit'   => 'Kebiasaan harian',
    'study'   => 'Beban akademik',
    'emotion' => 'Kondisi emosional',
];

const FIELDS = [
    ['name' => 'sleep_hours_per_day',      'label' => 'Jam tidur',          'caption' => 'Rata-rata tidur per malam',        'min' => 4, 'max' => 10, 'step' => 0.5,  'default' => 6.5,  'kind' => 'hours',   'group' => 'habit'],
    ['name' => 'study_hours_per_week',     'label' => 'Jam belajar',        'caption' => 'Total jam belajar per minggu',     'min' => 2, 'max' => 35, 'step' => 1,    'default' => 24,   'kind' => 'hours',   'group' => 'habit'],
    ['name' => 'academic_pressure_score',  'label' => 'Tekanan akademik',   'caption' => '0 = ringan · 10 = sangat berat',   'min' => 0, 'max' => 10, 'step' => 1,    'default' => 7,    'kind' => 'score',   'group' => 'study'],
    ['name' => 'attendance_rate',          'label' => 'Kehadiran kuliah',   'caption' => 'Seberapa sering kamu hadir',       'min' => 0, 'max' => 1,  'step' => 0.01, 'default' => 0.85, 'kind' => 'percent', 'group' => 'study'],
    ['name' => 'missed_submissions_count', 'label' => 'Tugas terlewat',     'caption' => 'Jumlah tugas yang terlewat',       'min' => 0, 'max' => 20, 'step' => 1,    'default' => 2,    'kind' => 'count',   'group' => 'study'],
    ['name' => 'anxiety_score',            'label' => 'Rasa cemas',         'caption' => '0 = ringan · 10 = sangat berat',   'min' => 0, 'max' => 10, 'step' => 1,    'default' => 4,    'kind' => 'score',   'group' => 'emotion'],
    ['name' => 'depression_score',         'label' => 'Perasaan murung',    'caption' => '0 = ringan · 10 = sangat berat',   'min' => 0, 'max' => 10, 'step' => 1,    'default' => 3,    'kind' => 'score',   'group' => 'emotion'],
    ['name' => 'burnout_score',            'label' => 'Kelelahan (burnout)', 'caption' => '0 = ringan · 10 = sangat berat',  'min' => 0, 'max' => 10, 'step' => 1,    'default' => 5,    'kind' => 'score',   'group' => 'emotion'],
];

// ============================================================ results ==
function results(): array
{
    $campus_tip = 'Hubungi layanan konseling ' . CAMPUS . '.';
    return [
        'Rendah' => [
            'icon' => 'sprout', 'level' => 0,
            'text' => 'Ritme harianmu tampak cukup terjaga. Tetap beri ruang untuk istirahat dan hal yang kamu sukai.',
            'tips' => ['Pertahankan waktu tidur yang teratur.', 'Sisihkan jeda untuk hal yang menyenangkan.'],
            'closing' => 'Jaga ritmemu, bukan kesempurnaan.', 'counseling' => false,
            'summary' => 'Ritme harian terjaga dengan baik.',
        ],
        'Sedang' => [
            'icon' => 'cloud-sun', 'level' => 1,
            'text' => 'Ada beban yang mungkin sedang terasa. Kamu boleh melambat dan membagi tugas menjadi langkah yang lebih kecil.',
            'tips' => ['Pilih satu tugas kecil untuk dimulai.', 'Ambil jeda dan cerita ke orang tepercaya.'],
            'closing' => 'Tidak semua harus selesai hari ini.', 'counseling' => false,
            'summary' => 'Ada beban yang mulai terasa.',
        ],
        'Tinggi' => [
            'icon' => 'heart-handshake', 'level' => 2,
            'text' => 'Bebanmu mungkin terasa berat. Kamu tidak perlu menghadapinya sendirian; dukungan layak kamu dapatkan.',
            'tips' => ['Jeda sejenak dari aktivitas yang membebani.', $campus_tip],
            'closing' => 'Mencari dukungan adalah langkah berani.', 'counseling' => true,
            'summary' => 'Beban terasa berat, dukungan layak dicari.',
        ],
    ];
}

// ============================================================ helpers ==
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Lucide icon (static/icons/NAME.svg) drawn as a CSS mask so it follows the text colour. */
function icon(string $name, int $size = 16, string $color = ''): string
{
    // The URL must sit in the inline style (not in a CSS variable) so it resolves relative to the page, not the stylesheet.
    $url = "url('static/icons/" . e($name) . ".svg')";
    $style = "-webkit-mask-image:$url;mask-image:$url;width:{$size}px;height:{$size}px;"
           . ($color !== '' ? 'color:' . e($color) . ';' : '');
    return '<span class="ic" aria-hidden="true" style="' . $style . '"></span>';
}

function pct(float $v): string
{
    return str_replace('.', ',', number_format($v * 100, 1, '.', '')) . '%';
}

/** Default text of a slider value (JS keeps it live afterwards; keep both formatters in sync). */
function format_value(array $f, $v): string
{
    switch ($f['kind']) {
        case 'hours':   return str_replace('.', ',', (string) (float) $v) . ' jam';
        case 'score':   return (int) $v . ' / 10';
        case 'percent': return (int) round($v * 100) . '%';
        default:        return (string) (int) $v;
    }
}

function end_label(array $f, $v): string
{
    switch ($f['kind']) {
        case 'hours':   return $v . ' jam';
        case 'percent': return (int) round($v * 100) . '%';
        default:        return (string) $v;
    }
}

// ============================================================== model ==
function load_meta(): array
{
    if (!is_file(META_PATH)) { return []; }
    $m = json_decode((string) file_get_contents(META_PATH), true);
    return is_array($m) ? $m : [];
}

/** Facts shown on the "Tentang model" page, all read from model_meta.json. */
function about_info(): array
{
    $meta = load_meta();
    $ds   = $meta['dataset'] ?? [];
    $m    = $meta['metrics'] ?? null;
    return [
        'algorithm'  => $meta['algorithm'] ?? 'Belum tersedia',
        'validation' => $meta['validation'] ?? 'Belum tersedia',
        'rows'       => isset($ds['rows']) ? number_format((int) $ds['rows'], 0, ',', '.') : '—',
        'columns'    => $ds['columns'] ?? '—',
        'test_rows'  => isset($meta['test_rows']) ? number_format((int) $meta['test_rows'], 0, ',', '.') : '—',
        'metrics'    => $m ? ['accuracy' => pct((float) $m['accuracy']), 'macro_f1' => pct((float) $m['macro_f1']), 'accuracy_raw' => (float) $m['accuracy']] : null,
        'ranges'     => $meta['feature_ranges'] ?? [],
    ];
}

/** Random Forest stored as JSON: average the class probabilities of the leaf each tree lands in. */
function predict_label(array $model, array $x): string
{
    $sum = array_fill(0, count($model['classes']), 0.0);
    foreach ($model['trees'] as $t) {
        $i = 0;
        while ($t['l'][$i] !== -1) {
            $i = $x[$t['f'][$i]] <= $t['t'][$i] ? $t['l'][$i] : $t['r'][$i];   // left when value <= threshold
        }
        foreach ($t['v'][$i] as $k => $p) { $sum[$k] += $p; }
    }
    $best = 0;
    foreach ($sum as $k => $v) { if ($v > $sum[$best]) { $best = $k; } }       // first max wins, like sklearn
    return $model['classes'][$best];
}

function json_error(int $code, string $msg): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

/** POST cek.php?api=predict  (JSON body with the 8 values) -> HTML of the result card. */
function handle_predict_api(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_error(405, 'Metode tidak diizinkan.'); }
    if (!is_file(MODEL_PATH)) { json_error(503, 'File model/stress_model.json belum diunggah.'); }
    $model = json_decode((string) file_get_contents(MODEL_PATH), true);
    if (!is_array($model) || empty($model['trees'])) { json_error(500, 'File model tidak dapat dibaca.'); }

    $data = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($data)) { $data = []; }

    $by_name = [];
    foreach (FIELDS as $f) { $by_name[$f['name']] = $f; }

    $row = [];
    foreach ($model['features'] as $idx => $name) {
        if (!isset($data[$name]) || !is_numeric($data[$name])) { json_error(400, "Nilai '$name' tidak valid."); }
        $v = (float) $data[$name];
        if (isset($by_name[$name])) {                                   // clamp to the slider range
            $v = min(max($v, (float) $by_name[$name]['min']), (float) $by_name[$name]['max']);
        }
        $row[$idx] = $v;
    }

    $label = predict_label($model, $row);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo result_card($label, results()[$label], false);
    exit;
}

// ============================================================ result UI ==
/** Result card. $example = the placeholder card shown before any prediction. */
function result_card(string $label, array $r, bool $example): string
{
    ob_start(); ?>
<div class="note <?= $example ? 'note--example' : 'note--result' ?>">
  <div class="tape"></div>
  <div class="note-marker"><?= $example ? 'CONTOH HASIL · BUKAN PREDIKSI AKTUAL' : 'HASIL PREDIKSI MODEL' ?></div>
  <div class="note-cat">
    <div>
      <small>KATEGORI STRES</small>
      <span class="note-name display"><?= e($label) ?></span>
    </div>
    <?= icon($r['icon'], 44) ?>
  </div>
  <div class="scale" aria-hidden="true"><?php for ($n = 0; $n < 3; $n++): ?><i class="<?= $n === $r['level'] ? 'on' : '' ?>"></i><?php endfor; ?></div>
  <p class="note-text"><?= e($r['text']) ?></p>
  <div class="steps">
    <b>Yang bisa kamu coba:</b>
    <?php foreach ($r['tips'] as $t): ?><div><?= icon('check', 16) ?><span><?= e($t) ?></span></div><?php endforeach; ?>
  </div>
  <?php if ($r['counseling'] && !$example): ?>
  <div class="contact">
    <b>Layanan konseling <?= e(CAMPUS) ?></b>
    <?php if (COUNSELING_CONTACT !== ''): ?>
      <span><?= e(COUNSELING_CONTACT) ?></span>
    <?php else: ?>
      <span class="muted">Cek kanal resmi kampus untuk informasi layanan konseling.</span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <p class="note-closing display"><?= e($r['closing']) ?></p>
  <div class="note-rule"></div>
  <p class="note-disclaimer"><?= e(DISCLAIMER) ?></p>
</div>
<p class="foot-note">
  <?= $example
      ? 'Contoh di atas hanya menunjukkan tampilan hasil. Tekan “Prediksi” untuk hasil dari model.'
      : 'Ini keluaran model dari tiga kategori (rendah / sedang / tinggi), bukan diagnosis.' ?>
</p>
<?php
    return (string) ob_get_clean();
}
