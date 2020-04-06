<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use Codeception\Util\Locator;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FinalizeOrderOnPayPalTest
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class HttpMockCest extends TestCase
{
    use HttpMockTrait;

    /**
     * @param AcceptanceTester $I
     */
    public function _before(AcceptanceTester $I)
    {
        static::setUpHttpMockBeforeClass('8082', 'localhost');
        $this->setUpHttpMock();

            $I->setPayPalSettingsData();
    }

    public function _after()
    {
        static::tearDownHttpMockAfterClass();
        $this->tearDownHttpMock();
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group http_mock
     */
    public function testDummyAcceptanceTest(AcceptanceTester $I)
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

        $I->openShop();
        $basket = new Basket($I);

        $basketItem = Fixtures::get('product');

        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount']);

        $I->openShop()->openMiniBasket();

        $paypalButton = Locator::find(
            'input',
            ['id' => 'paypalExpressCheckoutMiniBasketImage']
        );

        $I->click($paypalButton);

        $I->seeCurrentUrlEquals('/en/Kiteboarding/');

    }
}