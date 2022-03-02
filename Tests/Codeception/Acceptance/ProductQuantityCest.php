<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Step\ProductNavigation;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\Codeception\Module\Translation\Translator;

/**
 * @group oepaypal
 * @group oepaypal_standard
 * @group oepaypal_product_quantity
 */
class ProductQuantityCest extends BaseCest
{
    /**
     * @group product_quantity_paypal                          
     */
    public function increaseProductQuantity(AcceptanceTester $I)
    {
        $I->wantToTest('product details add items popup');

        $this->proceedToBasketStep($I, null, false);

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage(Fixtures::get('product')['id']);
        $I->seeElement("#paypalExpressCheckoutDetailsButton");
        $I->click("#paypalExpressCheckoutDetailsButton");
        $I->see(substr(sprintf(Translator::translate('OEPAYPAL_SAME_ITEM_QUESTION'), 1), 0, 30));
        $I->seeElement("#actionAddToBasketAndGoToCheckout");
        $I->click("#actionAddToBasketAndGoToCheckout");

        $basketItem = Fixtures::get('product');
        $I->amOnUrl($I->getShopUrl());
        $I->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) ($basketItem['amount'] + 1));
    }
}
