<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Step\ProductNavigation;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;

/**
 * @group oepaypal
 * @group oepaypal_standard
 * @group oepaypal_buttons
 *
 * Tests for checkout with regular payment method 'oxidpaypal'
 */
class ButtonPlacementCest extends BaseCest
{
    public function _after(AcceptanceTester $I): void
    {
        $I->activatePaypalModule();

        parent::_after($I);
    }

    /**
     * @group oepaypal_deactivated
     */
    public function deactivatedModuleDoesNoHarm(AcceptanceTester $I): void
    {
        $I->wantToTest('that the shop is safe with installed but inactive PayPal Module');

        $I->deactivatePaypalModule();

        $I->openShop();
        $I->waitForText(Translator::translate('HOME'));

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage(Fixtures::get('product')['id']);
        $I->dontSeeElement("#paypalExpressCheckoutDetailsButton");

        $home = $I->openShop()
            ->loginUser(Fixtures::get('userName'), Fixtures::get('userPassword'));
        $I->waitForText(Translator::translate('HOME'));

        $this->fillBasket($I);
        $I->dontSeeElement('//input[@name="paypalExpressCheckoutButtonECS"]');

        $home->openMiniBasket();
        $I->dontSeeElement('#paypalExpressCheckoutMiniBasketImage');

        $this->fromBasketToPayment($I);
        $I->dontSeeElement('#payment_oxidpaypal');
    }

    public function seeButtonsInExpectedLocations(AcceptanceTester $I): void
    {
        $I->wantToTest('that all PayPal checkout buttons are shown');

        $I->openShop();
        $I->waitForText(Translator::translate('HOME'));

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage(Fixtures::get('product')['id']);
        $I->seeElement("#paypalExpressCheckoutDetailsButton");

        $home = $I->openShop()
            ->loginUser(Fixtures::get('userName'), Fixtures::get('userPassword'));
        $I->waitForText(Translator::translate('HOME'));

        //add product to basket and start checkout
        $this->fillBasket($I);
        $I->seeElement('//input[@name="paypalExpressCheckoutButtonECS"]');

        $home->openMiniBasket();
        $I->seeElement('#paypalExpressCheckoutMiniBasketImage');

        $this->fromBasketToPayment($I);
        $I->seeElement('#payment_oxidpaypal');
    }

}