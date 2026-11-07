"""
Train the StressGuard AI model and write the files the website (app.py) loads:
    model/model.joblib      -> scikit-learn Pipeline
    model/model_meta.json   -> algorithm, dataset facts, validation, real test metrics

Works locally OR in Google Colab:
    Colab:  !pip -q install kagglehub   then   %run train_model.py   (download model/ afterwards)
    Local:  python train_model.py --csv path/to/file.csv

You MUST check the two CONFIG blocks below against the real column names
(the script prints them and stops with a clear message if something is missing).
"""
import argparse, glob, json, os, sys
import numpy as np, pandas as pd, joblib
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import HistGradientBoostingClassifier, RandomForestClassifier
from sklearn.impute import SimpleImputer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, classification_report, f1_score
from sklearn.model_selection import GroupShuffleSplit, StratifiedKFold, cross_val_score, train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler

# ---------------- CONFIG 1: features shown on the website ----------------
# {name used by the website : column name in the dataset CSV}
# The names on the left come from the Figma design. Change the RIGHT side if the CSV differs.
FEATURE_MAP = {
    "sleep_hours_per_day":      "sleep_hours_per_day",
    "study_hours_per_week":     "study_hours_per_week",
    "academic_pressure_score":  "academic_pressure_score",
    "anxiety_score":            "anxiety_score",
    "depression_score":         "depression_score",
    "burnout_score":            "burnout_score",
    "attendance_rate":          "attendance_rate",
    "missed_submissions_count": "missed_submissions_count",
}

# ---------------- CONFIG 2: target ----------------
TARGET = "stress_level"      # column to predict
GROUP_COL = None             # e.g. "id_student" if a student has many rows (prevents leakage)
# -------------------------------------------------------------------------

CLASSES = ["Rendah", "Sedang", "Tinggi"]   # order = low, medium, high


def find_csv(path_arg):
    if path_arg:
        return path_arg
    try:
        import kagglehub
        folder = kagglehub.dataset_download("rishavraj1232/student-early-stress-detection")
    except Exception as e:  # not on Kaggle / no network
        sys.exit(f"Could not download the dataset ({e}). Pass --csv path/to/file.csv")
    files = glob.glob(os.path.join(folder, "**", "*.csv"), recursive=True)
    print("CSV files found:", files)
    return files[0]


def to_three_classes(y: pd.Series) -> pd.Series:
    """Return 0/1/2 = Rendah/Sedang/Tinggi."""
    if y.dtype == object:
        low = y.astype(str).str.lower()
        m = {}
        for v in low.unique():
            if any(k in v for k in ("low", "rendah", "none", "no")):
                m[v] = 0
            elif any(k in v for k in ("med", "sedang", "moderate", "mid")):
                m[v] = 1
            elif any(k in v for k in ("high", "tinggi", "severe")):
                m[v] = 2
        unknown = set(low.unique()) - set(m)
        if unknown:
            sys.exit(f"Don't know how to map target values {unknown} to Rendah/Sedang/Tinggi. Edit to_three_classes().")
        return low.map(m)
    if y.nunique() == 3:                       # already 3 numeric levels
        order = {v: i for i, v in enumerate(sorted(y.unique()))}
        return y.map(order)
    print(f"Target has {y.nunique()} numeric values -> splitting into 3 equal-sized bands (tertiles). "
          "Replace this with the dataset's own thresholds if it defines them.")
    return pd.qcut(y.rank(method="first"), 3, labels=[0, 1, 2]).astype(int)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--csv")
    args = ap.parse_args()

    csv_path = find_csv(args.csv)
    df = pd.read_csv(csv_path)
    print("Loaded", csv_path, df.shape)

    missing = [c for c in list(FEATURE_MAP.values()) + [TARGET] if c not in df.columns]
    if missing:
        sys.exit(f"\nThese columns are not in the CSV: {missing}\nAvailable columns:\n{list(df.columns)}\n"
                 "-> Edit FEATURE_MAP / TARGET at the top of train_model.py")

    keep = list(FEATURE_MAP.values()) + [TARGET] + ([GROUP_COL] if GROUP_COL else [])
    df = df[keep].drop_duplicates().dropna(subset=[TARGET])
    y = to_three_classes(df[TARGET])
    X = df[list(FEATURE_MAP.values())].rename(columns={v: k for k, v in FEATURE_MAP.items()})
    X = X.apply(pd.to_numeric, errors="coerce")
    print("Class balance:\n", y.value_counts(normalize=True).sort_index().rename(dict(enumerate(CLASSES))))

    if GROUP_COL:
        tr, te = next(GroupShuffleSplit(test_size=0.2, random_state=42).split(X, y, df[GROUP_COL]))
        Xtr, Xte, ytr, yte = X.iloc[tr], X.iloc[te], y.iloc[tr], y.iloc[te]
        validation = "80/20 split grouped by student + 5-fold CV"
    else:
        Xtr, Xte, ytr, yte = train_test_split(X, y, test_size=0.2, stratify=y, random_state=42)
        validation = "80/20 stratified split + 5-fold CV"

    prep = ColumnTransformer([("num", Pipeline([("imp", SimpleImputer(strategy="median")),
                                                ("sc", StandardScaler())]), list(X.columns))])
    candidates = {
        "Logistic Regression": LogisticRegression(max_iter=1000, class_weight="balanced"),
        "Random Forest": RandomForestClassifier(n_estimators=200, n_jobs=-1, class_weight="balanced", random_state=42),
        "Gradient Boosting": HistGradientBoostingClassifier(random_state=42),
    }
    n = min(50_000, len(Xtr)); idx = Xtr.sample(n, random_state=42).index
    cv = StratifiedKFold(5, shuffle=True, random_state=42)
    cv_scores = {}
    for name, clf in candidates.items():
        s = cross_val_score(Pipeline([("prep", prep), ("clf", clf)]), Xtr.loc[idx], ytr.loc[idx],
                            cv=cv, scoring="f1_macro", n_jobs=-1)
        cv_scores[name] = float(s.mean()); print(f"{name}: CV macro-F1 {s.mean():.3f}")
    best_name = max(cv_scores, key=cv_scores.get)

    model = Pipeline([("prep", prep), ("clf", candidates[best_name])]).fit(Xtr, ytr)
    pred = model.predict(Xte)
    acc, f1 = accuracy_score(yte, pred), f1_score(yte, pred, average="macro")
    print(classification_report(yte, pred, target_names=CLASSES))

    os.makedirs("model", exist_ok=True)
    joblib.dump(model, "model/model.joblib")
    meta = {
        "algorithm": best_name,
        "classes": CLASSES,
        "features": list(X.columns),
        "feature_ranges": {c: [float(X[c].min()), float(X[c].max())] for c in X.columns},
        "dataset": {"name": "Student Early Stress Detection", "author": "Rishav Raj",
                    "rows": int(len(df)), "columns": int(pd.read_csv(csv_path, nrows=1).shape[1])},
        "validation": validation,
        "metrics": {"accuracy": float(acc), "macro_f1": float(f1), "cv_macro_f1": cv_scores},
        "test_rows": int(len(Xte)),
    }
    json.dump(meta, open("model/model_meta.json", "w"), indent=2)
    print(f"\nSaved model/model.joblib and model/model_meta.json  (accuracy {acc:.3f}, macro-F1 {f1:.3f})")


if __name__ == "__main__":
    main()
