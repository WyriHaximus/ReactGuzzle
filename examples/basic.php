<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use React\EventLoop\Factory;
use WyriHaximus\React\Guzzle\HttpClientAdapter;
use WyriHaximus\React\Guzzle\HttpClient\ProgressInterface;

// Create eventloop
$loop = Factory::create();

(new Client([
    'adapter' => new HttpClientAdapter($loop),
]))->get('http://docs.guzzlephp.org/en/latest/')->then(function(Response $response) { // Success callback
    var_export($response);
}, function($event) { // Error callback
    var_export($event);
}, function(ProgressInterface $event) { // Progress callback
    var_export($event['event']);
});

$loop->run();
