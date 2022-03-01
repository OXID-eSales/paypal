<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;

/**
 * @group oepaypal
 * @group oepaypal_deactivated
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

    public function deactivatedModuleDoesNoHarm(AcceptanceTester $I): void
    {
        $I->wantToTest('that the shop is safe with installed but inactive PayPal Module');

        $I->deactivatePaypalModule();

        $home = $I->openShop()
            ->loginUser(Fixtures::get('userName'), Fixtures::get('userPassword'));
        $I->waitForText(Translator::translate('HOME'));

        //add product to basket and start checkout
        $this->fillBasket($I);
        $I->dontSee('paypalExpressCheckoutButtonECS');

        $this->fromBasketToPayment($I);
        $I->dontSee('#payment_oxidpaypal');
    }

    public function seeButtonsInExpectedLocations(AcceptanceTester $I): void
    {
        $I->wantToTest('that all PayPal checkout buttons are shown');

        $home = $I->openShop()
            ->loginUser(Fixtures::get('userName'), Fixtures::get('userPassword'));
        $I->waitForText(Translator::translate('HOME'));

        //add product to basket and start checkout
        $this->fillBasket($I);
        $I->dontSee('paypalExpressCheckoutButtonECS');

        $this->fromBasketToPayment($I);
        $I->dontSee('#payment_oxidpaypal');
    }

}