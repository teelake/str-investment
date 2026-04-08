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

  document.querySelectorAll("[data-faq-item]").forEach(function (item) {
    var btn = item.querySelector("[data-faq-q]");
    if (!btn) return;
    btn.addEventListener("click", function () {
      var isOpen = item.getAttribute("data-open") === "true";
      document.querySelectorAll("[data-faq-item]").forEach(function (other) {
        other.setAttribute("data-open", "false");
        var b = other.querySelector("[data-faq-q]");
        if (b) b.setAttribute("aria-expanded", "false");
      });
      item.setAttribute("data-open", isOpen ? "false" : "true");
      btn.setAttribute("aria-expanded", isOpen ? "false" : "true");
    });
  });

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
