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