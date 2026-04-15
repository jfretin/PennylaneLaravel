<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'endpoint' => env('PENNYLANE_API_ENDPOINT', 'https://app.pennylane.tech/api/external/'),
    'v1_key' => env('PENNYLANE_API_KEY'),
    'v2_key' => env('PENNYLANE_API_V2_KEY'),
    'v2_use_oauth_token' => env('PENNYLANE_API_V2_USE_OAUTH_TOKEN', false),
    'use_2026_api_changes' => env('PENNYLANE_USE_2026_API_CHANGES'),
    'use_2026_api_changes_overrides' => [
        'attachments' => env('PENNYLANE_USE_2026_API_CHANGES_ATTACHMENTS'),
        'bank_accounts' => env('PENNYLANE_USE_2026_API_CHANGES_BANK_ACCOUNTS'),
        'categories' => env('PENNYLANE_USE_2026_API_CHANGES_CATEGORIES'),
        'changelogs' => env('PENNYLANE_USE_2026_API_CHANGES_CHANGELOGS'),
        'customer_invoice_templates' => env('PENNYLANE_USE_2026_API_CHANGES_CUSTOMER_INVOICE_TEMPLATES'),
        'customer_invoices' => env('PENNYLANE_USE_2026_API_CHANGES_CUSTOMER_INVOICES'),
        'customers' => env('PENNYLANE_USE_2026_API_CHANGES_CUSTOMERS'),
        'enums' => env('PENNYLANE_USE_2026_API_CHANGES_ENUMS'),
        'estimates' => env('PENNYLANE_USE_2026_API_CHANGES_ESTIMATES'),
        'journals' => env('PENNYLANE_USE_2026_API_CHANGES_JOURNALS'),
        'ledger_accounts' => env('PENNYLANE_USE_2026_API_CHANGES_LEDGER_ACCOUNTS'),
        'ledger_entries' => env('PENNYLANE_USE_2026_API_CHANGES_LEDGER_ENTRIES'),
        'ledger_entry_lines' => env('PENNYLANE_USE_2026_API_CHANGES_LEDGER_ENTRY_LINES'),
        'plan_items' => env('PENNYLANE_USE_2026_API_CHANGES_PLAN_ITEMS'),
        'products' => env('PENNYLANE_USE_2026_API_CHANGES_PRODUCTS'),
        'supplier_invoices' => env('PENNYLANE_USE_2026_API_CHANGES_SUPPLIER_INVOICES'),
        'suppliers' => env('PENNYLANE_USE_2026_API_CHANGES_SUPPLIERS'),
        'transactions' => env('PENNYLANE_USE_2026_API_CHANGES_TRANSACTIONS'),
    ],
];
