<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;

/**
 * Class InstallmentBannersCest
 *
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class InstallmentBannersCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function _before(AcceptanceTester $I)
    {
        $I->clearShopCache();
        $I->haveInDatabase('oxuser', $I->getExistingUserData());
        $I->setPayPalSettingsData();
    }

    /**
     * @param AcceptanceTester $I
     */
    public function searchPageBanner(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page');

        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', false);

        $I
            ->openShop()
            ->searchFor("1001");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow theme
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', true);
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        //Check installment banner body in Wave theme
        $I->updateConfigInDatabase('sTheme', 'wave');
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkoutPageBanner(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on checkout page');

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', false);

        $I
            ->openShop()
            ->loginUser($I->getExistingUserName(), $I->getExistingUserPassword());

        // 0. Prepare basket
        $basket = new Basket($I);
        $basketPage = $basket->addProductToBasketAndOpen(Fixtures::get('product')['id'], 1, 'basket');

        // 1. Basket overview
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', true);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme();

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        // 3. Payment
        $I->updateConfigInDatabase('oePayPalBannersHideAll', false);
        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', false);

        $basketPage->goToNextStep()->goToNextStep();

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', true);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme();

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function categoryPageBanner(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on category page');

        $I->updateConfigInDatabase('oePayPalBannersCategoryPage', false);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount'])
            ->openCategoryPage("Kiteboarding");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow theme
        $I->updateConfigInDatabase('oePayPalBannersCategoryPage', true);
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        $onloadMethod = $I->executeJS("return window.onload.toString()");
        $I->assertRegExp($this->prepareMessagePartRegex("amount: 119.6"), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex("ratio: '20x1'"), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex("currency: 'EUR'"), $onloadMethod);

        //Check installment banner body in Wave theme
        $I->updateConfigInDatabase('sTheme', 'wave');
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        $onloadMethod = $I->executeJS("return window.onload.toString()");
        $I->assertRegExp($this->prepareMessagePartRegex("amount: 119.6"), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex("ratio: '20x1'"), $onloadMethod);
        $I->assertRegExp($this->prepareMessagePartRegex("currency: 'EUR'"), $onloadMethod);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * Wrap the message part in message required conditions
     *
     * @param string $part
     * @return string
     */
    protected function prepareMessagePartRegex($part)
    {
        return "/paypal.Messages\(\{[^}\)]*{$part}/";
    }
}
