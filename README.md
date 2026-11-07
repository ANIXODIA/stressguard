# StressGuard AI

**Early student stress check, powered by machine learning.**
Students move eight sliders about their daily life (sleep, study, academic load, mood) and a Random Forest model returns a **low / medium / high** stress category with small, supportive next steps. The whole site is plain **PHP + HTML/CSS/JS**: the trained model runs inside PHP, so it works on any basic PHP host (XAMPP, InfinityFree, …) with **no Python server and no database**.

> ⚠️ **Not a medical tool.** Results are an indication from a model trained on a public dataset. They are not a diagnosis and do not replace a psychologist or health professional.

Built by **Kelompok 2 · Universitas Bunda Mulia**. The interface is in Indonesian.

| Landing page | Check page (with result) | About the model |
|---|---|---|
| ![Landing page](docs/screenshots/landing.png) | ![Check page](docs/screenshots/check.png) | ![Model page](docs/screenshots/model.png) |

## Features

- **Landing page** that explains how it works, what is measured and what the three results mean.
- **Check page** with eight sliders grouped into *daily habits*, *academic load* and *emotional state*, live value labels, a reset button and an instant result card.
- **Transparency page** with the algorithm, dataset, validation method, test metrics, the dataset column behind each indicator and the model's limitations.
- **Model runs in PHP**: a Random Forest exported to JSON and evaluated by a ~20-line PHP function.
- **Responsive**, accessible (keyboard-friendly sliders, skip link, reduced-motion support) and fully self-hosted (icons and fonts included, no CDN).

## How it works

```
 sliders (cek.php)                            model/stress_model.json
       │  POST JSON  ─────►  cek.php?api=predict ───► predict_label()  (average the leaf
       │                      (inc/bootstrap.php)      probabilities of every tree)
       ◄────────  HTML result card  ◄──────────────────────┘
```

Input values are validated and clamped to each slider's range on the server before prediction.

## Project structure

```
index.php            landing page
cek.php              form + result, and the prediction endpoint (?api=predict)
tentang.php          model transparency page
inc/
  bootstrap.php      settings (team, campus, contact), indicators, result texts, model engine, API
  layout.php         shared header / footer
model/
  stress_model.json  Random Forest exported for PHP
  model_meta.json    algorithm, dataset facts, validation, test metrics (shown on tentang.php)
static/              css, js, Lucide icons, fonts (Amatic SC, Courier Prime)
train_model.py       train the model with scikit-learn
export_model_json.py convert the trained forest to model/stress_model.json
docs/screenshots/    images used in this README
```

## Run it locally

Requires **PHP 7.4+** (no extensions, no database).

```bash
git clone <your-repo-url>
cd <repo>
php -S localhost:8000
# open http://localhost:8000
```

Or with **XAMPP**: copy the repo contents into `C:\xampp\htdocs\stressguard\` and open `http://localhost/stressguard/`.

## Deploy to shared hosting (e.g. InfinityFree)

1. Upload **everything** in this folder to the host's `htdocs/` directory, overwriting existing files.
2. Open `https://your-domain/`. That's it.

If the page ever looks unstyled after an update, the host is serving a cached stylesheet. CSS/JS links already carry a version tag (`?v=<file time>`), so make sure `static/css/style.css` was really overwritten and hard-refresh (`Ctrl+F5`).

## Settings you may want to change

All in `inc/bootstrap.php`:

| Constant | Purpose |
|---|---|
| `TEAM`, `CAMPUS` | Shown in the footer and the team section |
| `COUNSELING_CONTACT` | Official counseling contact shown on the *Tinggi* result (empty = points to the campus' official channels) |
| `FIELDS`, `GROUPS` | Indicators, labels, ranges and defaults |
| `results()` | Texts and tips for each category |

## Indicators

The eight inputs map to columns of the dataset (and to the features inside `stress_model.json`):

| Group | Indicator | Dataset column |
|---|---|---|
| Daily habits | Sleep hours | `sleep_hours_per_day` |
| Daily habits | Study hours | `study_hours_per_week` |
| Academic load | Academic pressure | `academic_pressure_score` |
| Academic load | Attendance | `attendance_rate` |
| Academic load | Missed submissions | `missed_submissions_count` |
| Emotional state | Anxiety | `anxiety_score` |
| Emotional state | Low mood | `depression_score` |
| Emotional state | Burnout | `burnout_score` |

## Model and dataset

- **Dataset:** [Student Early Stress Detection](https://www.kaggle.com/datasets/rishavraj1232/student-early-stress-detection) by Rishav Raj (Kaggle).
- **Algorithm:** Random Forest, 3 classes (Rendah / Sedang / Tinggi).
- **Validation:** 80/20 stratified split + 5-fold cross-validation. The exact figures live in `model/model_meta.json` and are shown on the *Tentang model* page.
- **Read the scores with care.** The reported accuracy is extremely high (≈ 99.9 %). That usually means the dataset's label is almost determined by the indicators used, so it does **not** mean the tool is 99.9 % reliable on real students.

### Retrain the model

`train_model.py` is a template pipeline; the training data is **not** included in this repo.

```bash
pip install -r requirements.txt

# 1. Open train_model.py and check FEATURE_MAP / TARGET / GROUP_COL against the real CSV columns
#    (the script stops and lists the real columns if a name does not match).
# 2. Train a Random Forest. Small values keep the web model light (~100-200 KB):
python train_model.py --algorithm rf --trees 60 --min-leaf 30        # downloads the dataset via kagglehub, or add --csv file.csv
# 3. Export it for PHP:
python export_model_json.py                                          # writes model/stress_model.json
```

`train_model.py` writes `model/model.joblib` (git-ignored) and `model/model_meta.json`. The exporter was checked against scikit-learn: 0 differing predictions on 1,000 random rows. Keep the forest small: PHP reads the whole JSON file on every prediction (200 trees without limits is ~6 MB, which is too slow for cheap hosting).

## Limitations

- Eight indicators cannot capture everything that causes stress (family, health, finances, …).
- The model only knows patterns from its dataset and may not fit every student.
- Results are three coarse categories, not scores or probabilities.
- Nothing is stored: answers are processed per request and not saved.

## Credits

- Dataset: Rishav Raj, *Student Early Stress Detection* (Kaggle).
- Icons: [Lucide](https://lucide.dev) (ISC). Fonts: Amatic SC and Courier Prime (SIL Open Font License).
- Team: **Kelompok 2**, **Universitas Bunda Mulia**.
