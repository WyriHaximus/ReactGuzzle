<?php

/**
 * This file is part of ReactGuzzle.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\React\Tests\Guzzle\HttpClient;

/**
 * Class RequestTest
 * @package WyriHaximus\React\Tests\Guzzle\HttpClient
 */
class RequestTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();

        $loop = \Mockery::mock('\React\EventLoop\StreamSelectLoop');
        $loop->shouldReceive('nextTick')
            ->withAnyArgs()
            ->atLeast(2)
            ->andReturn(\Mockery::on(function($callback) {
                $callback();
                return null;
            }));

        $this->httpClient = \Mockery::mock('React\HttpClient\Client', [
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
        ]);
        $this->request = new \WyriHaximus\React\Guzzle\HttpClient\Request($this->httpClient, $loop);
        
        $headersGuzzle = [
            'X-Guzzle' => [
                'React',
                'HttpClient',
            ],
        ];
        $headers = [
            'X-Guzzle' => 'React, HttpClient',
        ];
        $method = \GuzzleHttp\Message\RequestInterface::GET;
        $url = 'http://example.com/';

        $deferred = new \React\Promise\Deferred();

        $connector = \Mockery::mock('React\SocketClient\ConnectorInterface');
        $connector->shouldReceive('create')
            ->andReturn($deferred->promise());

        $httpRequest = \Mockery::mock('React\HttpClient\Request', [
            $connector,
            \Mockery::mock('React\HttpClient\RequestData', [
                $method,
                $url,
                $headers,
            ]),
        ]);
        $httpRequest->shouldReceive('on');
        $httpRequest->shouldReceive('end');

        $this->httpClient->shouldReceive('request')
            ->with($method, $url, $headers)
            ->andReturn($httpRequest)
            ->once();

        $request = \Mockery::mock('GuzzleHttp\Message\RequestInterface');
        $request->shouldReceive('getHeaders')
            ->with()
            ->andReturn($headersGuzzle)
            ->once();
        $request->shouldReceive('getMethod')
            ->with()
            ->andReturn($method)
            ->once();
        $request->shouldReceive('getUrl')
            ->with()
            ->andReturn($url)
            ->once();
        $request->shouldReceive('getHeader')
            ->with('X-Guzzle')
            ->andReturn($headers['X-Guzzle'])
            ->once();
        $request->shouldReceive('getConfig');
        $emitter = \Mockery::mock('GuzzleHttp\Event\EmitterInterface');
        $emitter->shouldReceive('emit');
        $request->shouldReceive('getEmitter')
            ->with()
            ->andReturn($emitter)
            ->once();

        $this->transaction = \Mockery::mock('GuzzleHttp\Adapter\TransactionInterface');
        $this->transaction->shouldReceive('getRequest')
            ->with()
            ->andReturn($request);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->httpClient, $this->request);
    }

    public function testSend() {
        $this->request->send($this->transaction);
    }
    
}