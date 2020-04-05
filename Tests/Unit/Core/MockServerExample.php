<?php

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class MockServerExample extends \OxidEsales\TestingLibrary\UnitTestCase
{
    use HttpMockTrait;

    public static function setUpBeforeClass()
    {
        static::setUpHttpMockBeforeClass('8082', 'localhost');
    }

    public static function tearDownAfterClass()
    {
        static::tearDownHttpMockAfterClass();
    }

    public function setUp()
    {
        $this->setUpHttpMock();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownHttpMock();
        parent::tearDown();
    }

    public function testSimpleRequest()
    {
        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/resource')
            ->then()
            ->body('mocked body')
            ->end();
        $this->http->setUp();

        $this->assertSame('mocked body', file_get_contents('http://localhost:8082/resource'));
    }

    public function testAccessingRecordedRequests()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/foo')
            ->then()
            ->body('mocked body')
            ->end();
        $this->http->setUp();

        $this->assertSame('mocked body', $this->http->client->post('http://localhost:8082/foo')->send()->getBody(true));

        $this->assertSame('POST', $this->http->requests->latest()->getMethod());
        $this->assertSame('/foo', $this->http->requests->latest()->getPath());
    }

    public function testAsd123()
    {
        $this->http->mock
            ->when()
            ->methodIs($this->http->matches->regex('/(GET|POST)/'))
            ->pathIs(
                $this->http->matches->regex('@^/.*?@msi')
            )
            ->then()
            ->body('response')
            ->end();
        $this->http->setUp();

        $this->assertSame('response', $this->http->client->post('http://localhost:8082/foo')->send()->getBody(true));

        $this->assertSame('POST', $this->http->requests->latest()->getMethod());
        $this->assertSame('/foo', $this->http->requests->latest()->getPath());
    }
}