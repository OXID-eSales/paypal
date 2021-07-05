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
    private $maxRetries = 20;

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

        $order = [
            'order_number'   => (int) $orderNumber,
            'payment_method' => 'PayPal',
            'capture_amount' => '55,55',
            'capture_type'   => 'NotComplete',
            'refund_amount'  => '49,50',
            'refund_type'    => 'Partial',
        ];

        $paypalOrder = $this->openAdminOrder($I, (int) $orderNumber);

        $paypalOrder->captureAmount($order['capture_amount'], $order['capture_type']);
        $I->dontSee($paypalOrder->captureErrorText, $paypalOrder->errorBox);
        $I->see(Translator::translate('OEPAYPAL_CAPTURE'), $paypalOrder->lastHistoryRowAction);
        $I->see('55.55', $paypalOrder->lastHistoryRowAmount);

        $paypalOrder->refundAmount($order['refund_amount'], $order['refund_type']);
        $I->wait(1);
        $I->dontSee($paypalOrder->refundErrorText, $paypalOrder->errorBox);
        $I->see(Translator::translate('OEPAYPAL_REFUND'), $paypalOrder->lastHistoryRowAction);
        $I->see('49.50', $paypalOrder->lastHistoryRowAmount);
    }

    /**
     * @param AcceptanceTester $I
     * @param int              $orderNumber
     *
     * @return PayPalOrder
     * @throws \Exception
     */
    protected function openAdminOrder(AcceptanceTester $I, int $orderNumber, int $retry = 0)
    {
        if ($retry >= $this->maxRetries) {
            $I->makeScreenshot();
            $I->makeHtmlSnapshot();
            $I->markTestIncomplete('Did not manage to open the PayPal order tab');
        }

        $adminLoginPage = $I->openAdminLoginPage();
        $adminUser = Fixtures::get('adminUser');
        $adminPanel = $adminLoginPage->login($adminUser['userLoginName'], $adminUser['userPassword']);

        $ordersList = $adminPanel->openOrders($adminPanel);

        $ordersList->searchByOrderNumber($orderNumber);
        $I->wait(1);
        if ($I->seePageHasElement('//a[text()="' . $orderNumber . '"]')) {
            $I->retryClick('//a[text()="' . $orderNumber . '"]');
        } elseif ($I->seePageHasElement('//a[text()="PayPal"]')) {
            $I->retryClick('//a[text()="PayPal"]');
        } else {
            $this->openAdminOrder($I, $orderNumber, ++$retry);
        }

        $I->wait(1);
        $paypalOrder = new PayPalOrder($I);
        if (!$I->seePageHasElement($paypalOrder->paypalTab)) {
            $this->openAdminOrder($I, $orderNumber, ++$retry);
        }

        $I->retryClick($paypalOrder->paypalTab);
        $I->selectEditFrame();

        if (!$I->seePageHasElement($paypalOrder->captureButton)) {
            $this->openAdminOrder($I, $orderNumber, ++$retry);
        }

        return $paypalOrder;
    }
}
