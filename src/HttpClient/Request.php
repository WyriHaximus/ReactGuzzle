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
     * @var ReactHttpClient
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
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var \Exception
     */
    protected $error = '';
    
    /**
     * @var \React\EventLoop\Timer\TimerInterface
     */
    protected $connectionTimer;

    /**
     * @var \React\EventLoop\Timer\TimerInterface
     */
    protected $requestTimer;

    /**
     * @var ProgressInterface
     */
    protected $progress;

    /**
     * @var Deferred
     */
    protected $deferred;

    /**
     * @var TransactionInterface
     */
    protected $transaction;

    /**
     * @var bool
     */
    protected $connectionTimedOut = false;

    /**
     * @param ReactHttpClient $httpClient
     * @param LoopInterface $loop
     * @param ProgressInterface $progress
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
        $this->transaction = $transaction;
        $this->deferred = new Deferred();

        $this->loop->futureTick(function() {
            RequestEvents::emitBefore($this->transaction);

            $request = $this->setupRequest();
            $this->setupListeners($request);

            $this->setConnectionTimeout($request);
            $request->end((string)$this->transaction->getRequest()->getBody());
            $this->setRequestTimeout($request);
        });

        return $this->deferred->promise();
    }

    /**
     * @return HttpRequest mixed
     */
    protected function setupRequest()
    {
        $request = $this->transaction->getRequest();
        $headers = [];
        foreach ($request->getHeaders() as $key => $values) {
            $headers[$key] = $request->getHeader($key);
        }
        return $this->httpClient->request($request->getMethod(), $request->getUrl(), $headers);
    }

    /**
     * @param HttpRequest $request
     */
    protected function setupListeners(HttpRequest $request)
    {
        $request->on(
            'headers-written',
            function () {
                $this->onHeadersWritten();
            }
        );
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

    /**
     * @param HttpRequest $request
     */
    public function setConnectionTimeout(HttpRequest $request) {
        if ($this->transaction->getRequest()->getConfig()['connect_timeout']) {
            $this->connectionTimer = $this->loop->addTimer($this->transaction->getRequest()->getConfig()['connect_timeout'], function() use ($request) {
                $request->closeError(new \Exception('Connection time out'));
            });
        }
    }

    /**
     * @param HttpRequest $request
     */
    public function setRequestTimeout(HttpRequest $request) {
        if ($this->transaction->getRequest()->getConfig()['timeout']) {
            $this->requestTimer = $this->loop->addTimer($this->transaction->getRequest()->getConfig()['timeout'], function() use ($request) {
                $request->close(new \Exception('Transaction time out'));
            });
        }
    }

    protected function onHeadersWritten() {
        if ($this->connectionTimer !== null) {
            $this->loop->cancelTimer($this->connectionTimer);
        }
    }

    /**
     * @param HttpResponse $response
     */
    protected function onResponse(HttpResponse $response) {
        $config = $this->transaction->getRequest()->getConfig();
        if (!empty($config['save_to'])) {
            $this->saveTo($response);
        } else {
            $response->on(
                'data',
                function ($data) use ($response) {
                    $this->onData($data);
                }
            );
        }

        $this->deferred->progress($this->progress->setEvent('response')->onResponse($response));

        $this->httpResponse = $response;
    }

    /**
     * @param HttpResponse $response
     */
    protected function saveTo(HttpResponse $response) {
        $saveTo = $this->transaction->getRequest()->getConfig()['save_to'];

        $writeStream = fopen($saveTo, 'w');
        stream_set_blocking($writeStream, 0);
        $saveToStream = new Stream($writeStream, $this->loop);
        
        $saveToStream->on(
            'end',
            function () {
                $this->onEnd();
            }
        );
        
        $response->pipe($saveToStream);
    }

    /**
     * @param string $data
     */
    protected function onData($data) {
        if (!$this->transaction->getRequest()->getConfig()['stream']) {
            $this->buffer .= $data;
        }

        $this->deferred->progress($this->progress->setEvent('data')->onData($data));
    }

    /**
     * @param \Exception $error
     */
    protected function onError(\Exception $error) {
        $this->error = $error;
    }

    /**
     *
     */
    protected function onEnd() {
        if ($this->requestTimer !== null) {
            $this->loop->cancelTimer($this->requestTimer);
        }
        
        if ($this->httpResponse === null) {
            $this->deferred->reject($this->error);
        } else {
            $response = $this->messageFactory->createResponse(
                $this->httpResponse->getCode(),
                $this->httpResponse->getHeaders(),
                $this->buffer
            );
            $this->transaction->setResponse($response);

            $this->loop->futureTick(function() use ($response) {
                try {
                    RequestEvents::emitComplete($this->transaction);
                    $this->deferred->resolve($response);
                } catch(\Exception $exception) {
                    $this->deferred->reject($exception);
                }
            });
        }
    }
}
