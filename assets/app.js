(function () {
  var toggle = document.querySelector("[data-nav-toggle]");
  var drawer = document.querySelector("[data-nav-drawer]");
  if (toggle && drawer) {
    toggle.addEventListener("click", function () {
      var open = drawer.getAttribute("data-open") === "true";
      drawer.setAttribute("data-open", open ? "false" : "true");
      toggle.setAttribute("aria-expanded", open ? "false" : "true");
    });
  }

  // Full-bleed home hero carousel
  (function () {
    var root = document.querySelector("[data-hero-fullslider]");
    if (!root) return;

    var viewport = root.querySelector("[data-hero-fs-viewport]");
    var track = root.querySelector("[data-hero-fs-track]");
    var slides = Array.prototype.slice.call(root.querySelectorAll("[data-hero-fs-slide]"));
    var dots = Array.prototype.slice.call(root.querySelectorAll("[data-hero-fs-dot]"));

    if (!viewport || !track || slides.length === 0) return;
    if (dots.length !== slides.length) return;

    var idx = 0;
    var intervalMs = 6500;
    var timer = null;
    var hoverPaused = false;

    var prefersReduced =
      typeof window !== "undefined" &&
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function layout() {
      var w = Math.max(0, Math.round(viewport.getBoundingClientRect().width));
      if (!w) return;
      track.style.width = String(w * slides.length) + "px";
      slides.forEach(function (s) {
        s.style.width = String(w) + "px";
        s.style.flex = "0 0 " + String(w) + "px";
      });
      track.style.transform = "translate3d(" + String(idx * -w) + "px, 0, 0)";
    }

    function setIndex(next) {
      idx = (next + slides.length) % slides.length;
      layout();
      dots.forEach(function (d, i) {
        d.setAttribute("aria-selected", i === idx ? "true" : "false");
      });
      root.setAttribute("data-active-slide", String(idx));
    }

    function stopAuto() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function startAuto() {
      if (prefersReduced) return;
      if (timer) return;
      timer = setInterval(function () {
        if (hoverPaused) return;
        window.requestAnimationFrame(function () {
          setIndex(idx + 1);
        });
      }, intervalMs);
    }

    dots.forEach(function (d, i) {
      d.addEventListener("click", function () {
        setIndex(i);
        stopAuto();
        startAuto();
      });
    });

    viewport.addEventListener("mouseenter", function () {
      hoverPaused = true;
      stopAuto();
    });
    viewport.addEventListener("mouseleave", function () {
      hoverPaused = false;
      startAuto();
    });
    viewport.addEventListener("focusin", function () {
      hoverPaused = true;
      stopAuto();
    });
    viewport.addEventListener("focusout", function () {
      hoverPaused = false;
      startAuto();
    });

    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stopAuto();
      else startAuto();
    });

    var sx = 0,
      sy = 0,
      dx = 0,
      active = false,
      locked = false;

    viewport.addEventListener(
      "touchstart",
      function (e) {
        if (!e.touches || e.touches.length !== 1) return;
        active = true;
        locked = false;
        dx = 0;
        sx = e.touches[0].clientX;
        sy = e.touches[0].clientY;
        stopAuto();
      },
      { passive: true }
    );

    viewport.addEventListener(
      "touchmove",
      function (e) {
        if (!active || !e.touches || e.touches.length !== 1) return;
        var x = e.touches[0].clientX;
        var y = e.touches[0].clientY;
        dx = x - sx;
        var dy = y - sy;
        if (!locked) locked = Math.abs(dx) > Math.abs(dy) + 8;
        if (locked) e.preventDefault();
      },
      { passive: false }
    );

    viewport.addEventListener(
      "touchend",
      function () {
        if (!active) return;
        active = false;
        if (locked && Math.abs(dx) > 50) {
          setIndex(dx < 0 ? idx + 1 : idx - 1);
        }
        startAuto();
      },
      { passive: true }
    );

    document.addEventListener("keydown", function (e) {
      if (!root.contains(document.activeElement)) return;
      if (e.key === "ArrowLeft") {
        e.preventDefault();
        setIndex(idx - 1);
        stopAuto();
        startAuto();
      } else if (e.key === "ArrowRight") {
        e.preventDefault();
        setIndex(idx + 1);
        stopAuto();
        startAuto();
      }
    });

    setIndex(0);
    window.addEventListener("resize", layout);
    window.addEventListener("load", layout);

    if (!prefersReduced) startAuto();
  })();

  // FAQ accordion
  (function () {
    var items = Array.prototype.slice.call(document.querySelectorAll("[data-qa]"));
    if (!items.length) return;

    function closeAll() {
      items.forEach(function (other) {
        other.setAttribute("data-open", "false");
        var b = other.querySelector("[data-q]");
        if (b) b.setAttribute("aria-expanded", "false");
      });
    }

    items.forEach(function (item) {
      var btn = item.querySelector("[data-q]");
      if (!btn) return;
      btn.addEventListener("click", function () {
        var isOpen = item.getAttribute("data-open") === "true";
        closeAll();
        item.setAttribute("data-open", isOpen ? "false" : "true");
        btn.setAttribute("aria-expanded", isOpen ? "false" : "true");
      });
    });
  })();

  function mailtoForm(sel, subjectPrefix) {
    var form = document.querySelector(sel);
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var to = form.getAttribute("data-mailto") || "strinvestmentservicesltd@gmail.com";
      var email = (form.querySelector('[name="email"]') || {}).value || "";
      var sub = encodeURIComponent(subjectPrefix || "Message — STR Investment");
      var body = encodeURIComponent("Email: " + email.trim());
      window.location.href = "mailto:" + to + "?subject=" + sub + "&body=" + body;
    });
  }

  mailtoForm("[data-newsletter]", "Newsletter — STR Investment");

  var contactForm = document.querySelector("[data-contact-form]");
  if (contactForm) {
    var submitBtn = contactForm.querySelector("[data-contact-submit]");
    var btnText = submitBtn && submitBtn.querySelector(".btn__text");
    var btnBusy = submitBtn && submitBtn.querySelector(".btn__busy");
    var statusEl = contactForm.querySelector(".form-status");

    function setContactLoading(on) {
      if (!submitBtn) return;
      submitBtn.disabled = !!on;
      submitBtn.setAttribute("aria-busy", on ? "true" : "false");
      if (btnText) btnText.hidden = !!on;
      if (btnBusy) btnBusy.hidden = !on;
    }

    function showContactStatus(msg, isError) {
      if (!statusEl) return;
      statusEl.hidden = false;
      statusEl.textContent = msg;
      statusEl.classList.toggle("form-status--error", !!isError);
      statusEl.classList.toggle("form-status--ok", !isError);
    }

    function clearContactStatus() {
      if (!statusEl) return;
      statusEl.hidden = true;
      statusEl.textContent = "";
      statusEl.classList.remove("form-status--error", "form-status--ok");
    }

    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (submitBtn && submitBtn.disabled) return;

      var honeypot = (contactForm.querySelector('[name="website"]') || {}).value || "";
      if (honeypot.trim()) return;

      clearContactStatus();

      if (typeof contactForm.checkValidity === "function" && !contactForm.checkValidity()) {
        contactForm.reportValidity();
        return;
      }

      var endpoint = contactForm.getAttribute("data-submit-url") || "contact-submit.php";
      var url = new URL(endpoint, window.location.href).href;
      var fd = new FormData(contactForm);

      setContactLoading(true);

      fetch(url, {
        method: "POST",
        body: fd,
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then(function (r) {
          return r
            .json()
            .catch(function () {
              return { ok: false, error: "Could not read server response." };
            })
            .then(function (data) {
              return { status: r.status, data: data };
            });
        })
        .then(function (res) {
          var d = res.data || {};
          if (res.status >= 200 && res.status < 300 && d.ok) {
            showContactStatus(d.message || "Thank you. We will respond soon.", false);
            contactForm.reset();
            var csrfInput = contactForm.querySelector('[name="csrf_token"]');
            if (d.csrf && csrfInput) csrfInput.value = d.csrf;
            var fs = contactForm.querySelector('[name="form_started_at"]');
            if (fs) fs.value = String(Math.floor(Date.now() / 1000));
          } else {
            showContactStatus(d.error || "Something went wrong. Please try again.", true);
          }
        })
        .catch(function () {
          showContactStatus("Network error. Email strinvestmentservicesltd@gmail.com or call 09054984777.", true);
        })
        .finally(function () {
          setContactLoading(false);
        });
    });
  }
})();
