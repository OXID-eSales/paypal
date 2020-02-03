<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
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
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $basketItem = Fixtures::get('product');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount'])
            ->searchFor("3503");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow theme
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', true);
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        $I->checkInstallmentBannerData(119.6, '20x1', 'EUR');

        //Check installment banner body in Wave theme
        $I->updateConfigInDatabase('sTheme', 'wave');
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        $I->checkInstallmentBannerData(119.6, '20x1', 'EUR');

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

        $I->checkInstallmentBannerData(119.6, '20x1', 'EUR');

        //Check installment banner body in Wave theme
        $I->updateConfigInDatabase('sTheme', 'wave');
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        $I->checkInstallmentBannerData(119.6, '20x1', 'EUR');

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkBannerPlaceholderAppearsOnStartPageOnlyByCorrectConfig(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', false);
        $I->openShop();
        $I->dontSeeElementInDOM("#paypal-installment-banner-container");

        $I->updateConfigInDatabase('oePayPalBannersStartPage', true);
        $I->clearShopCache();
        $I->openShop();
        $I->seeElementInDOM("#paypal-installment-banner-container");

        $I->click(Translator::translate('HELP'));
        $I->dontSeeElementInDOM("#paypal-installment-banner-container");
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkCorrectDefaultsSentToPaypalInstallmentsOnStartPageWithEmptyBasket(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true);
        $I->openShop();

        $I->checkInstallmentBannerData(0, '20x1', 'EUR');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkCorrectSumSentToPaypalInstallmentsOnStartPageWithFilledBasketBrutto(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true);
        $I->updateConfigInDatabase('blShowNetPrice', false);

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = [
            'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
            'title' => 'Kuyichi leather belt JEVER',
            'amount' => 4,
            'price' => '119,60 €'
        ];
        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $homePage->seeMiniBasketContains([$basketItem], $basketItem['price'], (string)$basketItem['amount']);

        $I->checkInstallmentBannerData(119.6);
    }
}
