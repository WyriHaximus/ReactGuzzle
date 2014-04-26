ReactHttpClientGuzzleAdapter
============================

[![Build Status](https://travis-ci.org/WyriHaximus/ReactGuzzle.png)](https://travis-ci.org/WyriHaximus/ReactGuzzle)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-guzzle/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-guzzle)
[![Total Downloads](https://poser.pugx.org/WyriHaximus/react-guzzle/downloads.png)](https://packagist.org/packages/WyriHaximus/react-guzzle)
[![Coverage Status](https://coveralls.io/repos/WyriHaximus/ReactGuzzle/badge.png)](https://coveralls.io/r/WyriHaximus/ReactGuzzle)
[![License](https://poser.pugx.org/wyrihaximus/react-guzzle/license.png)](https://packagist.org/packages/wyrihaximus/react-guzzle)

ReactPHP HttpClient Adapter for Guzzle

## Current state ##

This adapter is in active development and is subject to change at any given time. Untill 0.1.0 is tagged, use with caution!


## Installation ##

Installation is easy with composer just add ReactGuzzle to your composer.json.

```json
{
	"require": {
		"wyrihaximus/react-guzzle": "dev-master"
	}
}
```

## Basic Usage ##

```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Create eventloop
$loop = \React\EventLoop\Factory::create();

(new \GuzzleHttp\Client([
    'adapter' => new \WyriHaximus\React\Guzzle\HttpClientAdapter($loop),
]))->get('http://docs.guzzlephp.org/en/latest/')->then(function($event) { // Success callback
    var_export($event);
}, function($event) { // Error callback
    var_export($event);
}, function($event) { // Progress callback
    var_export($event);
});

$loop->run();

```

See the examples directory for more ways to use this adapter.

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

