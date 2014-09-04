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

use Phake;

/**
 * Class RequestTest
 * @package WyriHaximus\React\Tests\Guzzle\HttpClient
 */
class RequestTest extends \PHPUnit_Framework_TestCase {

	public function testSend() {
		$requestInterface = Phake::mock('GuzzleHttp\Message\RequestInterface');

		$transaction = Phake::mock('GuzzleHttp\Adapter\TransactionInterface');
		Phake::when($transaction)->getRequest()->thenReturn($requestInterface);

		$loop = Phake::mock('React\EventLoop\LoopInterface');

		$client = Phake::mock('React\HttpClient\Client');
		$request = Phake::partialMock('WyriHaximus\React\Guzzle\HttpClient\Request', $transaction, $client, $loop);

		$this->assertInstanceOf('React\Promise\PromiseInterface', $request->send());

		Phake::verify($loop)->futureTick($this->isType('callable'));
	}

	public function testSetConnectionTimeout() {
		$requestInterface = Phake::mock('GuzzleHttp\Message\RequestInterface');
		Phake::when($requestInterface)->getConfig()->thenReturn([
			'connect_timeout' => 1,
		]);

		$transaction = Phake::mock('GuzzleHttp\Adapter\TransactionInterface');
		Phake::when($transaction)->getRequest()->thenReturn($requestInterface);

		$loop = Phake::mock('React\EventLoop\LoopInterface');
		Phake::when($loop)->addTimer($this->isType('int'), $this->isType('callable'))->thenReturn(true);

		$client = Phake::mock('React\HttpClient\Client');
		$request = Phake::partialMock('WyriHaximus\React\Guzzle\HttpClient\Request', $transaction, $client, $loop);

		$httpClientRequest = Phake::mock('React\HttpClient\Request');
		$request->setConnectionTimeout($httpClientRequest);

		Phake::inOrder(
			Phake::verify($transaction, Phake::times(2))->getRequest(),
			Phake::verify($requestInterface, Phake::times(2))->getConfig(),
			Phake::verify($loop)->addTimer($this->isType('int'), $this->isType('callable'))
		);
	}

	public function testSetRequestTimeout() {
		$requestInterface = Phake::mock('GuzzleHttp\Message\RequestInterface');
		Phake::when($requestInterface)->getConfig()->thenReturn([
			'timeout' => 1,
		]);

		$transaction = Phake::mock('GuzzleHttp\Adapter\TransactionInterface');
		Phake::when($transaction)->getRequest()->thenReturn($requestInterface);

		$loop = Phake::mock('React\EventLoop\LoopInterface');
		Phake::when($loop)->addTimer($this->isType('int'), $this->isType('callable'))->thenReturn(true);

		$client = Phake::mock('React\HttpClient\Client');
		$request = Phake::partialMock('WyriHaximus\React\Guzzle\HttpClient\Request', $transaction, $client, $loop);

		$httpClientRequest = Phake::mock('React\HttpClient\Request');
		$request->setRequestTimeout($httpClientRequest);

		Phake::inOrder(
			Phake::verify($transaction, Phake::times(2))->getRequest(),
			Phake::verify($requestInterface, Phake::times(2))->getConfig(),
			Phake::verify($loop)->addTimer($this->isType('int'), $this->isType('callable'))
		);
	}
    
}
