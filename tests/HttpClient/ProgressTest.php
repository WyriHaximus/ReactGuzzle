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
 * Class ProgressTest
 * @package WyriHaximus\React\Tests\Guzzle\HttpClient
 */
class ProgressTest extends \PHPUnit_Framework_TestCase {

    private $progress;

    public function setUp() {
        parent::setUp();

        $this->progress = new \WyriHaximus\React\Guzzle\HttpClient\Progress();
    }

    public function tearDown() {
        parent::tearDown();

        unset($this->progress);
    }

    public function testGetUnknownIndex() {
        $this->assertSame(false, isset($this->progress['kgfw3oiur']));
        $this->assertSame(null, $this->progress['kgfw3oiur']);
    }

    public function testSetEvent() {
        $this->assertSame(null, $this->progress['event']);

        $this->progress->setEvent('data');
        $this->assertSame('data', $this->progress['event']);
    }

    public function testOnData() {
        $this->assertSame(null, $this->progress['data']);
        $this->assertSame(0, $this->progress['currentSize']);

        $this->progress->onData('data');
        $this->assertSame('data', $this->progress['data']);
        $this->assertSame(4, $this->progress['currentSize']);

        $this->progress->onData('foo');
        $this->assertSame('foo', $this->progress['data']);
        $this->assertSame(7, $this->progress['currentSize']);

        $this->progress->onData('bar');
        $this->assertSame('bar', $this->progress['data']);
        $this->assertSame(10, $this->progress['currentSize']);
    }

    public function onResponseProvider() {
        return [
            [
                [],
                null,
            ],
            [
                [
                    'Content-Length' => 123,
                ],
                123,
            ],
        ];
    }

    /**
     * @dataProvider onResponseProvider
     */
    public function testOnResponse($headers, $expectedLength) {
        $httpResponse = \Mockery::mock('React\HttpClient\Response');
        $httpResponse->shouldReceive('getHeaders')
            ->once()
            ->andReturn($headers);

        $this->progress->onResponse($httpResponse);
        $this->assertSame($httpResponse, $this->progress['response']);
        $this->assertSame($expectedLength, $this->progress['fullSize']);
    }

    public function isFullSizeKnownProvider() {
        return [
            [
                true,
                [
                    'Content-Length' => 123,
                ],
            ],
            [
                true,
                [
                    'Content-Length' => 0,
                ],
            ],
            [
                false,
                [],
            ],
        ];
    }

    /**
     * @dataProvider isFullSizeKnownProvider
     */
    public function testIsFullSizeKnown($expectedResult, $headers) {
        $response = \Mockery::mock('React\HttpClient\Response');

        $response->shouldReceive('getHeaders')
            ->with()
            ->andReturn($headers)
            ->once();
        $this->progress->onResponse($response);
        $this->assertSame($expectedResult, $this->progress->isFullSizeKnown());
    }

    public function getCompletePercentageProvider() {
        return [
            [
                '0',
                [],
                '',
            ],
            [
                '50',
                [
                    'Content-Length' => 2,
                ],
                'a'
            ],
            [
                '33.33',
                [
                    'Content-Length' => 3,
                ],
                'a'
            ],
            [
                '100',
                [
                    'Content-Length' => 3,
                ],
                'abc'
            ],
        ];
    }

    /**
     * @dataProvider getCompletePercentageProvider
     */
    public function testGetCompletePercentage($expectedResult, $headers, $dataChunk) {
        $response = \Mockery::mock('React\HttpClient\Response');

        $response->shouldReceive('getHeaders')
            ->with()
            ->andReturn($headers)
            ->once();
        $this->assertSame($expectedResult, substr($this->progress
                ->onResponse($response)
                ->onData($dataChunk)
                ->getCompletePercentage(), 0, 5));
    }

}