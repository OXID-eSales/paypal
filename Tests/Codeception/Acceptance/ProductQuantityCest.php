<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\Codeception\Step\ProductNavigation;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;

/**
 * Class ProductQuantityCest
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class ProductQuantityCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->clearShopCache();
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->updateInDatabase('oxuser', Fixtures::get('adminData'), ['OXUSERNAME' => 'admin']);
    }

    /**
     * @param AcceptanceTester $I
     *                           
     * @group product_quantity_paypal                          
     */
    public function increaseProductQuantity(AcceptanceTester $I)
    {
        $I->setPayPalSettingsData();

        $basket = new Basket($I);

        $basketItem = Fixtures::get('product');

        $expectedBasketContent = [
            'id' => $basketItem['id'],
            'title' => $basketItem['title'],
            'amount' => 5,
            'price' => '149,50 €'
        ];

        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount']);

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage($basketItem['id']);
        $I->waitForElementVisible('#paypalExpressCheckoutDetailsButton', 20);
        $I->click("#paypalExpressCheckoutDetailsButton");
        $I->waitForElementVisible('#actionAddToBasketAndGoToCheckout', 20);
        $I->click("#actionAddToBasketAndGoToCheckout");
        $I->waitForDocumentReadyState();

        $paypalPage = new PaypalLogin($I);
        $paypalPage->acceptAllPaypalCookies;
        $paypalPage->cancelPayPal();

        $I->openShop()->seeMiniBasketContains([$expectedBasketContent], $expectedBasketContent['price'], $expectedBasketContent['amount']);
    }
}
