<?php $active = 'eligibility'; ?>
<!doctype html>
<html lang="en-NG">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#0f6a4a" />
    <title>Eligibility — STR Investment Services Limited</title>
    <meta
      name="description"
      content="Learn how STR Investment assesses eligibility for microcredit in Nigeria — clear requirements, transparent pricing, and structured repayments."
    />
    <link rel="canonical" href="https://strinvestment.ng/eligibility" />
    <meta property="og:title" content="Eligibility — STR Investment" />
    <meta property="og:url" content="https://strinvestment.ng/eligibility" />
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
  </head>
  <body>
    <a class="skip" href="#main">Skip to content</a>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main id="main">
      <section class="hero">
        <div class="container hero-grid">
          <div>
            <span class="pill"><span class="dot" aria-hidden="true"></span> Eligibility</span>
            <h1 class="h1">Clarity first. <span class="accent">Assessment</span> next.</h1>
            <p class="lead">
              Our goal is quick, reliable access to credit — with responsible checks and documentation that fits the product.
            </p>
            <div class="hero-actions">
              <a class="btn primary" href="contact?product=general">Start an enquiry</a>
              <a class="btn ghost" href="loans">View loan products</a>
            </div>
          </div>

          <aside class="hero-side" aria-label="Process preview">
            <div class="frame" aria-hidden="true"></div>
            <div class="mini">
              <span class="dot" aria-hidden="true"></span>
              <span><b>Step-by-step</b> with clear requirements</span>
            </div>
          </aside>
        </div>
      </section>

      <section class="section why">
        <div class="container">
          <div class="center">
            <h2 class="h2">How eligibility works</h2>
            <p class="sub">A simple flow built to be fast — without cutting corners.</p>
          </div>

          <div class="loan-row">
            <article class="loan-tile">
              <strong>1) Choose a product</strong>
              <span>Tell us the amount range, tenure, and purpose.</span>
            </article>
            <article class="loan-tile">
              <strong>2) Provide documents</strong>
              <span>ID, bank statements, income/employment or trade evidence.</span>
            </article>
            <article class="loan-tile">
              <strong>3) Verification & decision</strong>
              <span>Affordability checks and clear next steps.</span>
            </article>
          </div>

          <div class="metrics" style="margin-top: 22px">
            <div class="metric"><b>Identity</b><span>KYC & verification</span></div>
            <div class="metric"><b>Income</b><span>Salary or turnover evidence</span></div>
            <div class="metric"><b>Affordability</b><span>Repayment fit</span></div>
            <div class="metric"><b>Support</b><span>Guidance throughout</span></div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="loans-head">
            <div>
              <h2 class="h2">Typical documents (by product)</h2>
              <p class="sub">Final requirements may vary based on assessment.</p>
            </div>
            <a class="btn ghost" href="contact?product=general">Ask us →</a>
          </div>

          <div class="loans equal">
            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-id-card"></i></span>
                  <div class="title">Salaried / personal</div>
                </div>
                <p>Employment ID, payslips, bank statement, ID, proof of residence, guarantor (where required).</p>
              </div>
            </article>
            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-store-alt"></i></span>
                  <div class="title">SME / business</div>
                </div>
                <p>Business registration or evidence of trade, sales records/bank statement, business location proof, ID, guarantor/collateral where applicable.</p>
              </div>
            </article>
          </div>

          <div class="loan-row">
            <article class="loan-tile">
              <strong>Education support</strong>
              <span>Child enrollment/fee invoice + parent/guardian income evidence.</span>
            </article>
            <article class="loan-tile">
              <strong>Salary advance</strong>
              <span>Short-tenor: employment proof + salary inflow evidence.</span>
            </article>
            <article class="loan-tile">
              <strong>Transparent pricing</strong>
              <span>Fees and interest are explained before acceptance.</span>
            </article>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="cta-panel">
            <div class="center" style="margin-bottom: 0; max-width: 760px">
              <h2 class="h2" style="color: white; margin-bottom: 10px">Ready to apply?</h2>
              <p>We’ll recommend the best product fit and the exact documents needed for your case.</p>
              <div class="actions">
                <a class="btn" href="loans" style="background:#fff;color:var(--green);">Browse Loans</a>
                <a class="btn primary" href="contact?product=general">Apply Now</a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="assets/app.js" defer></script>
  </body>
</html>

