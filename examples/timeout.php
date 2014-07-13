<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Create eventloop
$loop = \React\EventLoop\Factory::create();

$guzzle = new \GuzzleHttp\Client([
    'adapter' => new \WyriHaximus\React\Guzzle\HttpClientAdapter($loop),
]);

$guzzle->get('http://www.amazon.com/', ['timeout' => 0.5])->then(function(\GuzzleHttp\Message\Response $response) {
    echo 'Amazon completed' . PHP_EOL;
}, function($event) {
    echo 'Amazon error' . PHP_EOL;
}, function(\WyriHaximus\React\Guzzle\HttpClient\ProgressInterface $event) {
    echo 'Amazon progress: ' . $event['event'] . PHP_EOL;
});


$loop->run();
