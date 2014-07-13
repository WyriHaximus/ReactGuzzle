<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Create eventloop
$loop = \React\EventLoop\Factory::create();

(new \GuzzleHttp\Client([
    'adapter' => new \WyriHaximus\React\Guzzle\HttpClientAdapter($loop),
]))->get('http://www.google.com/robots.txt', ['save_to' => 'google-robots.txt'])->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Done!' . PHP_EOL;
}, function($event) {
    echo 'Error: ' . var_export($event, true) . PHP_EOL;
}, function(\WyriHaximus\React\Guzzle\HttpClient\ProgressInterface $event) {
    echo 'Progress: '. $event['event'] . PHP_EOL;
});

$loop->run();
