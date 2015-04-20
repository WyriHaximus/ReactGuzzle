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

use Phake;
use React\Promise\FulfilledPromise;
use WyriHaximus\React\Guzzle\HttpClientAdapter;

/**
 * Class HttpClientAdapterTest
 *
 * @package WyriHaximus\React\Tests\Guzzle
 */
class HttpClientAdapterTest extends \PHPUnit_Framework_TestCase {

	protected $transaction;
	protected $loop;
	protected $requestFactory;
	protected $httpClient;
	protected $request;
	protected $adapter;

    public function setUp() {
        parent::setUp();

        $this->httpClient = Phake::partialMock('React\HttpClient\Client',
            Phake::mock('React\SocketClient\ConnectorInterface'),
            Phake::mock('React\SocketClient\ConnectorInterface')
        );

        $this->loop = Phake::mock('React\EventLoop\StreamSelectLoop');
        $this->requestFactory = Phake::mock('WyriHaximus\React\Guzzle\HttpClient\RequestFactory');

        $this->request = Phake::partialMock('WyriHaximus\React\Guzzle\HttpClient\Request',
            [],
            $this->httpClient,
            $this->loop
        );

        $guzzleRequest = Phake::mock('GuzzleHttp\Message\RequestInterface');
		$this->transaction = Phake::mock('GuzzleHttp\Adapter\TransactionInterface');
        Phake::when($this->transaction)->getRequest()->thenReturn($guzzleRequest);

        $this->adapter = new HttpClientAdapter($this->loop, $this->httpClient, null, $this->requestFactory);
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->adapter, $this->request, $this->httpClient, $this->requestFactory, $this->loop, $this->transaction);
    }
    
    public function testSend() {
		Phake::when($this->requestFactory)->create($this->isType('array'), $this->httpClient, $this->loop)->thenReturn(new FulfilledPromise());

        $this->adapter->send($this->transaction);

		Phake::inOrder(
			Phake::verify($this->requestFactory, Phake::times(1))->create($this->isType('array'), $this->httpClient, $this->loop)
		);
    }
    
    public function testSetDnsResolver() {
        $this->adapter->setDnsResolver();
        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $this->adapter->getDnsResolver());

        $mock = Phake::partialMock('React\Dns\Resolver\Resolver',
			Phake::mock('React\Dns\Query\ExecutorInterface'),
			Phake::mock('React\Dns\Query\ExecutorInterface')
        );
        $this->adapter->setDnsResolver($mock);
        $this->assertSame($mock, $this->adapter->getDnsResolver());
    }

    public function testSetHttpClient() {
        $this->adapter->setHttpClient();
        $this->assertInstanceOf('React\HttpClient\Client', $this->adapter->getHttpClient());

        $mock = Phake::partialMock('React\HttpClient\Client',
			Phake::mock('React\SocketClient\ConnectorInterface'),
			Phake::mock('React\SocketClient\ConnectorInterface')
		);
        $this->adapter->setHttpClient($mock);
        $this->assertSame($mock, $this->adapter->getHttpClient());
    }

    public function testSetRequestFactory() {
        $this->adapter->setRequestFactory();
        $this->assertInstanceOf('WyriHaximus\React\Guzzle\HttpClient\RequestFactory', $this->adapter->getRequestFactory());

        $mock = Phake::mock('WyriHaximus\React\Guzzle\HttpClient\RequestFactory');
        $this->adapter->setRequestFactory($mock);
        $this->assertSame($mock, $this->adapter->getRequestFactory());
    }
}
