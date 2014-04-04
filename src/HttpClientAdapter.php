<?php

/**
 * This file is part of ReactGuzzle.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\React\Guzzle;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\MessageFactory;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver as DnsResolver;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client as HttpClient;
use React\HttpClient\Factory as HttpClientFactory;
use React\HttpClient\Request as HttpRequest;
use React\HttpClient\Response as HttpResponse;
use React\Promise\Deferred;

/**
 * Class HttpClientAdapter
 *
 * @package WyriHaximus\React\Guzzle
 */
class HttpClientAdapter implements AdapterInterface
{

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var DnsResolver
     */
    protected $dnsResolver;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param LoopInterface $loop
     * @param HttpClient $httpClient
     * @param DnsResolver $dnsResolver
     */
    public function __construct(LoopInterface $loop, HttpClient $httpClient = null, DnsResolver $dnsResolver = null)
    {
        $this->loop = $loop;
        $this->messageFactory = new MessageFactory();

        $this->setDnsResolver($dnsResolver);
        $this->setHttpClient($httpClient);
    }

    /**
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient = null)
    {
        if (!($httpClient instanceof HttpClient)) {
            $this->setDnsResolver($this->dnsResolver);

            $factory = new HttpClientFactory();
            $httpClient = $factory->create($this->loop, $this->dnsResolver);
        }

        $this->httpClient = $httpClient;
    }

    /**
     * @param DnsResolver $dnsResolver
     */
    public function setDnsResolver(DnsResolver $dnsResolver = null)
    {
        if (!($dnsResolver instanceof DnsResolver)) {
            $dnsResolverFactory = new DnsFactory();
            $dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);
        }

        $this->dnsResolver = $dnsResolver;
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return \React\Promise\Promise
     */
    public function send(TransactionInterface $transaction)
    {
        $deferred = new Deferred();
        $request = $this->setupRequest($transaction);
        $this->setupListeners($request, $deferred);
        $request->end();
        return $deferred->promise();
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return mixed
     */
    protected function setupRequest(TransactionInterface $transaction)
    {
        $request = $transaction->getRequest();
        $headers = [];
        foreach ($request->getHeaders() as $key => $values) {
            $headers[$key] = $request->getHeader($key);
        }
        return $this->httpClient->request($request->getMethod(), $request->getUrl(), $headers);
    }

    /**
     * @param HttpRequest $request
     * @param Deferred $deferred
     */
    protected function setupListeners(HttpRequest $request, Deferred $deferred)
    {
        $httpResponse = null;
        $buffer = '';
        $request->on(
            'response',
            function (HttpResponse $response) use (&$httpResponse, &$buffer) {
                $httpResponse = $response;
                $response->on(
                    'data',
                    function ($data) use (&$buffer) {
                        $buffer .= $data;
                    }
                );
            }
        );
        $request->on(
            'error',
            function ($error) use ($deferred) {
                $deferred->reject($error);
            }
        );
        $request->on(
            'end',
            function () use (&$httpResponse, &$buffer, $deferred) {
                $response = $this->messageFactory->createResponse(
                    $httpResponse->getCode(),
                    $httpResponse->getHeaders(),
                    $buffer
                );
                $deferred->resolve($response);
            }
        );
    }
}
