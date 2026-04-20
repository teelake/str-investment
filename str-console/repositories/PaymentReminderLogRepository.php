<?php

declare(strict_types=1);

/**
 * Dedupe log: one row per (loan, kind, anchor payment date).
 */
final class PaymentReminderLogRepository
{
    /**
     * @return bool True if a new row was inserted (caller may send email)
     */
    public function tryInsert(int $loanId, string $kind, string $anchorYmd, string $recipientEmail): bool
    {
        $kind = trim($kind);
        if ($kind !== 'advance' && $kind !== 'due') {
            throw new InvalidArgumentException('Invalid reminder kind.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchorYmd)) {
            throw new InvalidArgumentException('Invalid anchor date.');
        }
        $email = trim($recipientEmail);
        if ($email === '' || mb_strlen($email) > 255) {
            throw new InvalidArgumentException('Invalid recipient email.');
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO loan_payment_reminder_log (loan_id, reminder_kind, anchor_date, recipient_email, sent_at)
             VALUES (:lid, :k, :ad, :em, NOW())'
        );
        $stmt->execute([
            ':lid' => $loanId,
            ':k' => $kind,
            ':ad' => $anchorYmd,
            ':em' => $email,
        ]);
        return $stmt->rowCount() > 0;
    }
}
