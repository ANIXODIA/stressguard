// StressGuard AI front end (cek.php): live slider values, POST to the PHP model endpoint, reset.
(function () {
  const form = document.getElementById("predict-form");
  if (!form) return;                       // other pages have no form

  const btn = document.getElementById("predict-btn");
  const resetBtn = document.getElementById("reset-btn");
  const out = document.getElementById("result");
  const sliders = Array.from(form.querySelectorAll("input[type=range]"));

  // ---- slider labels / fill (formats mirror format_value() in inc/bootstrap.php) ----
  const comma = (v) => String(+v).replace(".", ",");
  function format(kind, v) {
    switch (kind) {
      case "hours":   return comma(v) + " jam";
      case "score":   return Math.round(v) + " / 10";
      case "percent": return Math.round(v * 100) + "%";
      default:        return String(Math.round(v));
    }
  }
  function sync(input) {
    const lo = +input.min, hi = +input.max;
    input.style.setProperty("--pct", ((input.value - lo) / (hi - lo)) * 100 + "%");
    const field = input.closest(".field");
    field.querySelector(".val").textContent = format(field.dataset.kind, input.value);
  }
  sliders.forEach((el) => {
    sync(el);
    el.addEventListener("input", () => sync(el));
  });

  resetBtn && resetBtn.addEventListener("click", () => {
    sliders.forEach((el) => { el.value = el.closest(".field").dataset.default; sync(el); });
  });

  // ---- prediction ---------------------------------------------------------------
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const payload = {};
    sliders.forEach((el) => (payload[el.name] = parseFloat(el.value)));

    const label = btn.querySelector(".btn-text");
    btn.disabled = true;
    label.textContent = "Menghitung…";
    try {
      const res = await fetch(form.dataset.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        out.innerHTML = await res.text();
      } else {
        let msg = "Terjadi kesalahan (HTTP " + res.status + "). Coba lagi.";
        try { msg = (await res.json()).error || msg; } catch (_) {}
        out.innerHTML = '<div class="result-error" role="alert">' + esc(msg) + "</div>";
      }
    } catch (_) {
      out.innerHTML = '<div class="result-error" role="alert">Tidak dapat menghubungi server.</div>';
    } finally {
      btn.disabled = false;
      label.textContent = "Prediksi";
      // when the result sits below the form (narrow screens), bring it into view
      if (window.matchMedia("(max-width:1024px)").matches) out.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });
})();
