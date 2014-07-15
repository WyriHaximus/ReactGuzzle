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

$guzzle->get('http://www.amazon.com/')->then(function(Response $response) {
    echo 'Amazon completed' . PHP_EOL;
}, function($event) {
    echo 'Amazon error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Amazon progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.google.com/')->then(function(Response $response) {
    echo 'Google completed' . PHP_EOL;
}, function($event) {
    echo 'Google error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Google progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.bing.com/')->then(function(Response $response) {
    echo 'Bing completed' . PHP_EOL;
}, function($event) {
    echo 'Bing error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Bing progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.yahoo.com/')->then(function(Response $response) {
    echo 'Yahoo! completed' . PHP_EOL;
}, function($event) {
    echo 'Yahoo! error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Yahoo! progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.duckduckgo.com/')->then(function(Response $response) {
    echo 'Duck Duck Go completed' . PHP_EOL;
}, function($event) {
    echo 'Duck Duck Go error' . PHP_EOL;
}, function(ProgressInterface $event) {
    echo 'Duck Duck Go progress: ' . $event['event'] . PHP_EOL;
});

$loop->run();
