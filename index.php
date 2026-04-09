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
    <link rel="stylesheet" href="assets/styles.css?v=20260409" />
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
            "name": "Who can apply for STR Investment loans?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Salaried workers, parents/guardians, and small and medium business owners can apply, depending on the product requirements and assessment."
            }
          },
          {
            "@type": "Question",
            "name": "Do you require collateral or a guarantor?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Some products are unsecured, while others may require a guarantor or collateral depending on the product type and assessment."
            }
          },
          {
            "@type": "Question",
            "name": "What documents do I need to apply?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Typical documents include valid ID, bank statement (3–6 months), proof of income or business activity, and product-specific requirements such as payslips, employer verification, school fee invoice, or business registration."
            }
          },
          {
            "@type": "Question",
            "name": "How long does approval and disbursement take?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Timelines depend on verification and completeness of documents. Salary advance loans are typically processed quickly after approval (often within 24–48 hours), while other products may take longer based on assessment."
            }
          },
          {
            "@type": "Question",
            "name": "How are repayments scheduled and what are the typical tenors?",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "Repayments are structured around salary dates or business cash-flow. Typical tenors include salary advance (15–30 days), back to school (1–4 months), term loan for SMEs (3–6 months), and personal loans (3–12 months)."
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
      <section class="hero hero--slider" aria-label="Hero">
        <div class="hero-slider" data-hero-slider data-index="0" aria-label="Loan highlights slider">
          <div class="hero-slides" data-hero-slides>
            <article class="hero-slide" data-hero-slide data-theme="personal" aria-label="Personal Loan slide">
              <img
                class="hero-slide-img"
                src="assets/images/personal-loan.jpg"
                alt=""
                aria-hidden="true"
                loading="lazy"
                onerror="this.onerror=null;this.src='assets/images/hero-placeholder.svg';"
              />
            </article>
            <article class="hero-slide" data-hero-slide data-theme="sme" aria-label="SME Term Loan slide">
              <img
                class="hero-slide-img"
                src="assets/images/loan-mama.jpg"
                alt=""
                aria-hidden="true"
                loading="lazy"
                onerror="this.onerror=null;this.src='assets/images/hero-placeholder.svg';"
              />
            </article>
            <article class="hero-slide" data-hero-slide data-theme="school" aria-label="Back to School slide">
              <img
                class="hero-slide-img"
                src="assets/images/back-to-school.avif"
                alt=""
                aria-hidden="true"
                loading="lazy"
                onerror="this.onerror=null;this.src='assets/images/hero-placeholder.svg';"
              />
            </article>
          </div>

          <div class="hero-dots" role="tablist" aria-label="Select slide">
            <button type="button" class="hero-dot" data-hero-dot aria-label="Show Personal Loan slide"></button>
            <button type="button" class="hero-dot" data-hero-dot aria-label="Show SME Term Loan slide"></button>
            <button type="button" class="hero-dot" data-hero-dot aria-label="Show Back to School slide"></button>
          </div>
        </div>

        <div class="container hero-content">
          <span class="pill" data-hero-pill><span class="dot" aria-hidden="true"></span> Incorporated February 2026</span>
          <h1 class="h1">
            <span data-hero-h1-pre>Bridging the </span><span class="accent" data-hero-h1-accent>Financing Gap</span
            ><span data-hero-h1-post> for You</span>
          </h1>
          <p class="lead" data-hero-lead>
            STR Investment Services Limited provides accessible and innovative microcredit solutions to individuals and small
            businesses — fast, flexible, and customer-focused.
          </p>
          <div class="hero-actions">
            <a class="btn primary" data-hero-primary href="contact?product=personal">Apply for a Loan</a>
            <a class="btn ghost" data-hero-secondary href="loans#personal">Browse Products</a>
          </div>
        </div>
      </section>

      <section class="section why">
        <div class="container">
          <div class="center">
            <h2 class="h2">Why Choose STR Investment?</h2>
            <p class="sub">
              Fast decisions. Responsible lending.
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
        </div>
      </section>

      <section class="section testimonials-band" aria-label="Testimonials">
        <div class="container">
          <div class="center">
            <h2 class="h2">Testimonials</h2>
            <p class="sub">What clients say about clarity, speed, and support.</p>
          </div>

          <div class="testimonials">
            <article class="t-card">
              <div class="t-quote">“Fast, clear process. I understood the requirements and repayment from day one.”</div>
              <div class="t-meta">
                <div class="t-avatar" aria-hidden="true">A</div>
                <div>
                  <b>Applicant</b>
                  <div class="t-sub">Personal loan</div>
                </div>
              </div>
            </article>
            <article class="t-card">
              <div class="t-quote">“The team guided me through the documents and disbursement was timely.”</div>
              <div class="t-meta">
                <div class="t-avatar" aria-hidden="true">K</div>
                <div>
                  <b>Business owner</b>
                  <div class="t-sub">SME term loan</div>
                </div>
              </div>
            </article>
            <article class="t-card">
              <div class="t-quote">“Back‑to‑school support helped us settle fees without stress. Smooth repayment plan.”</div>
              <div class="t-meta">
                <div class="t-avatar" aria-hidden="true">M</div>
                <div>
                  <b>Parent</b>
                  <div class="t-sub">School support</div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="section loans-band">
        <div class="container">
          <div class="loans-head">
            <div>
              <h2 class="h2">Quick, Reliable, and Affordable Access to Credit</h2>
              <p class="sub">Explore products designed for salaries, school, and business growth.</p>
            </div>
            <a class="btn ghost" href="loans">See all →</a>
          </div>

          <div class="loans equal">
            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-id-card"></i></span>
                  <div class="title">Personal Loan (Blue‑Chip Employee Loan)</div>
                </div>
                <p>
                  Unsecured short‑to‑medium term loan for verified staff of reputable employers, with a fixed monthly repayment
                  schedule and flexible tenor up to 12 months.
                </p>
              </div>
              <div style="margin-top: 16px"><a class="btn ghost" href="loans#personal">Learn More</a></div>
            </article>

            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-store-alt"></i></span>
                  <div class="title">Term Loan (Business Loan)</div>
                </div>
                <p>
                  Short‑term working capital for SMEs (3–6 months) with flexible collateral options (movable or
                  guarantor‑backed), assessed on turnover and cash‑flow.
                </p>
              </div>
              <div style="margin-top: 16px"><a class="btn ghost" href="loans#sme">Learn More</a></div>
            </article>

            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-book-open"></i></span>
                  <div class="title">Welcome Back to School Loan</div>
                </div>
                <p>
                  Short‑term education support for parents/guardians covering fees and essentials, with repayment structured
                  around school terms (1–4 months).
                </p>
              </div>
              <div style="margin-top: 16px"><a class="btn ghost" href="loans#school">Learn More</a></div>
            </article>

            <article class="loan-big">
              <div>
                <div class="title-row">
                  <span class="title-ic" aria-hidden="true"><i class="bx bx-time-five"></i></span>
                  <div class="title">Salary Advance Loan</div>
                </div>
                <p>
                  Short‑tenor support for salaried workers before payday (15–30 days), typically disbursed within 24–48 hours
                  after approval with automatic repayment from salary.
                </p>
              </div>
              <div style="margin-top: 16px"><a class="btn ghost" href="loans#advance">Learn More</a></div>
            </article>
          </div>
        </div>
      </section>

      <section class="section editorial-band" aria-label="How we support you">
        <div class="container lower-grid">
          <div class="lower-card">
            <span class="badge">OUR APPROACH</span>
            <h2 class="h2" style="margin-top: 14px">Bridging the financing gap with accessible microcredit.</h2>
            <p class="sub">
              We’re built to bridge the financing gap with timely, flexible microcredit—guided by risk-aware assessment and
              customer-first support.
            </p>
            <div class="editorial-points" aria-label="Highlights">
              <div class="pt">
                <i class="bx bx-timer" aria-hidden="true"></i>
                <div>
                  <b>Speed</b>
                  <div class="sub">Streamlined verification and quick turnarounds.</div>
                </div>
              </div>
              <div class="pt">
                <i class="bx bx-receipt" aria-hidden="true"></i>
                <div>
                  <b>Transparency</b>
                  <div class="sub">Clear requirements, fees, and repayment expectations.</div>
                </div>
              </div>
              <div class="pt">
                <i class="bx bx-user-check" aria-hidden="true"></i>
                <div>
                  <b>Customer‑first</b>
                  <div class="sub">Guidance from application to repayment.</div>
                </div>
              </div>
            </div>
            <div class="hero-actions" style="margin-top: 18px">
              <a class="btn primary" href="contact?product=general">Talk to a Consultant</a>
              <a class="btn ghost" href="eligibility">See Eligibility</a>
            </div>
          </div>

          <figure class="portrait" aria-label="Team photo">
            <img
              src="assets/images/Bridging-Credit-Gap-Rethinking-SME-Financing.png"
              loading="lazy"
              alt="Bridging the Credit Gap - Rethinking SME Financing"
            />
            <figcaption class="portrait-cap">Guided by experienced bankers and financial professionals.</figcaption>
          </figure>
        </div>
      </section>

      <section class="section faq-band">
        <div class="container">
          <div class="center">
            <h2 class="h2">Frequently Asked Questions</h2>
          </div>

          <div class="faq" aria-label="FAQs">
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                Who can apply for STR Investment loans?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Salaried workers, parents/guardians, and small and medium business owners can apply, depending on the
                product requirements and assessment.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                Do you require collateral or a guarantor?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Some products are unsecured, while others may require a guarantor or collateral depending on the
                product type and assessment.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                What documents do I need to apply?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Typical documents include valid ID, bank statement (3–6 months), proof of income or business activity,
                and product-specific requirements such as payslips, employer verification, school fee invoice, or
                business registration.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                How long does approval and disbursement take?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Timelines depend on verification and completeness of documents. Salary advance loans are typically
                processed quickly after approval (often within 24–48 hours), while other products may take longer based
                on assessment.
              </div>
            </div>
            <div class="qa" data-qa data-open="false">
              <button class="q" type="button" data-q aria-expanded="false">
                How are repayments scheduled and what are the typical tenors?
                <span aria-hidden="true">+</span>
              </button>
              <div class="a" data-a>
                Repayments are structured around salary dates or business cash-flow. Typical tenors include salary
                advance (15–30 days), back to school (1–4 months), term loan for SMEs (3–6 months), and personal loans
                (3–12 months).
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
                <a class="btn primary" href="contact?product=general">Talk to a Consultant</a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script src="assets/app.js?v=20260409" defer></script>
  </body>
</html>

