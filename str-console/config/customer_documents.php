<?php

declare(strict_types=1);

/**
 * KYC document categories for customer uploads. Keys are stored in customer_documents.document_type.
 * Adjust labels or add keys here as your policy evolves.
 *
 * @return array<string, string>
 */
function str_console_customer_document_types(): array
{
    return [
        'national_id' => 'National ID / NIN slip',
        'passport' => 'International passport',
        'drivers_license' => 'Driver\'s license',
        'voters_card' => 'Voter\'s card',
        'utility_bill' => 'Utility bill (proof of address)',
        'bank_statement' => 'Bank statement',
        'employment_letter' => 'Employment letter',
        'cac_document' => 'CAC / business registration',
        'other' => 'Other',
    ];
}

function str_console_customer_document_type_label(?string $key): string
{
    if ($key === null || $key === '') {
        return '—';
    }
    $types = str_console_customer_document_types();
    return $types[$key] ?? $key;
}
