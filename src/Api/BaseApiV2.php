<?php

namespace Ashraam\PennylaneLaravel\Api;

use GuzzleHttp\ClientInterface;

class BaseApiV2 extends BaseApi
{
    const API_NAMESPACE = 'v2/';

    private $sort_fields = [];

    private $filter_fields = [];
}
