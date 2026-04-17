<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoanLedgerMoneyTest extends TestCase
{
    public function testMoneyRoundsToTwoDecimals(): void
    {
        $this->assertSame(1.23, LoanLedgerService::money(1.234));
        $this->assertSame(1.24, LoanLedgerService::money(1.235));
        $this->assertSame(0.0, LoanLedgerService::money(0.004));
    }
}
