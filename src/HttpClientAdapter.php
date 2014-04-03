<?php

namespace WyriHaximus\React\Guzzle;

use \GuzzleHttp\Adapter\AdapterInterface;
use \GuzzleHttp\Adapter\TransactionInterface;
use \GuzzleHttp\Message\MessageFactory;
use \React\Dns\Resolver as DnsResolver;
use \React\Dns\Resolver\Factory as DnsFactory;
use \React\EventLoop\LoopInterface;
use \React\HttpClient\Client as HttpClient;
use \React\HttpClient\Factory as HttpClientFactory;
use \React\HttpClient\Response as HttpResponse;
use \React\HttpClient\Request as HttpRequest;
use \React\Promise\Deferred;


class HttpClientAdapter implements AdapterInterface {

    protected $dnsResolver;
    protected $httpClient;

    public function __construct(LoopInterface $loop, HttpClient $httpClient = null, DnsResolver $dnsResolver = null) {
        $this->loop = $loop;
        $this->messageFactory = new MessageFactory();

        $this->setDnsResolver($dnsResolver);
        $this->setHttpClient($httpClient);
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

    public function send(TransactionInterface $transaction) {
        $deferred = new Deferred();
        $request = $this->setupRequest($transaction);
        $this->setupListeners($request, $deferred);
        $request->end();
        return $deferred->promise();
    }

    protected function setupRequest(TransactionInterface $transaction) {
        $request = $transaction->getRequest();
        $headers = [];
        foreach ($request->getHeaders() as $key => $values) {
            $headers[$key] = $request->getHeader($key);
        }
        return $this->httpClient->request($request->getMethod(), $request->getUrl(), $headers);
    }

    protected function setupListeners(HttpRequest $request, Deferred $deferred) {
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
    }
}
