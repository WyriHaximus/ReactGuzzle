<?php

/**
 * This file is part of ReactGuzzle.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\React\Guzzle\HttpClient;

use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\MessageFactory;
use React\HttpClient\Client as ReactHttpClient;
use React\HttpClient\Request as HttpRequest;
use React\HttpClient\Response as HttpResponse;
use React\Promise\Deferred;

/**
 * Class Request
 *
 * @package WyriHaximus\React\Guzzle\HttpClient
 */
class Request
{
    /**
     * @var HttpClient
     */
    protected $httpClient;
    
    /**
     * @var HttpResponse
     */
    protected $httpResponse;

    /**
     * @var string
     */
    protected $buffer = '';

    protected $options = [
        'buffer' => false,
    ];

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(ReactHttpClient $httpClient, array $options = []) {
        $this->httpClient = $httpClient;
        $this->messageFactory = new MessageFactory();

        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return \React\Promise\Promise
     */
    public function send(TransactionInterface $transaction) {
        $this->deferred = new Deferred();
        $request = $this->setupRequest($transaction);
        $this->setupListeners($request);
        $request->end();
        return $this->deferred->promise();
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
    protected function setupListeners(HttpRequest $request)
    {
        $request->on(
            'response',
            function (HttpResponse $response) {
                $this->onResponse($response);
            }
        );
        $request->on(
            'error',
            function ($error) {
                $this->onError($error);
            }
        );
        $request->on(
            'end',
            function () {
                $this->onEnd();
            }
        );
    }

    protected function onResponse(HttpResponse $response) {
        $response->on(
            'data',
            function ($data) {
                $this->onData($data);
            }
        );

        $this->deferred->progress([
            'event' => 'response',
            'response' => $response,
        ]);

        $this->httpResponse = $response;
    }

    protected function onData($data) {
        if ($this->options['buffer']) {
            $this->buffer .= $data;
        }

        $this->deferred->progress([
            'event' => 'data',
            'data' => $data,
        ]);
    }

    protected function onError($error) {
        $this->error = $error;
    }

    protected function onEnd() {
        if ($this->httpResponse === null) {
            $this->deferred->reject($this->error);
        } else {
            $response = $this->messageFactory->createResponse(
                $this->httpResponse->getCode(),
                $this->httpResponse->getHeaders(),
                $this->buffer
            );
            $this->deferred->resolve($response);
        }
    }
}
