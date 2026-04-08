<?php $active = 'home'; ?>
<!doctype html>
<html lang="en-NG">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#0f6a4a" />
    <title>STR Investment Services Limited — Where trust meets opportunity</title>
    <meta
      name="description"
      content="STR Investment Services Limited offers accessible microcredit in Nigeria for individuals and SMEs — salary advance, personal loans, school support, and working capital."
    />
    <link rel="canonical" href="https://strinvestment.ng/" />
    <meta name="robots" content="index,follow,max-image-preview:large" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="STR Investment Services Limited" />
    <meta
      property="og:description"
      content="Microcredit for Nigeria — quick, responsible access to credit with clear requirements and structured repayments."
    />
    <meta property="og:url" content="https://strinvestment.ng/" />
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
        "@type": "FinancialService",
        "name": "STR Investment Services Limited",
        "slogan": "Where trust meets opportunity",
        "url": "https://strinvestment.ng/",
        "areaServed": "NG",
        "foundingDate": "2026-02",
        "email": "strinvestmentservicesltd@gmail.com"
      }
    </script>
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
          {
            "@type": "Question",
            "name": "Who can apply for a loan?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Salaried workers, parents/guardians, and small businesses can apply, subject to product requirements and assessment."
            }
          },
          {
            "@type": "Question",
            "name": "Do you require collateral?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Some products are unsecured while others may require a guarantor or collateral depending on assessment."
            }
          },
          {
            "@type": "Question",
            "name": "How is repayment structured?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Repayment is structured around salary dates or business cash-flow to support responsible borrowing."
            }
          }
        ]
      }
    </script>
  </head>
  <body>
    <a class="skip" href="#main">Skip to content</a>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main id="main">
      <section class="hero">
        <div class="container hero-grid">
          <div>
            <span class="pill"><span class="dot" aria-hidden="true"></span> Incorporated February 2026</span>
            <h1 class="h1">
              Bridging the <span class="accent">Financing Gap</span> for You
            </h1>
            <p class="lead">
              STR Investment Services Limited provides accessible and innovative microcredit solutions
              to individuals and small businesses — fast, flexible, and customer-focused.
            </p>
            <div class="hero-actions">
              <a class="btn primary" href="loans">Apply for a Loan</a>
              <a class="btn ghost" href="loans">Browse Products</a>
            </div>
          </div>

          <aside class="hero-side" aria-label="Loan highlights">
            <div class="frame" aria-hidden="true"></div>

            <div class="hero-slider" data-hero-slider data-index="0" aria-label="Loan highlights slider">
              <div class="hero-slides" data-hero-slides>
                <article class="hero-slide" data-hero-slide data-theme="personal" aria-label="Personal Loan slide">
                  <img
                    class="hero-slide-img"
                    src="assets/images/personal-loan.jpg"
                    alt=""
                    aria-hidden="true"
                    loading="lazy"
                  />
                  <div class="hero-slide-badge">
                    <i class="bx bx-id-card" aria-hidden="true"></i>
                    <span>Personal Loan</span>
                  </div>
                  <div class="hero-slide-title">Blue‑chip employee loan</div>
                  <div class="hero-slide-sub">Fast processing for verified staff with payroll‑friendly repayments.</div>
                </article>

                <article class="hero-slide" data-hero-slide data-theme="sme" aria-label="SME Term Loan slide">
                  <img
                    class="hero-slide-img"
                    src="assets/images/hero-placeholder.svg"
                    alt=""
                    aria-hidden="true"
                    loading="lazy"
                  />
                  <div class="hero-slide-badge">
                    <i class="bx bx-store-alt" aria-hidden="true"></i>
                    <span>SME Term Loan</span>
                  </div>
                  <div class="hero-slide-title">Working capital built for turnover</div>
                  <div class="hero-slide-sub">Short‑term support (3–6 months typical) with assessment-led structure.</div>
                </article>

                <article class="hero-slide" data-hero-slide data-theme="school" aria-label="Back to School slide">
                  <img
                    class="hero-slide-img"
                    src="assets/images/hero-placeholder.svg"
                    alt=""
                    aria-hidden="true"
                    loading="lazy"
                  />
                  <div class="hero-slide-badge">
                    <i class="bx bx-book-open" aria-hidden="true"></i>
                    <span>Back to School</span>
                  </div>
                  <div class="hero-slide-title">Education support for resumption</div>
                  <div class="hero-slide-sub">Covers fees and essentials with term‑aligned repayment options.</div>
                </article>
              </div>

              <div class="hero-dots" role="tablist" aria-label="Select slide">
                <button type="button" class="hero-dot" data-hero-dot aria-label="Show Personal Loan slide"></button>
                <button type="button" class="hero-dot" data-hero-dot aria-label="Show SME Term Loan slide"></button>
                <button type="button" class="hero-dot" data-hero-dot aria-label="Show Back to School slide"></button>
              </div>
            </div>

            <div class="mini">
              <span class="dot" aria-hidden="true"></span>
              <span><b>3</b> priority products</span>
            </div>
          </aside>
        </div>
      </section>

      <section class="section why">
        <div class="container">
          <div class="center">
            <h2 class="h2">Why Choose STR Investment?</h2>
            <p class="sub">
              We balance speed with responsible lending — protecting customers and sustaining long-term value.
            </p>
          </div>

          <div class="why-grid">
            <div class="why-item">
              <div class="ic"><i class="bx bx-timer" aria-hidden="true"></i></div>
              <strong>Quick Processing</strong>
              <div>Streamlined checks and clear requirements.</div>
            </div>
            <div class="why-item">
              <div class="ic"><i class="bx bx-calendar-check" aria-hidden="true"></i></div>
              <strong>Flexible Tenor</strong>
              <div>Repayments aligned to cash-flow and salary cycles.</div>
            </div>
            <div class="why-item">
              <div class="ic"><i class="bx bx-receipt" aria-hidden="true"></i></div>
              <strong>Transparent Fees</strong>
              <div>Clear pricing structure — no surprises.</div>
            </div>
            <div class="why-item">
              <div class="ic"><i class="bx bx-briefcase-alt-2" aria-hidden="true"></i></div>
              <strong>SME Support</strong>
              <div>Working capital structured around turnover reality.</div>
            </div>
          </div>

          <div class="metrics" aria-label="Metrics">
            <div class="metric"><b>₦450M</b><span>Capacity</span></div>
            <div class="metric"><b>120k+</b><span>Applicants</span></div>
            <div class="metric"><b>98%</b><span>Satisfaction</span></div>
            <div class="metric"><b>Instant</b><span>Support</span></div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="loans-head">
            <div>
              <h2 class="h2">Quick, Reliable, and Affordable Access to Credit</h2>
              <p class="sub">Explore products designed for salaries, school, and business growth.</p>
            </div>
            <a class="btn ghost" href="loans">See all →</a>
          </div>

          <div class="loans featured">
            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-id-card"></i></span>
                  <div class="title">Personal Loan</div>
                </div>
                <p>Blue‑chip employee loan for verified staff with payroll-friendly repayment.</p>
              </div>
              <div style="margin-top: 16px"><a class="btn ghost" href="loans#personal">Learn More</a></div>
            </article>

            <article class="loan-side">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-store-alt"></i></span>
                  <div class="title">Term Loan</div>
                </div>
                <p>Working capital for SMEs — assessed on turnover and cash-flow.</p>
              </div>
              <div style="margin-top: 16px"><a class="btn" href="loans#sme" style="background:#fff;color:var(--green);">Learn More</a></div>
            </article>
          </div>

          <div class="loan-row">
            <article class="loan-tile">
              <strong>Salary Advance Loan</strong>
              <span>Short-term support before payday.</span>
            </article>
            <article class="loan-tile">
              <strong>Back to School Loan</strong>
              <span>Term-aligned support for school expenses.</span>
            </article>
            <article class="loan-tile">
              <strong>More products</strong>
              <span>Talk to us for the best fit.</span>
            </article>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="center">
            <h2 class="h2">Frequently Asked Questions</h2>
          </div>

          <div class="faq" aria-label="FAQs">
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                Who can apply for a loan?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Salaried workers, parents/guardians, and SMEs can apply, subject to requirements and assessment.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                What is the eligibility process?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                We verify identity, income/trading activity, and affordability to structure responsible repayment.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                How are repayments scheduled?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Repayments are aligned to salary dates or business cash-flow where applicable.
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="cta-panel">
            <div class="center" style="margin-bottom: 0; max-width: 760px">
              <h2 class="h2" style="color: white; margin-bottom: 10px">
                Ready to empower your financial future?
              </h2>
              <p>Tell us what you need and we’ll guide you to the right product and requirements.</p>
              <div class="actions">
                <a class="btn" href="loans" style="background:#fff;color:var(--green);">Back to Loans</a>
                <a class="btn primary" href="contact">Talk to a Consultant</a>
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

