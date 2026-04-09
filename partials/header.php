<?php
/**
 * Shared site header / nav.
 *
 * Variables expected:
 * - $active (string): home|about|loans|eligibility|contact|privacy
 */
$active = $active ?? '';
?>
<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand brand--img" href="./" aria-label="STR Investment home">
        <img src="assets/images/logo-transparent.png" alt="STR Investment Services Limited" />
      </a>

      <nav class="navlinks" aria-label="Primary">
        <a href="./" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
        <a href="loans" <?= $active === 'loans' ? 'aria-current="page"' : '' ?>>Loans</a>
        <a href="eligibility" <?= $active === 'eligibility' ? 'aria-current="page"' : '' ?>>Eligibility</a>
        <a href="about" <?= $active === 'about' ? 'aria-current="page"' : '' ?>>About Us</a>
        <a href="contact" <?= $active === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
      </nav>

      <div class="cta">
        <a class="btn primary" href="contact">Apply Now</a>
        <button
          class="btn ghost mobile-toggle"
          type="button"
          aria-label="Open menu"
          aria-expanded="false"
          data-nav-toggle
        >
          <i class="bx bx-menu" aria-hidden="true"></i>
          <span class="sr-only">Menu</span>
        </button>
      </div>
    </div>

    <div class="drawer" data-nav-drawer data-open="false">
      <a href="./" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
      <a href="loans" <?= $active === 'loans' ? 'aria-current="page"' : '' ?>>Loans</a>
      <a href="eligibility" <?= $active === 'eligibility' ? 'aria-current="page"' : '' ?>>Eligibility</a>
      <a href="about" <?= $active === 'about' ? 'aria-current="page"' : '' ?>>About Us</a>
      <a href="contact" <?= $active === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    </div>
  </div>
</header>

