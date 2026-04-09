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
    var pauseBtn = root.querySelector("[data-hero-fs-pause]");
    var pauseLabelEl = pauseBtn ? pauseBtn.querySelector(".hero-fs-pause-label") : null;

    if (!viewport || !track || slides.length === 0) return;
    if (dots.length !== slides.length) return;

    var idx = 0;
    var intervalMs = 6500;
    var timer = null;
    var userPaused = false;
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
      if (prefersReduced || userPaused) return;
      if (timer) return;
      timer = setInterval(function () {
        if (hoverPaused) return;
        window.requestAnimationFrame(function () {
          setIndex(idx + 1);
        });
      }, intervalMs);
    }

    function syncPauseUi() {
      if (!pauseBtn) return;
      pauseBtn.setAttribute("aria-pressed", userPaused ? "true" : "false");
      if (pauseLabelEl) pauseLabelEl.textContent = userPaused ? "Play" : "Pause";
    }

    dots.forEach(function (d, i) {
      d.addEventListener("click", function () {
        setIndex(i);
        stopAuto();
        startAuto();
      });
    });

    if (pauseBtn) {
      pauseBtn.addEventListener("click", function () {
        userPaused = !userPaused;
        syncPauseUi();
        if (userPaused) stopAuto();
        else startAuto();
      });
      syncPauseUi();
    }

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
    var contactStartedAt = Date.now();
    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Bot/spam guards (best-effort on a no-backend mailto form)
      var honeypot = (contactForm.querySelector('[name="website"]') || {}).value || "";
      if (honeypot.trim()) return;

      if (Date.now() - contactStartedAt < 1600) return;

      try {
        var last = Number(window.localStorage.getItem("str_last_contact_submit") || "0");
        if (last && Date.now() - last < 30000) return;
        window.localStorage.setItem("str_last_contact_submit", String(Date.now()));
      } catch (err) {
        // ignore
      }

      var to = contactForm.getAttribute("data-mailto") || "strinvestmentservicesltd@gmail.com";

      function cleanText(s, max) {
        s = String(s || "");
        if (typeof max === "number") s = s.slice(0, max);
        // prevent header injection / weird control chars
        s = s.replace(/[\r\n\t]+/g, " ").trim();
        // keep it simple; mailto body is encoded anyway
        return s;
      }

      var name = cleanText((contactForm.querySelector('[name="name"]') || {}).value, 80);
      var phone = cleanText((contactForm.querySelector('[name="phone"]') || {}).value, 30);
      var email = cleanText((contactForm.querySelector('[name="email"]') || {}).value, 120);
      var product = cleanText((contactForm.querySelector('[name="product"]') || {}).value, 24);
      var msg = cleanText((contactForm.querySelector('[name="message"]') || {}).value, 1200);

      var productLabel = {
        general: "Loan enquiry",
        personal: "Personal loan",
        advance: "Salary advance",
        school: "Back to school",
        sme: "SME term loan",
      }[product] || "Loan enquiry";

      var sub = encodeURIComponent("Enquiry — STR Investment — " + productLabel);
      var body = encodeURIComponent(
        ["Name: " + name, "Phone: " + phone, "Email: " + email, "Subject: " + productLabel, "", msg].join("\n")
      );
      window.location.href = "mailto:" + to + "?subject=" + sub + "&body=" + body;
    });
  }
})();
