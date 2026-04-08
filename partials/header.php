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
      <a class="brand" href="./">STR Investment</a>

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
          data-mobile-toggle
        >
          Menu
        </button>
      </div>
    </div>

    <div class="drawer" data-mobile-drawer data-open="false">
      <a href="./" <?= $active === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
      <a href="loans" <?= $active === 'loans' ? 'aria-current="page"' : '' ?>>Loans</a>
      <a href="eligibility" <?= $active === 'eligibility' ? 'aria-current="page"' : '' ?>>Eligibility</a>
      <a href="about" <?= $active === 'about' ? 'aria-current="page"' : '' ?>>About Us</a>
      <a href="contact" <?= $active === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    </div>
  </div>
</header>

