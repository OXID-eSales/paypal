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
use OxidEsales\Codeception\Step\ProductNavigation;
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
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('blUseStock', false);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function searchPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page in brutto mode');

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
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(119.6);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function searchPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page in netto mode');

        $I->updateConfigInDatabase('blShowNetPrice', true);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $product = Fixtures::get('product');
        $product['price'] = '100,52 €';

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basket->addProductToBasket($product['id'], (int)$product['amount']);
        $homePage
            ->seeMiniBasketContains([$product], $product['price'], $product['amount'])
            ->searchFor($product['title']);

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(100.52);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkoutPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on checkout page in brutto mode');
        $I->haveInDatabase('oxuser', $I->getExistingUserData());

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
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

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
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkoutPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on checkout page in netto mode');
        $I->haveInDatabase('oxuser', $I->getExistingUserData());
        $I->updateConfigInDatabase('blShowNetPrice', true);
        $I
            ->openShop()
            ->loginUser($I->getExistingUserName(), $I->getExistingUserPassword());

        // 0. Prepare basket
        $basket = new Basket($I);
        $basketPage = $basket->addProductToBasketAndOpen(Fixtures::get('product')['id'], 1, 'basket');

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

        $basketPage->goToNextStep()->goToNextStep();
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function categoryPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on category page in brutto mode');

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

        //Check installment banner body in Flow and Wave theme
        $I->updateConfigInDatabase('oePayPalBannersCategoryPage', true);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(119.6);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function categoryPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on category page in netto mode');

        $I->updateConfigInDatabase('blShowNetPrice', true);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basketItem['price'] = '100,52 €';
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount'])
            ->openCategoryPage("Kiteboarding");

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(100.52);
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

        $I->checkInstallmentBannerData();
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkCorrectSumSentToPaypalInstallmentsOnStartPageWithFilledBasketBrutto(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);
        $I->updateConfigInDatabase('blShowNetPrice', false);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage->seeMiniBasketContains([$basketItem], $basketItem['price'], (string)$basketItem['amount']);

        $I->checkInstallmentBannerData(119.6);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkCorrectSumSentToPaypalInstallmentsOnStartPageWithFilledBasketNetto(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);
        $I->updateConfigInDatabase('blShowNetPrice', true);
        $I->updateConfigInDatabase('iNewBasketItemMessage', false);

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basketItem['price'] = '100,52 €';
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage->seeMiniBasketContains([$basketItem], $basketItem['price'], (string)$basketItem['amount']);

        $I->checkInstallmentBannerData(100.52);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function productDetailsPageBannerBrutto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on product details page brutto mode');

        $product = Fixtures::get('variant');
        $basketItem = Fixtures::get('product');

        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPage', false);

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPage', true);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($product['minBruttoPrice']);

        // Check banner amount when basket is not empty
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], 1);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(122.8);

        // Check banner amount when the given product is also in the basket
        $basket->addProductToBasket($product['id'], 1);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(122.8);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function productDetailsPageBannerNetto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on product details page in netto mode');
        $I->updateConfigInDatabase('blShowNetPrice', true);

        $product = Fixtures::get('variant');
        $basketItem = Fixtures::get('product');

        $productNavigation = new ProductNavigation($I);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($product['minNettoPrice']);

        // Check banner amount when basket is not empty
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], 1);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(103.2);

        // Check banner amount when the given product is also in the basket
        $basket->addProductToBasket($product['id'], 1);
        $productNavigation->openProductDetailsPage($product['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(103.2);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function productVariantBannerBrutto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner for selected variant in brutto mode');

        $product = Fixtures::get('variant');

        $productNavigation = new ProductNavigation($I);
        $productDetailPage = $productNavigation->openProductDetailsPage($product['id']);
        $productDetailPage->selectVariant(1, 'W 30/L 30');
        $productDetailPage->selectVariant(2, 'Super Blue');
        $I->checkInstallmentBannerData($product['maxBruttoPrice']);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function productVariantBannerNetto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner for selected variant in netto mode');
        $I->updateConfigInDatabase('blShowNetPrice', true);

        $product = Fixtures::get('variant');

        $productNavigation = new ProductNavigation($I);
        $productDetailPage = $productNavigation->openProductDetailsPage($product['id']);
        $productDetailPage->selectVariant(1, 'W 30/L 30');
        $productDetailPage->selectVariant(2, 'Super Blue');
        $I->checkInstallmentBannerData($product['maxNettoPrice']);
    }
}
