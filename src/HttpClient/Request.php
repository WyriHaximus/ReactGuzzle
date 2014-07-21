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
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Message\MessageFactory;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client as ReactHttpClient;
use React\HttpClient\Request as HttpRequest;
use React\HttpClient\Response as HttpResponse;
use React\Promise\Deferred;
use React\Stream\Stream;

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
     * @var LoopInterface
     */
    protected $loop;
    
    /**
     * @var HttpResponse
     */
    protected $httpResponse;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var string
     */
    protected $error = '';
    
    /**
     * @var null
     */
    protected $timer;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(ReactHttpClient $httpClient, LoopInterface $loop, ProgressInterface $progress = null) {
        $this->httpClient = $httpClient;
        $this->loop = $loop;
        $this->messageFactory = new MessageFactory();

        if ($progress instanceof ProgressInterface) {
            $this->progress = $progress;
        } else {
            $this->progress = new Progress();
        }
    }

    /**
     * @param TransactionInterface $transaction
     *
     * @return \React\Promise\Promise
     */
    public function send(TransactionInterface $transaction) {
        RequestEvents::emitBefore($transaction);
        $this->deferred = new Deferred();

        $request = $this->setupRequest($transaction);
        $this->setupListeners($request, $transaction);

        $request->end();
        $this->setTimeout($request, $transaction);

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
    protected function setupListeners(HttpRequest $request, TransactionInterface $transaction)
    {
        $request->on(
            'response',
            function (HttpResponse $response) use ($transaction) {
                $this->onResponse($response, $transaction);
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
            function () use ($transaction) {
                $this->onEnd($transaction);
            }
        );
    }
    
    public function setTimeout(HttpRequest $request, TransactionInterface $transaction) {
        if ($transaction->getRequest()->getConfig()['timeout']) {
            $this->timer = $this->loop->addTimer($transaction->getRequest()->getConfig()['timeout'], function() use ($request) {
                $request->close(new \Exception('Transaction time out'));
            });
        }
    }

    protected function onResponse(HttpResponse $response, TransactionInterface $transaction) {
        $config = $transaction->getRequest()->getConfig();
        if (!empty($config['save_to'])) {
            $this->saveTo($response, $transaction);
        } else {
            $response->on(
                'data',
                function ($data) use ($response, $transaction) {
                    $this->onData($data, $transaction);
                }
            );
        }

        $this->deferred->progress($this->progress->setEvent('response')->onResponse($response));

        $this->httpResponse = $response;
    }
    
    protected function saveTo(HttpResponse $response, TransactionInterface $transaction) {
        $saveTo = $transaction->getRequest()->getConfig()['save_to'];

        $writeStream = fopen($saveTo, 'w');
        stream_set_blocking($writeStream, 0);
        $saveToStream = new Stream($writeStream, $this->loop);
        
        $saveToStream->on(
            'end',
            function () use ($transaction) {
                $this->onEnd($transaction);
            }
        );
        
        $response->pipe($saveToStream);
    }

    protected function onData($data, TransactionInterface $transaction) {
        if (!$transaction->getRequest()->getConfig()['stream']) {
            $this->buffer .= $data;
        }

        $this->deferred->progress($this->progress->setEvent('data')->onData($data));
    }

    protected function onError($error) {
        $this->error = $error;
    }

    protected function onEnd(TransactionInterface $transaction) {
        if ($this->timer !== null) {
            $this->loop->cancelTimer($this->timer);
        }
        
        if ($this->httpResponse === null) {
            $this->deferred->reject($this->error);
        } else {
            $response = $this->messageFactory->createResponse(
                $this->httpResponse->getCode(),
                $this->httpResponse->getHeaders(),
                $this->buffer
            );
            //RequestEvents::emitComplete($transaction);
            $this->deferred->resolve($response);
        }
    }
}
