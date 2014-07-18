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

foreach ([
    [
        'name' => 'Nu',
        'url' => 'http://www.nu.nl/',
    ],
    [
        'name' => 'Yahoo!',
        'url' => 'http://www.yahoo.com/',
    ],
    [
        'name' => 'Duck Duck Go',
        'url' => 'http://www.duckduckgo.com/',
    ],
] as $site) {
    $name = $site['name'];

    $guzzle->get($site['url'])->then(function(Response $response) use ($name) {
        echo $name . ' completed' . PHP_EOL;
    }, function($event) use ($name) {
        echo $name . ' error' . PHP_EOL;
    }, function(ProgressInterface $event) use ($name) {
        echo $name . ' progress: ' . number_format($event->getCompletePercentage(), 2) . '%' . PHP_EOL;
    });
}

$loop->run();
