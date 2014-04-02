<?php

namespace WyriHaximus\React\Guzzle;

use \React\Promise\Deferred;
use \React\HttpClient\Client as HttpClient;
use \React\HttpClient\Factory as HttpClientFactory;
use \React\HttpClient\Response as HttpResponse;
use \React\Dns\Resolver as DnsResolver;
use \React\Dns\Resolver\Factory as DnsFactory;
use \GuzzleHttp\Message\MessageFactory;

class HttpClientAdapter implements \GuzzleHttp\Adapter\AdapterInterface {

    public function __construct(\React\EventLoop\LoopInterface $loop) {
        $this->messageFactory = new MessageFactory();
    }

    public function setHttpClient(HttpClient $httpClient = null) {
        if (!($httpClient instanceof HttpClient)) {
            $this->setDnsResolver($this->dnsResolver);

            $factory = new HttpClientFactory();
            $httpClient = $factory->create($this->loop, $this->dnsResolver);
        }

        $this->httpClient = $httpClient;
    }

    public function setDnsResolver(DnsResolver $dnsResolver = null) {
        if (!($dnsResolver instanceof DnsResolver)) {
            $dnsResolverFactory = new DnsFactory();
            $dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);
        }

        $this->dnsResolver = $dnsResolver;
    }

    public function send(\GuzzleHttp\Adapter\TransactionInterface $transaction) {
        $deferred = new Deferred();
        $request = $this->client->request($transaction->getRequest()->getMethod(), $transaction->getRequest()->getUrl(), $transaction->getRequest()->getHeaders());

        $httpResponse = null;
        $buffer = '';
        $request->on('response', function(HttpResponse $response) use(&$httpResponse, &$buffer) {
            $httpResponse = $response;
            $response->on('data', function($data) use(&$buffer) {
                $buffer .= $data;
            });
        });
        $request->on('end', function() use(&$httpResponse, &$buffer, $deferred) {
              $deferred->resolve($this->messageFactory->createResponse($httpResponse->getCode(), $httpResponse->getHeaders(), $buffer));
        });
        $request->end();

        return $deferred->promise();
    }
}