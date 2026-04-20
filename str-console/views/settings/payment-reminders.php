<?php
declare(strict_types=1);
/** @var bool $enabled */
/** @var int $daysBefore */
/** @var bool $sendOnDue */
/** @var string $amountMode */
/** @var string $organizationName */
/** @var string $currencySymbol */
/** @var string $subjectAdvance */
/** @var string $subjectDue */
/** @var string $bodyAdvance */
/** @var string $bodyDue */
/** @var bool $mailOk */
/** @var mixed $flash */
/** @var mixed $error */

$basePath = Request::basePath();
$ok = is_string($flash) ? $flash : '';
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width:720px;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Payment reminders (borrowers)</h1>
  <p style="color: var(--muted); margin: 0 0 18px;">
    Send <strong>automatic emails</strong> to customers before each scheduled payment and on the due day. Text below is merged with live numbers from each loan — use the placeholders in <strong>curly braces</strong> exactly as shown. No technical knowledge is needed: edit the wording to match how you speak to clients.
  </p>

  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (!$mailOk): ?>
    <div style="background: rgba(180, 120, 20, .12); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 12px 14px; border-radius: 14px; margin-bottom: 18px; font-size: 14px;">
      <strong>No outgoing email is configured yet.</strong> Ask your technical contact to set the <code style="font-size:13px;">STR_CONSOLE_MAIL_FROM</code> environment variable (same as password-reset mail). Until then, reminders stay off even if you enable them below.
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/settings/payment-reminders', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 18px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>

      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="enabled" value="1" <?= $enabled ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Turn on automatic payment reminders</strong><br />
          <span style="color: var(--muted); font-size: 13px;">The server must run the daily reminder task (see below). Keep this off until you are ready.</span>
        </span>
      </label>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">First reminder — how many days before the due date?</span>
        <input type="number" name="days_before" min="0" max="60" value="<?= (int) $daysBefore ?>" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); max-width:120px;" />
        <span style="color: var(--muted); font-size: 12px;">Usually <strong>2</strong>. Use <strong>0</strong> if you only want an email <em>on</em> the due date (and turn on the next option).</span>
      </label>

      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="send_on_due" value="1" <?= $sendOnDue ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Also email on the due date itself</strong><br />
          <span style="color: var(--muted); font-size: 13px;">Sends a second, “due today” message (recommended).</span>
        </span>
      </label>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Amount to show for “this payment”</span>
        <select name="amount_mode" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); max-width:420px;">
          <option value="installment_when_set" <?= $amountMode === 'installment_when_set' ? 'selected' : '' ?>>
            Prefer the agreed installment when set on the loan (otherwise use the ledger amount)
          </option>
          <option value="ledger_only" <?= $amountMode === 'ledger_only' ? 'selected' : '' ?>>
            Always use the full ledger amount for the next step (ignore the optional installment on the loan)
          </option>
        </select>
        <span style="color: var(--muted); font-size: 12px;">
          Example: ₦40,000 still owing, ₦10,000 per due — enter <strong>10,000</strong> on the loan page; emails use the smaller of that and what the ledger expects for the date. Totals still show the real balance owing.
        </span>
      </label>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">How you sign the emails</span>
        <input type="text" name="organization_name" value="<?= htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8') ?>" maxlength="190" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" placeholder="Our team or your business name" />
      </label>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Currency symbol in the text</span>
        <input type="text" name="currency_symbol" value="<?= htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8') ?>" maxlength="8" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); max-width:120px;" />
      </label>

      <div style="padding:12px 14px; border-radius:12px; background:rgba(15,106,74,.06); border:1px solid rgba(15,106,74,.15); font-size:13px; line-height:1.45;">
        <strong style="font-size:13px;">Placeholders (copy exactly)</strong>
        <div style="margin-top:8px; font-family:ui-monospace,monospace; font-size:12px; word-break:break-all;">
          {customer_name} {loan_title} {loan_id} {due_date} {amount_due_this_period} {ledger_amount_due} {outstanding_balance} {currency_symbol} {organization_name} {reminder_note} {days_until_due}
        </div>
      </div>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Subject — first reminder (before due date)</span>
        <input type="text" name="subject_advance" value="<?= htmlspecialchars($subjectAdvance, ENT_QUOTES, 'UTF-8') ?>" maxlength="250" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
      </label>
      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Email body — first reminder</span>
        <textarea name="body_advance" rows="12" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); font-family:inherit; font-size:13px;"><?= htmlspecialchars($bodyAdvance, ENT_QUOTES, 'UTF-8') ?></textarea>
      </label>

      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Subject — due today</span>
        <input type="text" name="subject_due" value="<?= htmlspecialchars($subjectDue, ENT_QUOTES, 'UTF-8') ?>" maxlength="250" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
      </label>
      <label style="display:grid; gap:6px;">
        <span style="font-size:13px; font-weight:650; color:var(--muted);">Email body — due today</span>
        <textarea name="body_due" rows="10" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); font-family:inherit; font-size:13px;"><?= htmlspecialchars($bodyDue, ENT_QUOTES, 'UTF-8') ?></textarea>
      </label>

      <button type="submit" class="btn primary" style="justify-self: start;">Save payment reminders</button>
    </form>
  </div>

  <div style="margin-top:22px; padding:16px 18px; border-radius:14px; border:1px solid var(--line2); background:var(--card); font-size:13px; color:var(--muted2); line-height:1.45;">
    <strong style="color:var(--ink);">Automatic sending</strong><br />
    Your server should run once per day (same idea as interest accrual), for example:<br />
    <code style="display:block; margin-top:8px; font-size:12px; white-space:pre-wrap;">STR_CONSOLE_PAYMENT_REMINDER_CRON=1 php bin/send-payment-reminders.php</code>
    <span style="display:block; margin-top:8px;">Each customer must have an email on their profile. Loans use the next payment date from the ledger (30-day steps from disbursement) or the loan end date for the final balance.</span>
  </div>
</div>
