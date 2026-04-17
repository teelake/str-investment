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
        'bill_of_sale' => 'Bill of sale',
        'id_card' => 'ID card',
        'cheque' => 'Cheque',
        'guarantor_form' => 'Guarantor form',
        'application_form' => 'Application form',
        'kyc' => 'KYC',
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
