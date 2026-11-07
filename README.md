# StressGuard AI (PHP)

Upload the contents of this folder to `htdocs/` on the host (InfinityFree, XAMPP, etc.). No Python or database needed.

    index.php      landing page
    cek.php        form + result (also the prediction endpoint: cek.php?api=predict)
    tentang.php    model transparency page
    inc/           shared PHP (settings, model engine, header/footer)
    model/         stress_model.json (Random Forest exported for PHP) + model_meta.json
    static/        css, js, icons, fonts (all self-hosted)

Edit the team / campus / counseling contact at the top of `inc/bootstrap.php`.
Retrain: `python train_model.py`, then export the new model to `model/` (same format).
Needs PHP 7.4+.
