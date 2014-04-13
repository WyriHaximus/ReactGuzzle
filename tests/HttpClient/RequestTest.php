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
 * Class Requesttest
 *
 * @package WyriHaximus\React\Tests\Guzzle
 */
class RequestTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();

        $this->httpClient = $this->getMock('React\HttpClient\Client', [
            'request',
        ], [
            $this->getMock('React\SocketClient\ConnectorInterface'),
            $this->getMock('React\SocketClient\ConnectorInterface'),
        ]);
        $this->request = new \WyriHaximus\React\Guzzle\HttpClient\Request($this->httpClient);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->httpClient, $this->request);
    }

    public function testSend() {
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

        $connector = $this->getMock('React\SocketClient\ConnectorInterface', [
            'create',
        ]);
        $connector->expects($this->at(0))
            ->method('create')
            ->willReturn($deferred->promise());

        $httpRequest = $this->getMock('React\HttpClient\Request', [
                'trigger',
            ], [
            $connector,
            $this->getMock('React\HttpClient\RequestData', [], [
                $method,
                $url,
                $headers,
            ]),
        ]);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with($method, $url, $headers)
            ->willReturn($httpRequest);

        $request = $this->getMock('GuzzleHttp\Message\RequestInterface', [
            '__toString',
            'setUrl',
            'getUrl',
            'getResource',
            'getQuery',
            'setQuery',
            'getMethod',
            'setMethod',
            'getScheme',
            'setScheme',
            'getHost',
            'setHost',
            'getPath',
            'setPath',
            'getConfig',
            'getProtocolVersion',
            'setBody',
            'getBody',
            'getHeaders',
            'getHeader',
            'hasHeader',
            'removeHeader',
            'addHeader',
            'addHeaders',
            'setHeader',
            'setHeaders',
            'getEmitter',
        ]);
        $request->expects($this->once())
            ->method('getHeaders')
            ->with()
            ->willReturn($headersGuzzle);
        $request->expects($this->once())
            ->method('getMethod')
            ->with()
            ->willReturn($method);
        $request->expects($this->once())
            ->method('getUrl')
            ->with()
            ->willReturn($url);
        $request->expects($this->once())
            ->method('getHeader')
            ->with('X-Guzzle')
            ->willReturn($headers['X-Guzzle']);

        $transaction = $this->getMock('GuzzleHttp\Adapter\TransactionInterface', [
            'getRequest',
            'getResponse',
            'setResponse',
            'getClient',
        ]);
        $transaction->expects($this->once())
            ->method('getRequest')
            ->with()
            ->willReturn($request);

        $this->request->send($transaction);
    }
    
}