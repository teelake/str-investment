<?php $active = 'privacy'; ?>
<!doctype html>
<html lang="en-NG">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#0f6a4a" />
    <title>Privacy — STR Investment Services Limited</title>
    <meta name="description" content="Privacy notice for STR Investment Services Limited marketing website." />
    <link rel="canonical" href="https://strinvestment.ng/privacy" />
    <meta property="og:title" content="Privacy — STR Investment" />
    <meta property="og:url" content="https://strinvestment.ng/privacy" />
    <meta property="og:image" content="https://strinvestment.ng/og.svg" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="icon" href="favicon.svg" type="image/svg+xml" />
    <link rel="stylesheet" href="assets/styles.css?v=20260423" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    />
  </head>
  <body>
    <a class="skip" href="#main">Skip to content</a>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main id="main">
      <section class="contact-hero">
        <div class="container">
          <h1 class="contact-title">Privacy <span class="accent">Notice</span></h1>
          <p class="lead" style="max-width: 70ch">
            This is a marketing website (no backend). Please have counsel review and tailor for production.
          </p>

          <div class="contact-grid">
            <section class="panel">
              <h2 class="h2" style="margin-top: 0">What we collect</h2>
              <p class="sub">
                When you submit the contact form or newsletter field, your device opens your email client and sends the
                message to <a href="mailto:strinvestmentservicesltd@gmail.com">strinvestmentservicesltd@gmail.com</a>.
                This site does not store form submissions in a database.
              </p>
              <div style="height: 12px"></div>
              <h2 class="h2" style="margin-top: 0">Third parties</h2>
              <p class="sub">
                Fonts may be loaded from Google Fonts. If images are embedded from external sources, those providers may
                process requests. Your hosting provider may log standard technical information.
              </p>
            </section>

            <aside class="panel">
              <h2 class="h2" style="margin-top: 0">Contact</h2>
              <p class="sub">
                Questions about privacy? Email:
                <a href="mailto:strinvestmentservicesltd@gmail.com">strinvestmentservicesltd@gmail.com</a>
              </p>
              <div class="placeholder-box" aria-label="Note">
                <b>Note</b><br />
                For urgent support, call <a href="tel:+2349054984777">09054984777</a>.
              </div>
            </aside>
          </div>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="assets/app.js?v=20260422" defer></script>
  </body>
</html>

