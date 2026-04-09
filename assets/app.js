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

  // Home hero slider (visual only)
  (function () {
    var slider = document.querySelector("[data-hero-slider]");
    if (!slider) return;

    var debug =
      typeof window !== "undefined" &&
      window.location &&
      typeof window.location.search === "string" &&
      window.location.search.indexOf("debug=1") !== -1;

    var slidesEl = slider.querySelector("[data-hero-slides]");
    var slides = Array.prototype.slice.call(slider.querySelectorAll("[data-hero-slide]"));
    var dots = Array.prototype.slice.call(slider.querySelectorAll("[data-hero-dot]"));
    if (!slidesEl || slides.length === 0 || dots.length !== slides.length) return;

    var pillTextEl = document.querySelector("[data-hero-pill-text]");
    var h1PreEl = document.querySelector("[data-hero-h1-pre]");
    var h1AccentEl = document.querySelector("[data-hero-h1-accent]");
    var h1PostEl = document.querySelector("[data-hero-h1-post]");
    var leadEl = document.querySelector("[data-hero-lead]");
    var primaryEl = document.querySelector("[data-hero-primary]");
    var secondaryEl = document.querySelector("[data-hero-secondary]");

    var copyByTheme = {
      personal: {
        pill: "Incorporated February 2026",
        h1Pre: "Bridging the ",
        h1Accent: "Financing Gap",
        h1Post: " for You",
        lead:
          "STR Investment Services Limited provides accessible and innovative microcredit solutions to individuals and small businesses — fast, flexible, and customer-focused.",
        primaryHref: "contact?product=personal",
        primaryText: "Apply for a Loan",
        secondaryHref: "loans#personal",
        secondaryText: "Browse Products",
      },
      sme: {
        pill: "Incorporated February 2026",
        h1Pre: "Working capital that helps ",
        h1Accent: "SMEs grow",
        h1Post: " sustainably",
        lead:
          "Short‑term working capital for business owners — assessed on turnover and cash‑flow, with flexible tenor and clear requirements.",
        primaryHref: "contact?product=sme",
        primaryText: "Request a Call Back",
        secondaryHref: "loans#sme",
        secondaryText: "View SME Term Loan",
      },
      school: {
        pill: "Incorporated February 2026",
        h1Pre: "Support for ",
        h1Accent: "Back to School",
        h1Post: " expenses",
        lead:
          "Short‑term education support designed to cover fees and essentials, with repayment structured around school terms and your income cycle.",
        primaryHref: "contact?product=school",
        primaryText: "Apply for School Loan",
        secondaryHref: "loans#school",
        secondaryText: "View Details",
      },
    };

    var prefersReduced =
      typeof window !== "undefined" &&
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var idx = 0;
    var intervalMs = 7000;
    var timer = null;
    var lastW = 0;
    var isPaused = false;

    function render() {
      var rect = slider.getBoundingClientRect();
      var w = Math.max(0, Math.round(rect.width));
      if (!w) return;
      // Lock slide widths to real pixels (prevents peeking/subpixel issues)
      slidesEl.style.width = String(w * slides.length) + "px";
      slides.forEach(function (s) {
        s.style.width = String(w) + "px";
        s.style.flex = "0 0 " + String(w) + "px";
      });

      // Ensure a stable transform based on real pixel width (prevents slide peeking)
      slidesEl.style.transform = "translate3d(" + idx * -w + "px, 0, 0)";
      if (w !== lastW) lastW = w;

      dots.forEach(function (d, i) {
        d.setAttribute("aria-current", i === idx ? "true" : "false");
      });
      slider.setAttribute("data-index", String(idx));

      var slide = slides[idx];
      var theme = (slide && slide.getAttribute("data-theme")) || "personal";
      var c = copyByTheme[theme] || copyByTheme.personal;

      if (pillTextEl) pillTextEl.textContent = c.pill;
      if (h1PreEl) h1PreEl.textContent = c.h1Pre;
      if (h1AccentEl) h1AccentEl.textContent = c.h1Accent;
      if (h1PostEl) h1PostEl.textContent = c.h1Post;
      if (leadEl) leadEl.textContent = c.lead;
      if (primaryEl) {
        primaryEl.setAttribute("href", c.primaryHref);
        primaryEl.textContent = c.primaryText;
      }
      if (secondaryEl) {
        secondaryEl.setAttribute("href", c.secondaryHref);
        secondaryEl.textContent = c.secondaryText;
      }

      if (debug && typeof console !== "undefined") {
        try {
          var t = slidesEl.style.transform;
          console.groupCollapsed("[STR slider] render idx=" + idx);
          console.log("sliderWidthPx:", w);
          console.log("trackWidthPx:", slidesEl.style.width, "transform:", t);
          console.log(
            "slides:",
            slides.map(function (s) {
              var img = s.querySelector("img");
              return {
                theme: s.getAttribute("data-theme"),
                width: s.style.width,
                src: img ? img.getAttribute("src") : null,
                complete: img ? img.complete : null,
                naturalW: img ? img.naturalWidth : null,
                naturalH: img ? img.naturalHeight : null,
              };
            })
          );
          console.groupEnd();
        } catch (e) {
          // ignore
        }
      }
    }

    function go(next) {
      idx = (next + slides.length) % slides.length;
      render();
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
        if (isPaused) return;
        // schedule after paint to avoid jank on some browsers
        window.requestAnimationFrame(function () {
          go(idx + 1);
        });
      }, intervalMs);
    }

    dots.forEach(function (d, i) {
      d.addEventListener("click", function () {
        go(i);
        stopAuto();
        startAuto();
      });
    });

    render();
    window.addEventListener("resize", function () {
      // re-render to snap to new width
      render();
    });
    window.addEventListener("load", function () {
      // ensure widths are correct after images/layout settle
      render();
    });

    // Pause on hover / focus (accessibility + readability)
    slider.addEventListener("mouseenter", function () {
      isPaused = true;
      stopAuto();
    });
    slider.addEventListener("mouseleave", function () {
      isPaused = false;
      startAuto();
    });
    slider.addEventListener("focusin", function () {
      isPaused = true;
      stopAuto();
    });
    slider.addEventListener("focusout", function () {
      isPaused = false;
      startAuto();
    });
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stopAuto();
      else startAuto();
    });

    // Swipe support (touch devices)
    var sx = 0,
      sy = 0,
      dx = 0,
      active = false,
      locked = false;

    slider.addEventListener(
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

    slider.addEventListener(
      "touchmove",
      function (e) {
        if (!active || !e.touches || e.touches.length !== 1) return;
        var x = e.touches[0].clientX;
        var y = e.touches[0].clientY;
        dx = x - sx;
        var dy = y - sy;
        if (!locked) {
          // lock when it's clearly a horizontal swipe
          locked = Math.abs(dx) > Math.abs(dy) + 8;
        }
        if (locked) e.preventDefault();
      },
      { passive: false }
    );

    slider.addEventListener(
      "touchend",
      function () {
        if (!active) return;
        active = false;
        if (locked && Math.abs(dx) > 50) {
          go(dx < 0 ? idx + 1 : idx - 1);
        }
        startAuto();
      },
      { passive: true }
    );

    if (debug) {
      // Log image load/error to quickly diagnose missing/blocked assets
      slides.forEach(function (s) {
        var img = s.querySelector("img");
        if (!img) return;
        img.addEventListener("load", function () {
          console.log("[STR slider] image loaded:", img.getAttribute("src"), img.naturalWidth + "x" + img.naturalHeight);
        });
        img.addEventListener("error", function () {
          console.warn("[STR slider] image failed:", img.getAttribute("src"));
        });
      });
      console.log("[STR slider] debug=1 enabled");
    }

    startAuto();
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
