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
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use OxidEsales\Codeception\Module\Translation\Translator;

/**
 * @group oepaypal
 * @group oepaypal_standard
 * @group oepaypal_capture_and_refund
 */
class CaptureAndRefundCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * 
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_captureandrefund
     */
    public function orderCaptureAndRefundAmount(AcceptanceTester $I)
    {
        $I->updateConfigInDatabase('sOEPayPalTransactionMode', 'Authorization', 'str');
        $I->updateConfigInDatabase('blOEPayPalFinalizeOrderOnPayPal', true, 'bool');

        $basket = new Basket($I);

        $basketItem = Fixtures::get('product');

        //add Product to basket
        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], (string) $basketItem['amount']);
        $I->waitForElementVisible("#paypalExpressCheckoutMiniBasketImage", 10);
        $I->click("#paypalExpressCheckoutMiniBasketImage");

        $loginPage = new PayPalLogin($I);
        $loginPage->loginAndCheckout($_ENV['sBuyerLogin'], $_ENV['sBuyerPassword']);

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

        $paypalOrder = $I->openAdminOrder((int) $orderNumber);

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
}
