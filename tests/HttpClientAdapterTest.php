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

use WyriHaximus\React\Guzzle\HttpClientAdapter;

/**
 * Class HttpClientAdapterTest
 *
 * @package WyriHaximus\React\Tests\Guzzle
 */
class HttpClientAdapterTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();

		$this->transaction = \Mockery::mock('GuzzleHttp\Adapter\TransactionInterface');
        $this->loop = \Mockery::mock('React\EventLoop\StreamSelectLoop');
        $this->requestFactory = \Mockery::mock('WyriHaximus\React\Guzzle\HttpClient\RequestFactory');
        $this->httpClient = \Mockery::mock('React\HttpClient\Client', [
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
        ]);
        $this->request = \Mockery::mock('WyriHaximus\React\Guzzle\HttpClient\Request', [
			$this->transaction,
			$this->httpClient,
            $this->loop,
        ]);

        $this->adapter = new HttpClientAdapter($this->loop, $this->httpClient, null, $this->requestFactory);
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
        $this->adapter->send($this->transaction);
    }
    
    public function testSetDnsResolver() {
        $this->adapter->setDnsResolver();
        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $this->adapter->getDnsResolver());

        $mock = $this->getMock('React\Dns\Resolver\Resolver', [], [
            $this->getMock('React\Dns\Query\ExecutorInterface'),
            $this->getMock('React\Dns\Query\ExecutorInterface'),
        ]);
        $this->adapter->setDnsResolver($mock);
        $this->assertSame($mock, $this->adapter->getDnsResolver());
    }

    public function testSetHttpClient() {
        $this->adapter->setHttpClient();
        $this->assertInstanceOf('React\HttpClient\Client', $this->adapter->getHttpClient());

        $mock = $this->getMock('React\HttpClient\Client', [], [
                $this->getMock('React\SocketClient\ConnectorInterface'),
                $this->getMock('React\SocketClient\ConnectorInterface'),
            ]);
        $this->adapter->setHttpClient($mock);
        $this->assertSame($mock, $this->adapter->getHttpClient());
    }

    public function testSetRequestFactory() {
        $this->adapter->setRequestFactory();
        $this->assertInstanceOf('WyriHaximus\React\Guzzle\HttpClient\RequestFactory', $this->adapter->getRequestFactory());

        $mock = $this->getMock('WyriHaximus\React\Guzzle\HttpClient\RequestFactory');
        $this->adapter->setRequestFactory($mock);
        $this->assertSame($mock, $this->adapter->getRequestFactory());
    }

}