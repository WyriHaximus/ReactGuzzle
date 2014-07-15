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
]))->get('http://www.google.com/robots.txt', ['save_to' => 'google-robots.txt'])->then(function(Response $response) {
    echo 'Done!' . PHP_EOL;
}, function($event) {
    echo 'Error: ' . var_export($event, true) . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Progress: '. $event['event'] . PHP_EOL;
});

$loop->run();
