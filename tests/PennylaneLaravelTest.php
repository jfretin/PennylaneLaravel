<?php

namespace Ashraam\PennylaneLaravel\Tests;

use Ashraam\PennylaneLaravel\Api\BankAccounts;
use Ashraam\PennylaneLaravel\Api\Transactions;
use Ashraam\PennylaneLaravel\PennylaneLaravel;
use GuzzleHttp\Client;

class PennylaneLaravelTest extends TestCase
{
    public function testFacadeExposesBankAccountsAndTransactionsResources(): void
    {
        $api = new PennylaneLaravel(new Client(), new Client());

        $this->assertInstanceOf(BankAccounts::class, $api->bank_accounts());
        $this->assertInstanceOf(Transactions::class, $api->transactions());
    }
}
