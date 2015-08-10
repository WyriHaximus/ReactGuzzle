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

use GuzzleHttp\Message\Response;
use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use React\Dns\Resolver\Factory as DnsFactory;
use React\Dns\Resolver\Resolver as DnsResolver;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client as HttpClient;
use React\HttpClient\Factory as HttpClientFactory;
use WyriHaximus\React\Guzzle\HttpClient\RequestFactory;

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
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @param LoopInterface $loop
     * @param HttpClient $httpClient
     * @param DnsResolver $dnsResolver
     */
    public function __construct(
        LoopInterface $loop,
        HttpClient $httpClient = null,
        DnsResolver $dnsResolver = null,
        RequestFactory $requestFactory = null
    ) {
        $this->loop = $loop;

        $this->setDnsResolver($dnsResolver);
        $this->setHttpClient($httpClient);
        $this->setRequestFactory($requestFactory);
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
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
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
     * @return DnsResolver
     */
    public function getDnsResolver()
    {
        return $this->dnsResolver;
    }

    /**
     * @param RequestFactory $requestFactory
     */
    public function setRequestFactory(RequestFactory $requestFactory = null)
    {
        if (!($requestFactory instanceof RequestFactory)) {
            $requestFactory = new RequestFactory();
        }

        $this->requestFactory = $requestFactory;
    }

    /**
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->requestFactory;
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return \React\Promise\Promise
     */
    public function send(TransactionInterface $transaction)
    {
        return $this->requestFactory->create(
            static::transformRequest($transaction),
            [],
            $this->httpClient,
            $this->loop
        )->then(function (ResponseInterface $response) {
            return \React\Promise\resolve(static::transformResponse($response));
        });
    }

    protected static function transformRequest(TransactionInterface $transaction)
    {
        return new Request(
            $transaction->getRequest()->getMethod(),
            $transaction->getRequest()->getUrl(),
            $transaction->getRequest()->getHeaders(),
            $transaction->getRequest()->getBody(),
            $transaction->getRequest()->getProtocolVersion()
        );
    }

    protected static function transformResponse(ResponseInterface $response)
    {
        return new Response($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }
}
