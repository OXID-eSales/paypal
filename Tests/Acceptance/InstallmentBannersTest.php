<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Acceptance;

/**
 * This test class contains acceptance tests for the PayPal Module Installment Banners,
 * which are only OXID eShop GUI parts but get JS code from PP.
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class InstallmentBannersTest extends BaseAcceptanceTestCase
{
    const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';
    const PRODUCT_ARTNUM = '3503';
    const PRODUCT_TITLE_EN = 'Kuyichi leather belt JEVER';
    const PRODUCT_AMOUNT = 4;
    const PRODUCT_PRICE = 29.9;

    const PAYPAL_CONTAINER_ID = 'paypal-installment-banner-container';
    const PAYPAL_FRAME_LOCATOR = "//div[contains(@id, 'paypal-installment-banner-container')]//iframe";

    private $theme = 'flow';
    private $payPalBannersStartPageSelector = '#wrapper .row';
    private $payPalBannersSearchResultsPageSelector = '#content .page-header .clearfix';
    private $payPalBannersProductDetailsPageSelector = '.detailsParams';
    private $payPalBannersPaymentPageSelector = '.checkoutSteps ~ .spacer';

    /**
     * Tets installment banner on start page
     *
     * @group paypal_nobuyerlogin
     */
    public function testBannerOnStartPage()
    {
        //start page banner off
        $this->openShop(false, true);
        $this->switchLanguage("Deutsch");
        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));

        //start page banner on
        $this->setInstallmentBannerConfigFlag('oePayPalBannersStartPage', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->checkInstallmentBannerData();

        $this->open($this->getTestConfig()->getShopUrl() . "/Hilfe-Main/");
        $this->assertElementNotPresent(self::PAYPAL_CONTAINER_ID);

        //Check banner visibility when oePayPalBannersHideAll setting is set to true
        $this->setInstallmentBannerConfigFlag('oePayPalBannersHideAll', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));

        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));
    }

    /**
     * Selects shop language in frontend.
     *
     * @param string $language Language title.
     */
    public function switchLanguage($language)
    {
        $this->clickAndWait("//a[@title='" . $language . "']");
    }

    /**
     * @param $name
     * @param $value
     */
    private function setInstallmentBannerConfigFlag($name, $value)
    {
        $this->callShopSC(
            'oxConfig', null, null, [
                $name => [
                    'type'   => 'bool',
                    'value'  => $value,
                    'module' => 'module:oepaypal'
                ]
            ]
        );
    }

    /**
     * @param float  $amount
     * @param string $ratio
     * @param string $currency
     */
    private function checkInstallmentBannerData(float $amount = 0, string $ratio = '20x1', string $currency = 'EUR')
    {
        $this->assertElementPresent(self::PAYPAL_CONTAINER_ID);
        $this->assertElementPresent(self::PAYPAL_FRAME_LOCATOR);

        $bruteForceScript = "document.getElementById('" . self::PAYPAL_CONTAINER_ID . "').innerHTML=PayPalMessage.toString();";
        $this->getMinkSession()->getDriver()->executeScript($bruteForceScript);
        $content = $this->getElement(self::PAYPAL_CONTAINER_ID)->getText();

        $this->assertRegExp($this->prepareMessagePartRegex(sprintf("amount: %s", $amount)), $content);
        $this->assertRegExp($this->prepareMessagePartRegex(sprintf("ratio: '%s'", $ratio)), $content);
        $this->assertRegExp($this->prepareMessagePartRegex(sprintf("currency: '%s'", $currency)), $content);
    }

    /**
     * Wrap the message part in message required conditions
     *
     * @param string $part
     * @return string
     */
    private function prepareMessagePartRegex($part)
    {
        return "/paypal.Messages\(\{[^}\)]*{$part}/";
    }

    /**
     * Test installment banner on search page
     *
     * @group paypal_nobuyerlogin
     */
    public function testBannerOnSearchPage()
    {
        //search banner off
        $this->openShop(false, true);
        $this->switchLanguage("Deutsch");
        $this->searchFor(self::PRODUCT_ARTNUM);
        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));

        //search banner on
        $this->setInstallmentBannerConfigFlag('oePayPalBannersSearchResultsPage', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->searchFor(self::PRODUCT_ARTNUM);
        $this->checkInstallmentBannerData();

        //Check banner visibility when oePayPalBannersHideAll setting is set to true
        $this->setInstallmentBannerConfigFlag('oePayPalBannersHideAll', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->searchFor(self::PRODUCT_ARTNUM);
        $this->assertFalse($this->isElementPresent('paypal-installment-banner-container'));
    }

    /**
     * Test installment banner on checkout page
     *
     * @group paypal_nobuyerlogin
     */
    public function testBannerOnCheckoutPage()
    {
        //checkout page banner off
        $this->openShop(false, true);
        $this->switchLanguage("Deutsch");
        $this->logInUser(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->addToBasket(self::PRODUCT_ID);
        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));

        //checkout page banner off
        $this->setInstallmentBannerConfigFlag('oePayPalBannersCheckoutPage', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->addToBasket(self::PRODUCT_ID);
        $this->checkInstallmentBannerData(round(2 * self::PRODUCT_PRICE, 1));

        //Check banner visibility when oePayPalBannersHideAll setting is set to true
        $this->setInstallmentBannerConfigFlag('oePayPalBannersHideAll', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->addToBasket(self::PRODUCT_ID);
        $this->assertFalse($this->isElementPresent('paypal-installment-banner-container'));
    }

    /**
     * Flow theme frontend login.
     *
     * @param string  $userName     User name (email).
     * @param string  $userPass     User password.
     * @param boolean $waitForLogin If needed to wait until user get logged in.
     *
     */
    private function logInUser($userName, $userPass, $waitForLogin = true)
    {
        $this->click("//div[contains(@class, 'showLogin')]/button");
        $this->waitForItemAppear("loginBox");

        $this->type("loginEmail", $userName);
        $this->type("loginPasword", $userPass);

        $this->clickAndWait("//div[@id='loginBox']/button");

        if ($waitForLogin) {
            $this->waitForTextDisappear('%LOGIN%');
        }
    }

    /**
     * Test installment banner on details page
     *
     * @group paypal_nobuyerlogin
     */
    public function testBannerOnDetailsPage()
    {
        //details page banner off
        $this->openShop(false, true);
        $this->switchLanguage("Deutsch");
        $this->openProductDetailsPage();
        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));

        //details page banner on
        $this->setInstallmentBannerConfigFlag('oePayPalBannersProductDetailsPage', true);
        $this->openProductDetailsPage();
        $this->checkInstallmentBannerData(self::PRODUCT_PRICE);

        //Check banner visibility when oePayPalBannersHideAll setting is set to true
        $this->setInstallmentBannerConfigFlag('oePayPalBannersHideAll', true);
        $this->openProductDetailsPage();
        $this->assertFalse($this->isElementPresent('paypal-installment-banner-container'));
    }

    /**
     * Test helper
     */
    private function openProductDetailsPage()
    {
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->switchLanguage("Deutsch");
        $this->searchFor(self::PRODUCT_ARTNUM);
        $this->clickAndWait(self::translate("//a[contains(., '%MORE_INFO%')]"));
    }

    /**
     * Test installment banner on category page
     *
     * @group paypal_nobuyerlogin
     */
    public function testBannerOnCategoryPage()
    {
        $categoryUrl = $this->getTestConfig()->getShopUrl() . 'en/Kiteboarding/Harnesses/';

        //category page banner off
        $this->openShop(false, true);
        $this->switchLanguage("Deutsch");
        $this->addToBasket(self::PRODUCT_ID, 4);
        $this->open($categoryUrl);
        $this->assertFalse($this->isElementPresent(self::PAYPAL_CONTAINER_ID));

        //category page banner on
        $this->setInstallmentBannerConfigFlag('oePayPalBannersCategoryPage', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->open($categoryUrl);
        $this->checkInstallmentBannerData(round(4 * self::PRODUCT_PRICE, 1));

        //Check banner visibility when oePayPalBannersHideAll setting is set to true
        $this->setInstallmentBannerConfigFlag('oePayPalBannersHideAll', true);
        $this->clickAndWait(self::translate('//a["%HOME%"]'));
        $this->open($categoryUrl);
        $this->assertFalse($this->isElementPresent('paypal-installment-banner-container'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->callShopSC(
            'oxConfig', null, null, [
                'oePayPalClientId'                          => [
                    'type'   => 'str',
                    'value'  => $this->getLoginDataByName('OEPayPalClientId'),
                    'module' => 'module:oepaypal'
                ],
                'iNewBasketItemMessage'                     => [
                    'type'   => 'int',
                    'value'  => 0,
                    'module' => ''
                ],
                'oePayPalBannersHideAll'                    => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersStartPage'                  => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersSearchResultsPage'          => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersCheckoutPage'               => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersProductDetailsPage'         => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersCategoryPage'               => [
                    'type'   => 'bool',
                    'value'  => false,
                    'module' => 'module:oepaypal'
                ],
                'oePayPalBannersStartPageSelector'          => [
                    'type'   => 'str',
                    'value'  => $this->payPalBannersStartPageSelector,
                    'module' => 'theme:flow'
                ],
                'oePayPalBannersSearchResultsPageSelector'  => [
                    'type'   => 'str',
                    'value'  => $this->payPalBannersSearchResultsPageSelector,
                    'module' => 'theme:flow'
                ],
                'oePayPalBannersProductDetailsPageSelector' => [
                    'type'   => 'str',
                    'value'  => $this->payPalBannersProductDetailsPageSelector,
                    'module' => 'theme:flow'
                ],
                'oePayPalBannersPaymentPageSelector'        => [
                    'type'   => 'str',
                    'value'  => $this->payPalBannersPaymentPageSelector,
                    'module' => 'theme:flow'
                ],
                'aCachableClasses'=> [
                    'type'   => 'arr',
                    'value'  => [],
                    'module' => ''
                ],
            ]
        );

        $this->activateTheme($this->theme);
        $this->getTranslator()->setLanguage(0);
    }

    /**
     * Open cart.
     */
    protected function openCart()
    {
        $this->click("//button[@id='minibasketbutton']");
        $this->waitForText('%CHECKOUT%');
        $this->click('%CHECKOUT%');
        $this->waitForPageToLoad();
    }
}
