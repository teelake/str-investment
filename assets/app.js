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

    var slidesEl = slider.querySelector("[data-hero-slides]");
    var slides = Array.prototype.slice.call(slider.querySelectorAll("[data-hero-slide]"));
    var dots = Array.prototype.slice.call(slider.querySelectorAll("[data-hero-dot]"));
    if (!slidesEl || slides.length === 0 || dots.length !== slides.length) return;

    var prefersReduced =
      typeof window !== "undefined" &&
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var idx = 0;
    var intervalMs = 7000;
    var timer = null;

    function render() {
      slidesEl.style.transform = "translateX(" + idx * -100 + "%)";
      dots.forEach(function (d, i) {
        d.setAttribute("aria-current", i === idx ? "true" : "false");
      });
      slider.setAttribute("data-index", String(idx));
    }

    function go(next) {
      idx = (next + slides.length) % slides.length;
      render();
    }

    dots.forEach(function (d, i) {
      d.addEventListener("click", function () {
        go(i);
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      });
    });

    render();
    if (!prefersReduced) {
      timer = setInterval(function () {
        go(idx + 1);
      }, intervalMs);
    }
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
    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var to = contactForm.getAttribute("data-mailto") || "strinvestmentservicesltd@gmail.com";
      var name = (contactForm.querySelector('[name="name"]') || {}).value || "";
      var phone = (contactForm.querySelector('[name="phone"]') || {}).value || "";
      var email = (contactForm.querySelector('[name="email"]') || {}).value || "";
      var product = (contactForm.querySelector('[name="product"]') || {}).value || "";
      var msg = (contactForm.querySelector('[name="message"]') || {}).value || "";
      var sub = encodeURIComponent("Enquiry — STR Investment" + (product ? " — " + product : ""));
      var body = encodeURIComponent(
        ["Name: " + name, "Phone: " + phone, "Email: " + email, "Product: " + product, "", msg].join("\n")
      );
      window.location.href = "mailto:" + to + "?subject=" + sub + "&body=" + body;
    });
  }
})();
