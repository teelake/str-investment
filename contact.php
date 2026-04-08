<?php $active = 'contact'; ?>
<!doctype html>
<html lang="en-NG">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#0f6a4a" />
    <title>Contact — STR Investment Services Limited</title>
    <meta
      name="description"
      content="Contact STR Investment Services Limited. Send an enquiry about personal loans, salary advances, school loans, or SME working capital."
    />
    <link rel="canonical" href="https://strinvestment.ng/contact" />
    <meta property="og:title" content="Contact — STR Investment" />
    <meta property="og:url" content="https://strinvestment.ng/contact" />
    <meta property="og:image" content="https://strinvestment.ng/og.svg" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="icon" href="favicon.svg" type="image/svg+xml" />
    <link rel="stylesheet" href="assets/styles.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    />

    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "ContactPage",
        "name": "Contact STR Investment Services Limited",
        "url": "https://strinvestment.ng/contact"
      }
    </script>
  </head>
  <body>
    <a class="skip" href="#main">Skip to content</a>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main id="main">
      <section class="contact-hero">
        <div class="container">
          <h1 class="contact-title">
            Connect With <span class="accent">Sovereign Finance</span>
          </h1>
          <p class="lead" style="max-width: 64ch">
            Whether you’re exploring microcredit options or need guidance on eligibility, our team is ready to
            provide the precision you require.
          </p>

          <div class="contact-grid">
            <section class="panel" aria-label="Contact form">
              <form class="formgrid" data-contact-form data-mailto="strinvestmentservicesltd@gmail.com">
                <div class="field">
                  <label for="name">Name</label>
                  <input id="name" name="name" placeholder="Full name" required />
                </div>
                <div class="field">
                  <label for="email">Email</label>
                  <input id="email" name="email" placeholder="professional@email.com" required />
                </div>
                <div class="field">
                  <label for="phone">Phone</label>
                  <input id="phone" name="phone" placeholder="+234 000 000 0000" required />
                </div>
                <div class="field">
                  <label for="subject">Subject</label>
                  <select id="subject" name="product">
                    <option>Loan enquiry</option>
                    <option>Personal loan</option>
                    <option>Salary advance</option>
                    <option>Back to school</option>
                    <option>SME term loan</option>
                  </select>
                </div>
                <div class="field" style="grid-column: 1 / -1">
                  <label for="message">Message</label>
                  <textarea
                    id="message"
                    name="message"
                    placeholder="How can our consultants assist you today?"
                    required
                  ></textarea>
                </div>
                <div class="form-actions">
                  <button class="btn primary" type="submit" style="width: 100%">Dispatch Inquiry</button>
                </div>
              </form>
            </section>

            <aside class="panel office" aria-label="Principal office">
              <h3>Principal Office</h3>
              <div class="row">
                <span aria-hidden="true"><i class="bx bx-map"></i></span>
                <div>
                  <b>Headquarters</b><br />
                  Nigeria (update address later)
                </div>
              </div>
              <div class="row">
                <span aria-hidden="true"><i class="bx bx-envelope"></i></span>
                <div>
                  <b>Email Us</b><br />
                  <a href="mailto:strinvestmentservicesltd@gmail.com">strinvestmentservicesltd@gmail.com</a>
                </div>
              </div>
              <div class="row">
                <span aria-hidden="true"><i class="bx bx-phone-call"></i></span>
                <div>
                  <b>Direct Line</b><br />
                  <a href="tel:+2340000000000">+234 000 000 0000</a>
                </div>
              </div>
              <div class="map" aria-label="Map placeholder"></div>
            </aside>
          </div>
        </div>
      </section>

      <section class="section lower">
        <div class="container lower-grid">
          <div class="lower-card">
            <span class="badge">PRIORITY ACCESS</span>
            <h2 class="h2" style="margin-top: 14px">Direct Guidance for Your Next Move.</h2>
            <p class="sub">
              Skip the queue. Connect directly with a Senior Loan Consultant to discuss custom financing structures
              and exclusive rates.
            </p>
            <div class="hero-actions" style="margin-top: 18px">
              <a class="btn primary" href="contact">Schedule a Call</a>
              <a class="btn ghost" href="loans">View Products</a>
            </div>
          </div>
          <div class="portrait" aria-label="Portrait placeholder">
            <img
              src="https://images.unsplash.com/photo-1556157382-97eda2d62296?w=1200&q=80&auto=format&fit=crop"
              alt="Professional consultant portrait (placeholder) — replace with your team photo."
              loading="lazy"
            />
          </div>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="assets/app.js" defer></script>
  </body>
</html>

