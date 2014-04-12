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

use WyriHaximus\React\Guzzle\HttpClient\RequestFactory;

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
        
        unset($this->requestFactory);
    }

    public function testSend() {
        /*$this->httpClient->expects($this->once())
            ->method('create')
            ->with($this->httpClient)
            ->willReturn('');
        $this->request->send($this->getMock('GuzzleHttp\Adapter\TransactionInterface'));*/
    }
    
}