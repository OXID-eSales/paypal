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
use OxidEsales\Codeception\Module\Translation\Translator;

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
     * 
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_captureandrefund
     */
    public function orderCaptureAndRefundAmount(AcceptanceTester $I)
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

        $adminLoginPage = $I->openAdminLoginPage();
        $adminUser = Fixtures::get('adminUser');
        $adminPanel = $adminLoginPage->login($adminUser['userLoginName'], $adminUser['userPassword']);

        $ordersList = $adminPanel->openOrders($adminPanel);

        $order = [
            'order_number' => (int) $orderNumber,
            'payment_method' => 'PayPal',
            'capture_amount' => '55,55',
            'capture_type' => 'NotComplete',
            'refund_amount' => '49,50',
            'refund_type' => 'Partial',
        ];

        $ordersList->searchByOrderNumber($order['order_number']);
        $I->click($order['payment_method']);

        $I->selectListFrame();
        $paypalOrder = new PayPalOrder($I);
        $I->waitForElement($paypalOrder->paypalTab, 10);
        $I->click($paypalOrder->paypalTab);
        $I->executeJS("top.oxid.admin.changeEditBar('oepaypalorder_paypal',6);return true;");
        $I->waitForJS("top.oxid.admin.changeEditBar('oepaypalorder_paypal',6);return true;");

        $I->selectEditFrame();
        $paypalOrder->captureAmount($order['capture_amount'], $order['capture_type']);
        $I->dontSee($paypalOrder->captureErrorText, $paypalOrder->errorBox);
        $I->see(Translator::translate('OEPAYPAL_CAPTURE'), $paypalOrder->lastHistoryRowAction);
        $I->see('55.55', $paypalOrder->lastHistoryRowAmount);

        $paypalOrder->refundAmount($order['refund_amount'], $order['refund_type']);
        $I->dontSee($paypalOrder->refundErrorText, $paypalOrder->errorBox);
        $I->see(Translator::translate('OEPAYPAL_REFUND'), $paypalOrder->lastHistoryRowAction);
        $I->see('49.50', $paypalOrder->lastHistoryRowAmount);
    }
}
