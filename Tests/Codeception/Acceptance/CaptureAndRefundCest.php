<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Checkout\ThankYou;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Admin\PayPalOrder;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;

/**
 * Class CaptureAndRefundCest
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class CaptureAndRefundCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->updateInDatabase('oxuser', Fixtures::get('adminData'), ['OXUSERNAME' => 'admin']);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function orderCaptureAndRefundAmount(AcceptanceTester $I)
    {
        $I->setPayPalSettingsData();
        $I->updateConfigInDatabase('sOEPayPalTransactionMode', 'Authorization');

        $basket = new Basket($I);

        $basketItem = [
            'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
            'title' => 'Kuyichi leather belt JEVER',
            'amount' => 4,
            'price' => '119,60 €'
        ];

        //add Product to basket
        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount']);
        $I->waitForElementVisible("#paypalExpressCheckoutMiniBasketImage", 10);
        $I->click("#paypalExpressCheckoutMiniBasketImage");

        $loginPage = new PayPalLogin($I);
        $paypalUserEmail = Fixtures::get('sBuyerLogin');
        $paypalUserPassword = Fixtures::get('sBuyerPassword');

        $orderPage = $loginPage->loginPayPalUser($paypalUserEmail, $paypalUserPassword);
        $orderPage->submitOrder();

        $thankYouPage = new ThankYou($I);
        $orderNumber = $thankYouPage->grabOrderNumber();

        $adminLoginPage = $I->openAdminLoginPage();
        $adminUser = Fixtures::get('adminUser');
        $adminPanel = $adminLoginPage->login($adminUser['userLoginName'], $adminUser['userPassword']);

        $ordersList = $adminPanel->openOrders($adminPanel);

        $order = [
            'order_number' => (int) $orderNumber,
            'payment_method' => 'PayPal',
        ];

        $ordersList->searchByOrderNumber($order['order_number']);
        $I->click($order['payment_method']);

        $paypalOrder = new PayPalOrder($I);
        $I->waitForElement($paypalOrder->paypalTab, 10);
        $I->click($paypalOrder->paypalTab);
        $I->executeJS("top.oxid.admin.changeEditBar('oepaypalorder_paypal',6);return false;");

        $I->selectEditFrame();
        $paypalOrder->captureAmount();
        $I->dontSee($paypalOrder->captureErrorText, $paypalOrder->errorBox);

        $paypalOrder->refundAmount();
        $I->dontSee($paypalOrder->refundErrorText, $paypalOrder->errorBox);
    }
}
