<?php

/**
 * This file is part of ReactGuzzle.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\React\Tests\Guzzle;

/**
 * Class HttpClientAdapterTest
 *
 * @package WyriHaximus\React\Tests\Guzzle
 */
class HttpClientAdapterTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();

        $this->loop = $this->getMock('\React\EventLoop\StreamSelectLoop');
        $this->requestFactory = $this->getMock('\WyriHaximus\React\Guzzle\HttpClient\RequestFactory', [
            'create',
        ]);
        $httpClient = $this->getMock('React\HttpClient\Client', [], [
            $this->getMock('React\SocketClient\ConnectorInterface'),
            $this->getMock('React\SocketClient\ConnectorInterface'),
        ]);
        $request = $this->getMock('WyriHaximus\React\Guzzle\HttpClient\Request', [
            'send',
        ], [
            $httpClient,
        ]);
        $request->expects($this->once())
            ->method('send')
            ->willReturn($this->getMock('React\Promise\Deferred'));

        $this->requestFactory->expects($this->once())
            ->method('create')
            ->with($httpClient)
            ->willReturn($request);
        $this->adapter = new \WyriHaximus\React\Guzzle\HttpClientAdapter($this->loop, $httpClient, null, $this->requestFactory);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->adapter, $this->requestFactory, $this->loop);
    }
    
    public function testSend() {
        $this->adapter->send($this->getMock('GuzzleHttp\Adapter\TransactionInterface'));
    }

}