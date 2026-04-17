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

    public function testInterestPeriodIndex(): void
    {
        $this->assertSame(0, LoanLedgerService::interestPeriodIndex('2026-04-01', '2026-04-01'));
        $this->assertSame(0, LoanLedgerService::interestPeriodIndex('2026-04-01', '2026-04-30'));
        $this->assertSame(1, LoanLedgerService::interestPeriodIndex('2026-04-01', '2026-05-01'));
        $this->assertSame(-1, LoanLedgerService::interestPeriodIndex('2026-04-01', '2026-03-15'));
    }

    public function testMaxPaymentSame30DayPeriodIsBalanceOnly(): void
    {
        $this->assertSame(
            1000.0,
            LoanLedgerService::maxPaymentForNextLine(
                1000.0,
                12.0,
                '2026-04-20',
                '2026-04-17',
                '2026-04-01',
                LoanInterestBasis::REDUCING_BALANCE,
                5000.0
            )
        );
    }

    public function testMaxPaymentNext30DayPeriodAddsReducingInterest(): void
    {
        $this->assertSame(
            1120.0,
            LoanLedgerService::maxPaymentForNextLine(
                1000.0,
                12.0,
                '2026-05-01',
                '2026-04-17',
                '2026-04-01',
                LoanInterestBasis::REDUCING_BALANCE,
                5000.0
            )
        );
    }

    public function testMaxPaymentFlatMonthlyUsesOriginalPrincipalForInterest(): void
    {
        $this->assertSame(
            1600.0,
            LoanLedgerService::maxPaymentForNextLine(
                1000.0,
                12.0,
                '2026-05-01',
                '2026-04-17',
                '2026-04-01',
                LoanInterestBasis::FLAT_MONTHLY,
                5000.0
            )
        );
    }
}
