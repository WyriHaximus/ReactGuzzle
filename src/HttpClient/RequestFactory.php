<?php

/**
 * This file is part of ReactGuzzle.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\React\Guzzle\HttpClient;

use React\HttpClient\Client as HttpClient;

/**
 * Class RequestFactory
 *
 * @package WyriHaximus\React\Guzzle\HttpClient
 */
class RequestFactory
{
    public function create(HttpClient $httpClient) {
        return new Request($httpClient);
    }
}