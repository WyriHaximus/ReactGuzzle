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

        $this->loop = \Mockery::mock('React\EventLoop\StreamSelectLoop');
        $this->requestFactory = \Mockery::mock('WyriHaximus\React\Guzzle\HttpClient\RequestFactory');
        $this->httpClient = \Mockery::mock('React\HttpClient\Client', [
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
        ]);
        $this->request = \Mockery::mock('WyriHaximus\React\Guzzle\HttpClient\Request', [
            $this->httpClient,
            $this->loop,
        ]);

        $this->adapter = new \WyriHaximus\React\Guzzle\HttpClientAdapter($this->loop, $this->httpClient, null, $this->requestFactory);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->adapter, $this->request, $this->httpClient, $this->requestFactory, $this->loop);
    }
    
    public function testSend() {
        $this->requestFactory->shouldReceive('create')
            ->with($this->httpClient, $this->loop)
            ->andReturn($this->request)
            ->once();
            

        $this->request->shouldReceive('send')
            ->once();
        $this->adapter->send(\Mockery::mock('GuzzleHttp\Adapter\TransactionInterface'));
    }
    
    /*public function testSetDnsResolver() {
        $this->adapter->shouldReceive('send')
            ->with($this->dnsResolver);
    }*/

}