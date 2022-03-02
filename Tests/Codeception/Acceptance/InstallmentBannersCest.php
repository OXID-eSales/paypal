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
 * @group oepaypal
 * @group oepaypal_standard
 * @group oepaypal_installment_banners
 */
class InstallmentBannersCest extends BaseCest
{
    public function _after(AcceptanceTester $I): void
    {
        $I->activateFlowTheme();
        $I->clearShopCache();

        parent::_after($I);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_search
     */
    public function searchPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page in brutto mode');

        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', false, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $basketItem = Fixtures::get('product');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount'])
            ->searchFor("3503");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow theme
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', true, 'bool');
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(119.6);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true, 'bool');
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_search
     */
    public function searchPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page in netto mode');

        $I->updateConfigInDatabase('blShowNetPrice', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $product = Fixtures::get('product');
        $product['price'] = '100,52 €';

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basket->addProductToBasket($product['id'], (int)$product['amount']);
        $homePage
            ->seeMiniBasketContains([$product], $product['price'], (string) $product['amount'])
            ->searchFor($product['title']);

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(100.52);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_checkout
     */
    public function checkoutPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on checkout page in brutto mode');
        $I->haveInDatabase('oxuser', $I->getExistingUserData());

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', false, 'bool');

        $I
            ->openShop()
            ->loginUser($I->getExistingUserName(), $I->getExistingUserPassword());

        // 0. Prepare basket
        $basket = new Basket($I);
        $basketPage = $basket->addProductToBasketAndOpenBasket(Fixtures::get('product')['id'], 1, 'basket');

        // 1. Basket overview
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', true, 'bool');
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true, 'bool');
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        // 3. Payment
        $I->updateConfigInDatabase('oePayPalBannersHideAll', false, 'bool');
        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', false, 'bool');

        $basketPage->goToNextStep()->goToNextStep();

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersCheckoutPage', true, 'bool');
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true, 'bool');
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_checkout
     */
    public function checkoutPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on checkout page in netto mode');
        $I->haveInDatabase('oxuser', $I->getExistingUserData());
        $I->updateConfigInDatabase('blShowNetPrice', true, 'bool');
        $I
            ->openShop()
            ->loginUser($I->getExistingUserName(), $I->getExistingUserPassword());

        // 0. Prepare basket
        $basket = new Basket($I);
        $basketPage = $basket->addProductToBasketAndOpenBasket(Fixtures::get('product')['id'], 1, 'basket');

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);

        $basketPage->goToNextStep()->goToNextStep();
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(33.8);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_category
     */
    public function categoryPageBannerInBruttoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on category page in brutto mode');

        $I->updateConfigInDatabase('oePayPalBannersCategoryPage', false, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount'])
            ->openCategoryPage("Kiteboarding");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow and Wave theme
        $I->updateConfigInDatabase('oePayPalBannersCategoryPage', true, 'bool');
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(119.6);

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true, 'bool');
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_category
     */
    public function categoryPageBannerInNettoMode(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on category page in netto mode');

        $I->updateConfigInDatabase('blShowNetPrice', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basketItem['price'] = '100,52 €';
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage
            ->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount'])
            ->openCategoryPage("Kiteboarding");

        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(100.52);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     */
    public function checkBannerPlaceholderAppearsOnStartPageOnlyByCorrectConfig(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', false, 'bool');
        $I->openShop();
        $I->dontSeeElementInDOM("#paypal-installment-banner-container");

        $I->updateConfigInDatabase('oePayPalBannersStartPage', true, 'bool');
        $I->clearShopCache();
        $I->openShop();
        $I->seeElementInDOM("#paypal-installment-banner-container");

        $I->click(Translator::translate('HELP'));
        $I->dontSeeElementInDOM("#paypal-installment-banner-container");
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     */
    public function checkCorrectDefaultsSentToPaypalInstallmentsOnStartPageWithEmptyBasket(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true, 'bool');
        $I->openShop();

        $I->checkInstallmentBannerData();
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     */
    public function checkCorrectSumSentToPaypalInstallmentsOnStartPageWithFilledBasketBrutto(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');
        $I->updateConfigInDatabase('blShowNetPrice', false, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basket->addProductToBasket($basketItem['id'], (int) $basketItem['amount']);
        $homePage->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount']);

        $I->checkInstallmentBannerData(119.6);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     */
    public function checkCorrectSumSentToPaypalInstallmentsOnStartPageWithFilledBasketNetto(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('oePayPalBannersStartPage', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');
        $I->updateConfigInDatabase('blShowNetPrice', true, 'bool');
        $I->updateConfigInDatabase('iNewBasketItemMessage', false, 'bool');

        $homePage = $I->openShop();
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basketItem['price'] = '100,52 €';
        $basket->addProductToBasket($basketItem['id'], (int)$basketItem['amount']);
        $homePage->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount']);

        $I->checkInstallmentBannerData(100.52);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_details
     */
    public function productDetailsPageBannerBrutto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on product details page brutto mode');

        $parentProduct = Fixtures::get('parent');
        $variant = Fixtures::get('variant');
        $alternateVariant = Fixtures::get('alternate_variant');
        $basketItem = Fixtures::get('product');

        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPage', false, 'bool');

        $parentProductNavigation = new ProductNavigation($I);
        $parentProductNavigation->openProductDetailsPage($parentProduct['id'])
            ->seeOnBreadCrumb(Translator::translate('YOU_ARE_HERE'));
        $I->dontSee(Translator::translate('ERROR_MESSAGE_ARTICLE_ARTICLE_NOT_BUYABLE'));
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPage', true, 'bool');
        $parentProductNavigation->openProductDetailsPage($parentProduct['id'])
            ->seeOnBreadCrumb(Translator::translate('YOU_ARE_HERE'));
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($parentProduct['minBruttoPrice'], Translator::translate('YOU_ARE_HERE'));

        // Check banner amount when basket is not empty
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], 1);
        $parentProductNavigation->openProductDetailsPage($parentProduct['id'])
            ->seeOnBreadCrumb(Translator::translate('YOU_ARE_HERE'));
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['bruttoprice_single'] + $parentProduct['minBruttoPrice'],
            Translator::translate('YOU_ARE_HERE')
        );

        // Check banner amount when the given product is also in the basket
        $basket->addProductToBasket($variant['id'], 1);
        $I->waitForPageLoad();
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($basketItem['bruttoprice_single'] + $variant['bruttoprice']); //check on front page

        //check banner in case we open variant parent details page and have no variant selected
        $parentProductNavigation->openProductDetailsPage($parentProduct['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['bruttoprice_single'] + $variant['bruttoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page

        //check banner in case we open alternate variant details page, alternate variant price should be added to price
        $parentProductNavigation->openProductDetailsPage($alternateVariant['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['bruttoprice_single'] + $variant['bruttoprice'] + $alternateVariant['bruttoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page */

        //check banner in case we open variant details page
        $parentProductNavigation->openProductDetailsPage($variant['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['bruttoprice_single'] + $variant['bruttoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page */

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true, 'bool');
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_details
     */
    public function productDetailsPageBannerNetto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on product details page in netto mode');
        $I->updateConfigInDatabase('blShowNetPrice', true, 'bool');

        $parentProduct = Fixtures::get('parent');
        $variant = Fixtures::get('variant');
        $alternateVariant = Fixtures::get('alternate_variant');
        $basketItem = Fixtures::get('product');

        $parentProductNavigation = new ProductNavigation($I);
        $parentProductNavigation->openProductDetailsPage($parentProduct['id'])
            ->seeOnBreadCrumb(Translator::translate('YOU_ARE_HERE'));
        $I->waitForPageLoad();
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($parentProduct['minNettoPrice']);

        // Check banner amount when basket is not empty
        $basket = new Basket($I);
        $basket->addProductToBasket($basketItem['id'], 1);   
        $parentProductNavigation->openProductDetailsPage($parentProduct['id'])
            ->seeOnBreadCrumb(Translator::translate('YOU_ARE_HERE'));
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($basketItem['nettoprice_single'] + $parentProduct['minNettoPrice']);

        // Check banner amount when the given product is also in the basket
        $basket->addProductToBasket($variant['id'], 1); 
        $I->waitForPageLoad(); 
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme($basketItem['nettoprice_single'] + $variant['nettoprice']); //check on front page

        //check banner in case we open variant parent details page and have no variant selected
        $parentProductNavigation->openProductDetailsPage($parentProduct['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['nettoprice_single'] + $variant['nettoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page

        //check banner in case we open alternate variant details page, alternate variant price should be added to price
        $parentProductNavigation->openProductDetailsPage($alternateVariant['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['nettoprice_single'] + $variant['nettoprice'] + $alternateVariant['nettoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page */

        //check banner in case we open variant details page
        $parentProductNavigation->openProductDetailsPage($variant['id']);
        $I->seePayPalInstallmentBannerInFlowAndWaveTheme(
            $basketItem['nettoprice_single'] + $variant['nettoprice'],
            Translator::translate('YOU_ARE_HERE')
        ); //check on details page */
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_variant
     */
    public function productVariantBannerBrutto(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner for selected variant in brutto mode');

        $product = Fixtures::get('parent');

        $productNavigation = new ProductNavigation($I);
        $productNavigation
            ->openProductDetailsPage($product['id'])
            ->selectVariant(1, 'W 30/L 30')
            ->selectVariant(2, 'Super Blue')
            ->seeProductData([
                'id'          => '0702-85-853-1-3',
                'title'       => 'Kuyichi Jeans ANNA W 30/L 30 | Super Blue',
                'description' => 'Cool lady jeans by Kuyichi',
                'price'       => '99,90 €'
            ]);
        $I->checkInstallmentBannerData($product['maxBruttoPrice']);

        $I->activateWaveTheme();
        $I->reloadPage();
        $I->waitForPageLoad();
        $productDetails = $productNavigation
            ->openProductDetailsPage($product['id']);
        $this->selectWaveVariant($I, 1, 'W 30/L 30');
        $this->selectWaveVariant($I, 2, 'Super Blue');
        $productDetails->seeProductData([
                'id'          => '0702-85-853-1-3',
                'title'       => 'Kuyichi Jeans ANNA W 30/L 30 | Super Blue',
                'description' => 'Cool lady jeans by Kuyichi',
                'price'       => '99,90 €'
            ]);
        $I->checkInstallmentBannerData($product['maxBruttoPrice']);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group installment_banners_paypal
     * @group installment_banners_paypal_variant
     */
    public function productVariantBannerWrongSelector(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner with wrong selector');

        $I->updateConfigInDatabase('oePayPalBannersProductDetailsPageSelector', '.non-existing-css-selector', 'str');

        $product = Fixtures::get('parent');

        $productNavigation = new ProductNavigation($I);
        $productNavigation
            ->openProductDetailsPage($product['id'])
            ->seeProductData([
                'id'          => '3570',
                'title'       => 'Kuyichi Jeans ANNA',
                'description' => 'Cool lady jeans by Kuyichi',
                'price'       => 'from 92,90 € *'
            ])
            ->selectVariant(1, "W 30/L 30", "W 30/L 30")
            ->selectVariant(2, "Blue", "W 30/L 30, Blue")
            ->seeProductData([
                'id'          => '0702-85-853-1-1',
                'title'       => 'Kuyichi Jeans ANNA W 30/L 30 | Blue',
                'description' => 'Cool lady jeans by Kuyichi',
                'price'       => '99,90 €'
            ]);

        $I->activateWaveTheme();
        $I->reloadPage();
        $I->waitForPageLoad();
        $productDetails = $productNavigation
            ->openProductDetailsPage($product['id'])
            ->seeProductData([
                'id'          => '3570',
                'title'       => 'Kuyichi Jeans ANNA',
                'description' => 'Cool lady jeans by Kuyichi',
                'price'       => 'from 92,90 € *'
        ]);
        $this->selectWaveVariant($I, 1, 'W 30/L 30');
        $this->selectWaveVariant($I, 2, 'Blue');
        $productDetails->seeProductData([
            'id'          => '0702-85-853-1-1',
            'title'       => 'Kuyichi Jeans ANNA W 30/L 30 | Blue',
            'description' => 'Cool lady jeans by Kuyichi',
            'price'       => '99,90 €'
        ]);
    }

    /**
     * @param AcceptanceTester $I
     * @param int              $variant      The position of the variant.
     * @param string           $variantValue The value of the variant.
     */
    private function selectWaveVariant(AcceptanceTester $I, int $variant, string $variantValue)
    {
        $variantSelection = '/descendant::button[@class="btn btn-outline-dark btn-sm dropdown-toggle"][%s]';
        $variantOpenSelection = '//ul[@class="dropdown-menu  vardrop"]';

        $I->click(sprintf($variantSelection, $variant));
        $I->click($variantValue);
        if ($I->see($variantValue)) {
            $I->click($variantValue);
        }
        $I->waitForElementNotVisible($variantOpenSelection);
        $I->waitForPageLoad();
        $I->see($variantValue);
    }
}
