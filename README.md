# Pennylane API wrapper for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ashraam/pennylane-laravel.svg?style=flat-square)](https://packagist.org/packages/ashraam/pennylane-laravel)

Please read the [official API documentation](https://pennylane.readme.io/reference#presentation) to know what are the required fields for each endpoint.
___

## Installation

You can install the package via composer:

```bash
composer require ashraam/pennylane-laravel
```

Then add the Pennylance API KEY in the `.env` file.

```bash
PENNYLANE_API_KEY=my_api_key
```
___

## Usage

### List all customers
```php
$customers = PennylaneLaravel::customers()->list();
```
___

### Get a customer by it's ID
```php
$customer = PennylaneLaravel::customers()->get(999);
```
___

### Create a new customer
```php
$customer = PennylaneLaravel::customers()->create([
    'source_id' => (string) 1,
    'customer_type' => 'individual',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'gender' => 'mister',
    'address' => "Street",
    'postal_code' => 'zip code',
    'city' => 'City',
    'country_alpha2' => 'FR',
    'emails' => ['john.doe@gmail.com'],
    'phone' => '+33625478510'
]);
```
___

### Update a customer
```php
$customer = PennylaneLaravel::customers()->update(1, [
    'delivery_address' => 'Delivery address',
    'delivery_postal_code' => 'Delivery zip code',
    'delivery_city' => 'Delivery city',
    'delivery_country_alpha2' => 'FR'
]);
```
___

### List all products
```php
$products = PennylaneLaravel::products()->list();
```
___

### Get a product by it's ID
```php
$product = PennylaneLaravel::products()->get(1);
```
___

### Create a new product
```php
$product = PennylaneLaravel::products()->create([
    'source_id' => (string) 1,
    'label' => 'Product 1',
    'unit' => 'piece',
    'price_before_tax' => 10,
    'price' => 12,
    'vat_rate' => 'FR_200',
    'currency' => 'EUR',
    'reference' => 'ref-001'
]);
```
___

### Update a product
```php
$product = PennylaneLaravel::products()->update(1, [
    'description' => 'Updated description'
]);
```
___

### List all invoices
```php
$invoices = PennylaneLaravel::invoices()->list();

// Invoices can be filtered
$invoices = PennylaneLaravel::invoices()->list([
    [
        'field' => 'customer_id',
        'operator' => 'eq',
        'value' => (string) 1
    ],
    [
        'field' => 'status',
        'operator' => 'eq',
        'value' => 'draft_status'
    ]
]);
```
___

### Get an invoice by it's ID
```php
$invoice = PennylaneLaravel::invoices()->get('RNT9MXHXAD');
```
___

### Create an invoice
Second and third default value is set to **false**
```php
$invoice = PennylaneLaravel::invoices()->create([
    'date' => today()->format('Y-m-d'),
    'deadline' => today()->addDays(15)->format('Y-m-d'),
    'draft' => false,
    'customer' => [
        'source_id' => (string) 1
    ],
    'line_items' => [
        [
            'label' => "My special item",
            'quantity' => 3,
            'product' => [
                'source_id' => (string) 1
            ]
        ],
        [
            'label' => "Remise",
            'quantity' => 1,
            'currency_amount' => -10,
            'unit' => 'piece',
            'vat_rate' => 'FR_200'
        ]
    ]
], $create_customers = false, $create_products = false);
```
___

### Import an invoice
Third default value is set to **false**
```php
$invoice = PennylaneLaravel::invoices()->import([
    'date' => today()->format('Y-m-d'),
    'deadline' => today()->addDays(15)->format('Y-m-d'),
    'invoice_number' => 'F-874',
    'currency' => 'EUR',
    'customer' => [
        'source_id' => (string) 1
    ],
    'line_items' => [
        [
            'label' => "My special item",
            'quantity' => 3,
            'product' => [
                'source_id' => (string) 1
            ]
        ],
        [
            'label' => "Remise",
            'quantity' => 1,
            'currency_amount' => -10,
            'unit' => 'piece',
            'vat_rate' => 'FR_200'
        ]
    ]
], $file_url, $create_customer = false);
```
___

### List all estimates
```php
$estimates = PennylaneLaravel::estimates()->list();
```
___

### Get an estimate by it's ID
```php
$estimate = PennylaneLaravel::estimates()->get('VVAWLPY8QB');
```
___

### Create a new estimate
```php
$estimate = PennylaneLaravel::estimates()->create([
    'date' => today()->format('Y-m-d'),
    'deadline' => today()->addDays(15)->format('Y-m-d'),
    'customer' => [
        'source_id' => (string) 1
    ],
    'line_items' => [
        [
            'label' => "My special item",
            'quantity' => 3,
            'product' => [
                'source_id' => (string) 1
            ]
        ],
        [
            'label' => "Random line",
            'quantity' => 1,
            'currency_amount' => 17.85,
            'unit' => 'piece',
            'vat_rate' => 'FR_200'
        ]
    ]
]);
```
___

### Get an enum
The second parameter default value is **en**
```php
$values = PennylaneLaravel::enums()->get('unit', 'fr');
```
___

### List Ledger Accounts
```php
$accounts = PennylaneLaravel::ledger_accounts()->list(1, 25);
```
___

### List Ledger Accounts with filter on number start
```php
$accounts = PennylaneLaravel::ledger_accounts()->list(1, 25, [['field'=>'number', 'operator'=>'start_with', 'value'=>'607']]);
```
___

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.
___

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
___

### Security

If you discover any security related issues, please email romain.bertolucci@gmail.com instead of using the issue tracker.
___

## Credits

-   [Romain Bertolucci](https://github.com/ashraam)
___

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.