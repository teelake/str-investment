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
                  <input id="phone" name="phone" placeholder="09054984777" required />
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
                  6, 2nd Avenue, Olorunkemi Estate, Elebu, Oluyole Extension, Ibadan
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
                  <a href="tel:+2349054984777">09054984777</a>
                </div>
              </div>
              <div class="office-extra" aria-label="Office details">
                <div class="row">
                  <span aria-hidden="true"><i class="bx bx-time-five"></i></span>
                  <div>
                    <b>Office hours</b><br />
                    Mon–Fri, 9:00am–5:00pm
                  </div>
                </div>
                <div class="row">
                  <span aria-hidden="true"><i class="bx bxl-whatsapp"></i></span>
                  <div>
                    <b>WhatsApp</b><br />
                    <a href="https://wa.me/2349054984777" target="_blank" rel="noopener">Chat with us</a>
                  </div>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <section class="lower map-band" aria-label="Map">
        <div class="map-embed-full" aria-label="Office location map">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d494.6233020332049!2d3.831665299454212!3d7.355418203058441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10398d000b02ddc9%3A0x1e6c5e85ece95e70!2sElebu%2C%20Ibadan!5e0!3m2!1sen!2sng!4v1775720775449!5m2!1sen!2sng"
            width="600"
            height="450"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="STR Investment Services Limited — Office location"
          ></iframe>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="assets/app.js" defer></script>
  </body>
</html>

