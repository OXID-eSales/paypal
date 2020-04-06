<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\PayPalModule\Tests\Acceptance;

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FinalizeOrderOnPayPalTest
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class HttpMockTest extends BaseAcceptanceTestCase
{
    use HttpMockTrait;

    public static $shopUrl = 'http://www.oxideshop.local/en/Kiteboarding/';

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

    /**
     * @group http_mock
     */
    public function testDummyAcceptanceTest()
    {
        $this->http->mock
            ->when()
            ->callback(
                static function (Request $request) {
                    return preg_match('@/cgi-bin@msi', $request->getPathInfo());
                }
            )
            ->then()
            ->callback(
                static function (Response $response) {
                    if (preg_match('@/cgi-bin@msi', $_SERVER['PATH_INFO'])) {
                        $response = new RedirectResponse((new \OxidEsales\Facts\Facts())->getShopUrl() . "en/Kiteboarding/");
                        $response->send();
                    }
                }
                )
            ->end();
        $this->http->setUp();

        $this->openShop();
        $this->searchFor("1001");
        $this->clickAndWait(self::SELECTOR_ADD_TO_BASKET);

        $this->click("id=minibasketIcon");
        $this->waitForElement("paypalExpressCheckoutButton");
        $this->assertElementPresent("paypalExpressCheckoutButton", "PayPal express button not displayed in the cart");
        $this->clickAndWait("id=paypalExpressCheckoutButton");

        $this->assertTextPresent('You are here: / Kiteboarding');

    }
}