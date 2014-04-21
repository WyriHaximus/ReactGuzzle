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
 * Class RequestFactoryTest
 *
 * @package WyriHaximus\React\Tests\Guzzle
 */
class RequestFactoryTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();

        $this->requestFactory = new RequestFactory();
    }
    
    public function tearDown() {
        parent::tearDown();
        
        unset($this->requestFactory);
    }

    public function testCreate() {
        $this->assertInstanceOf('WyriHaximus\React\Guzzle\HttpClient\Request', $this->requestFactory->create(\Mockery::mock('React\HttpClient\Client', [
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
            \Mockery::mock('React\SocketClient\ConnectorInterface'),
        ]), \Mockery::mock('\React\EventLoop\StreamSelectLoop')));
    }
    
}