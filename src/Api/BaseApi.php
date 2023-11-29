<?php

namespace Ashraam\PennylaneLaravel\Api;

use GuzzleHttp\ClientInterface;

abstract class BaseApi
{
    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

}
