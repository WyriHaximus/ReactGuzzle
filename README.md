ReactGuzzle
===========

[![Build Status](https://travis-ci.org/WyriHaximus/ReactGuzzle.png)](https://travis-ci.org/WyriHaximus/ReactGuzzle)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-guzzle/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-guzzle)
[![Total Downloads](https://poser.pugx.org/WyriHaximus/react-guzzle/downloads.png)](https://packagist.org/packages/WyriHaximus/react-guzzle)
[![Coverage Status](https://coveralls.io/repos/WyriHaximus/ReactGuzzle/badge.png)](https://coveralls.io/r/WyriHaximus/ReactGuzzle)
[![License](https://poser.pugx.org/wyrihaximus/react-guzzle/license.png)](https://packagist.org/packages/wyrihaximus/react-guzzle)

ReactPHP HttpClient Adapter for Guzzle4, for Guzzle5 check [ReactGuzzleRing](https://github.com/WyriHaximus/ReactGuzzleRing) and Guzzle6 check [react-guzzle-psr7](https://github.com/WyriHaximus/react-guzzle-psr7)

## Installation ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require wyrihaximus/react-guzzle 
```

## Basic Usage ##

```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use React\EventLoop\Factory;
use WyriHaximus\React\Guzzle\HttpClientAdapter;
use WyriHaximus\React\Guzzle\HttpClient\ProgressInterface;

// Create eventloop
$loop = Factory::create();

$client = new Client([
    'adapter' => new HttpClientAdapter($loop),
]);
$client->get('http://docs.guzzlephp.org/en/latest/')->then(function(Response $response) { // Success callback
    var_export($response);
}, function($event) { // Error callback
    var_export($event);
}, function(ProgressInterface $event) { // Progress callback
    var_export($event);
});

$loop->run();

```

See the [examples](https://github.com/WyriHaximus/ReactGuzzle/tree/master/examples) directory for more ways to use this adapter.

## Note about save_to ##

File I/O in react is blocking and doesn't always play well with certain event loops. So use `save_to` with caution as it will have a less then optimal performance.

## License ##

Copyright 2014 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

