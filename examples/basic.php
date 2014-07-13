<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Create eventloop
$loop = \React\EventLoop\Factory::create();

(new \GuzzleHttp\Client([
    'adapter' => new \WyriHaximus\React\Guzzle\HttpClientAdapter($loop),
]))->get('http://docs.guzzlephp.org/en/latest/')->then(function(\GuzzleHttp\Message\Response $response) { // Success callback
    var_export($response);
}, function($event) { // Error callback
    var_export($event);
}, function(\WyriHaximus\React\Guzzle\HttpClient\ProgressInterface $event) { // Progress callback
    var_export($event['event']);
});

$loop->run();
