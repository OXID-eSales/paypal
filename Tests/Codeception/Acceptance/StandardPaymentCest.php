<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Checkout\ThankYou;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\Codeception\Page\Checkout\Basket as BasketCheckout;
use OxidEsales\Codeception\Page\Checkout\PaymentCheckout;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\Codeception\Module\Translation\Translator;

/**
 * Class StandardPaymentCest
 *
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class StandardPaymentCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->updateInDatabase('oxuser', Fixtures::get('adminData'), ['OXUSERNAME' => 'admin']);
        $I->updateInDatabase('oxuser',
            [
                'oxpassword' => '$2y$10$b186f117054b700a89de9uXDzfahkizUucitfPov3C2cwF5eit2M2',
                'oxpasssalt' => 'b186f117054b700a89de929ce90c6aef'
            ],
            [
                'oxusername' => $I->getDemoUserName()
            ]
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_standard
     * @group paypal_checkout
     */
    public function checkoutWithPaypalStandard(AcceptanceTester $I)
    {
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('sOEPayPalTransactionMode', 'Authorization', 'str');
        $I->updateConfigInDatabase('blOEPayPalFinalizeOrderOnPayPal', false, 'bool');

        //log user in
        $homePage = $I->openShop()
            ->loginUser($I->getDemoUserName(), $I->getExistingUserPassword());

        //add Product to basket
        $basket = new Basket($I);
        $basketItem = Fixtures::get('product');
        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);

        $I->amOnPage('/en/cart');
        $basketPage = new BasketCheckout($I);
        $basketPage->goToNextStep()
            ->goToNextStep();

        $I->see(Translator::translate('PAYMENT_METHOD'));

        $paymentPage = new PaymentCheckout($I);
        $paymentPage = $paymentPage->selectPayment('oxidpaypal');
        $I->click($paymentPage->nextStepButton);

        $loginPage = new PayPalLogin($I);
        $paypalUserEmail = Fixtures::get('sBuyerLogin');
        $paypalUserPassword = Fixtures::get('sBuyerPassword');

        $orderCheckout = $loginPage->checkoutWithStandardPayPal($paypalUserEmail, $paypalUserPassword);
        $orderCheckout->submitOrder();

        $thankYouPage = new ThankYou($I);
        $orderNumber = $thankYouPage->grabOrderNumber();

        $I->assertGreaterThan(1, $orderNumber);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_express
     * @group paypal_checkout
     */
    public function checkoutWithPaypalExpress(AcceptanceTester $I)
    {
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('sOEPayPalTransactionMode', 'Authorization', 'str');
        $I->updateConfigInDatabase('blOEPayPalFinalizeOrderOnPayPal', true, 'bool');

        $basket = new Basket($I);

        $basketItem = Fixtures::get('product');

        //add Product to basket
        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount']);
        $I->waitForElementVisible("#paypalExpressCheckoutMiniBasketImage", 10);
        $I->click("#paypalExpressCheckoutMiniBasketImage");

        $loginPage = new PayPalLogin($I);
        $paypalUserEmail = Fixtures::get('sBuyerLogin');
        $paypalUserPassword = Fixtures::get('sBuyerPassword');

        $loginPage->loginAndCheckout($paypalUserEmail, $paypalUserPassword);

        $thankYouPage = new ThankYou($I);
        $orderNumber = $thankYouPage->grabOrderNumber();

        $I->assertGreaterThan(1, $orderNumber);
    }
}
