<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use React\EventLoop\Factory;
use WyriHaximus\React\Guzzle\HttpClientAdapter;

// Create eventloop
$loop = Factory::create();

$client = new Client([
    'adapter' => new HttpClientAdapter($loop),
    'base_url' => 'https://api.twitter.com/1.1/',
]);

$oauth = new Oauth1([
    'consumer_key'    => 'FILL_IN_YOUR_OWN',
    'consumer_secret' => 'FILL_IN_YOUR_OWN',
    'token'           => 'FILL_IN_YOUR_OWN',
    'token_secret'    => 'FILL_IN_YOUR_OWN'
]);

$client->getEmitter()->attach($oauth);

// Set the "auth" request option to "oauth" to sign using oauth
$client->get('statuses/home_timeline.json', [
    'auth' => 'oauth',
])->then(function(Response $response) { // Success callback
    var_export(json_decode((string)$response->getBody()));
}, function($event) { // Error callback
    echo 'Timeline error' . PHP_EOL;
});

$loop->run();
