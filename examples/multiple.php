<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Create eventloop
$loop = \React\EventLoop\Factory::create();

$guzzle = new \GuzzleHttp\Client([
    'adapter' => new \WyriHaximus\React\Guzzle\HttpClientAdapter($loop),
]);

$guzzle->get('http://www.amazon.com/')->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Amazon completed' . PHP_EOL;
}, function($event) {
    echo 'Amazon error' . PHP_EOL;
}, function(array $event) {
    echo 'Amazon progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.google.com/')->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Google completed' . PHP_EOL;
}, function($event) {
    echo 'Google error' . PHP_EOL;
}, function(array $event) {
    echo 'Google progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.bing.com/')->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Bing completed' . PHP_EOL;
}, function($event) {
    echo 'Bing error' . PHP_EOL;
}, function(array $event) {
    echo 'Bing progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.yahoo.com/')->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Yahoo! completed' . PHP_EOL;
}, function($event) {
    echo 'Yahoo! error' . PHP_EOL;
}, function(array $event) {
    echo 'Yahoo! progress: ' . $event['event'] . PHP_EOL;
});

$guzzle->get('https://www.duckduckgo.com/')->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Duck Duck Go completed' . PHP_EOL;
}, function($event) {
    echo 'Duck Duck Go error' . PHP_EOL;
}, function(array $event) {
    echo 'Duck Duck Go progress: ' . $event['event'] . PHP_EOL;
});

$loop->run();
