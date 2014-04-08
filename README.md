ReactHttpClientGuzzleAdapter
============================

ReactPHP HttpClient Adapter for Guzzle

## Current state ##

This adapter is in active development and is subject to change at any given time. Untill 0.1.0 is tagged, use with caution!

## Example ##

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


```