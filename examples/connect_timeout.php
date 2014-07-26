<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use React\EventLoop\Factory;
use WyriHaximus\React\Guzzle\HttpClientAdapter;
use WyriHaximus\React\Guzzle\HttpClient\ProgressInterface;

// Create eventloop
$loop = Factory::create();

$guzzle = new Client([
    'adapter' => new HttpClientAdapter($loop),
]);

$guzzle->get('http://www.amazon.com/', ['connect_timeout' => 0.5])->then(function(Response $response) {
    echo 'Amazon completed, we really shouldn\'t get getting here...' . PHP_EOL;
}, function($event) {
    echo 'Amazon error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Amazon progress: ' . $event['event'] . PHP_EOL;
});


$loop->run();
