<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Tests\Integration\IPNRequestHandler;

use OxidEsales\Eshop\Application\Model\Order;

class IPNHandlerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_order`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`');

        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsCommentsTable();

        parent::setUp();
    }

    public function providerHandleRequest()
    {
        $config = new \OxidEsales\PayPalModule\Core\Config();
        $realShopOwner = $config->getUserEmail();
        $notRealShopOwner = 'some12a1sd5@oxid-esales.com';

        return array(
            // Correct values. Payment status changes to given from PayPal. Order status is calculated from payment.
            array($realShopOwner, array('VERIFIED' => true), true, 'completed', false
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),

            // PayPal do not verifies request. Nothing changes.
            array($realShopOwner, array('Not-VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 1.45, 'USD', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Pending'),
            // Wrong Shop owner from PayPal. Data do not change.
            array($notRealShopOwner, array('VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 121.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Pending'),

            // Wrong amount. Payment status get updated. Payment amount do not change. Order becomes failed.
            array($realShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 121.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),
            // Wrong currency. Payment status get updated. Payment currency do not change. Order becomes failed.
            array($realShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 124.45, 'USD', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),
        );
    }

    /**
     * @dataProvider providerHandleRequest
     */
    public function testHandleRequest($shopOwnerPayPal, $responseFromPayPal, $requestHandledExpected, $OrderStatusAfterRequest, $failureMessageExist
        , $transactionIdPayPal, $paymentAmountPayPal, $paymentCurrencyPayPal, $paymentStatusPayPal
        , $transactionIdShop, $paymentAmountShop, $paymentCurrencyShop, $PaymentStatusAfterRequest)
    {
        $orderId = '__handleRequest_order';

        $this->preparePayPalRequest($shopOwnerPayPal, $paymentStatusPayPal, $transactionIdPayPal, $paymentAmountPayPal, $paymentCurrencyPayPal);

        $order = $this->createPayPalOrder($orderId);
        $this->createOrderPayment($orderId, $transactionIdShop, $paymentAmountShop, $paymentCurrencyShop);

        // Mock curl so we do not call PayPal to check if request originally from PayPal.
        $ipnRequestVerifier = $this->createPayPalResponse($responseFromPayPal);

        $payPalIPNHandler = new \OxidEsales\PayPalModule\Controller\IPNHandler();
        $payPalIPNHandler->setIPNRequestVerifier($ipnRequestVerifier);
        $payPalIPNHandler->handleRequest();

        $logHelper = new \OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper();
        $logData = $logHelper->getLogData();
        $lastLogItem = end($logData);
        $requestHandled = $lastLogItem->data['Result'] == 'true';

        $order->load();
        $payment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $payment->loadByTransactionId($transactionIdShop);
        $this->assertEquals($requestHandledExpected, $requestHandled, 'Request is not handled as expected.');
        $this->assertEquals($PaymentStatusAfterRequest, $payment->getStatus(), 'Status did not change to one returned from PayPal.');
        $this->assertEquals($OrderStatusAfterRequest, $order->getPaymentStatus(), 'Status did not change to one returned from PayPal.');
        $this->assertEquals($paymentAmountShop, $payment->getAmount(), 'Payment amount should not change to get from PayPal.');
        $this->assertEquals($paymentCurrencyShop, $payment->getCurrency(), 'Payment currency should not change to get from PayPal.');
        if (!$failureMessageExist) {
            $this->assertEquals(0, count($payment->getCommentList()), 'There should be no failure comment.');
        } else {
            $comments = $payment->getCommentList();
            $comments = $comments->getArray();
            $comment = $comments[0]->getComment();
            // Failure comment should have all information about request and original payment.
            $commentHasAllInformation = strpos($comment, (string) $paymentAmountPayPal) !== false
                                          && strpos($comment, (string) $paymentCurrencyPayPal) !== false
                                          && strpos($comment, (string) $paymentAmountShop) !== false
                                          && strpos($comment, (string) $paymentCurrencyShop) !== false;
            $this->assertEquals(1, count($comments), 'There should failure comment.');
            $this->assertTrue($commentHasAllInformation, 'Failure comment should have all information about request and original payment: ' . $comment);
        }
    }

    public function providerHandlingPendingRequest()
    {
        $order = oxNew(Order::class);
        return array(
            array('Completed', $order::OEPAYPAL_TRANSACTION_STATUS_OK),
            array('Pending', $order::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
            array('Failed', $order::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
        );
    }

    /**
     * @param string $payPalResponseStatus PayPal response status. Order transaction status depends on it.
     * @param string $transactionStatus    Order transaction status. Will be checked if as expected.
     *
     * @dataProvider providerHandlingPendingRequest
     */
    public function testHandlingTransactionStatusChange($payPalResponseStatus, $transactionStatus)
    {
        $config = oxNew(\OxidEsales\PayPalModule\Core\Config::class);
        $shopOwner = $config->getUserEmail();
        $transId = '__handleRequest_transaction';
        $orderId = '__handleRequest_order';

        $this->preparePayPalRequest($shopOwner, $payPalResponseStatus, $transId, 0, '');

        $this->createOrder($orderId, \OxidEsales\PayPalModule\Model\Order::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED);
        $this->createPayPalOrder($orderId);
        $this->createOrderPayment($orderId, $transId, 0, '');

        // Mock curl so we do not call PayPal to check if request originally from PayPal.
        $ipnRequestVerifier = $this->createPayPalResponse(array('VERIFIED' => true));

        // Post imitates call from PayPal.
        $payPalIPNHandler = oxNew(\OxidEsales\PayPalModule\Controller\IPNHandler::class);
        $payPalIPNHandler->setIPNRequestVerifier($ipnRequestVerifier);
        $payPalIPNHandler->handleRequest();

        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->load($orderId);
        $this->assertEquals($transactionStatus, $order->getFieldData('oxtransstatus'));
    }

    /**
     * Create order in database by given ID.
     *
     * @param string $orderId
     * @param string $status
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     */
    protected function createOrder($orderId, $status)
    {
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->setId($orderId);
        $order->oxorder__oxtransstatus = new \OxidEsales\Eshop\Core\Field($status);
        $order->save();

        return $order;
    }

    /**
     * Create order in database by given ID.
     *
     * @param string $orderId
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function createPayPalOrder($orderId)
    {
        /** @var \OxidEsales\PayPalModule\Model\PayPalOrder $order */
        $order = oxNew(\OxidEsales\PayPalModule\Model\PayPalOrder::class);
        $order->setOrderId($orderId);
        $order->setPaymentStatus('pending');
        $order->save();

        return $order;
    }

    /**
     * Create order payment related with given order and with specific transaction id.
     *
     * @param string $orderId
     * @param string $transactionId
     * @param double $paymentAmount
     * @param string $paymentCurrency
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    protected function createOrderPayment($orderId, $transactionId, $paymentAmount, $paymentCurrency)
    {
        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->setOrderid($orderId);
        $orderPayment->setTransactionId($transactionId);
        $orderPayment->setAmount($paymentAmount);
        $orderPayment->setCurrency($paymentCurrency);
        $orderPayment->setStatus('Pending');
        $orderPayment->save();

        return $orderPayment;
    }

    /**
     * Communication service do not call PayPal to check if request is from it.
     *
     * @param array $responseFromPayPal
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestVerifier
     */
    protected function createPayPalResponse($responseFromPayPal)
    {
        $curl = $this->getPayPalCommunicationHelper()->getCurl($responseFromPayPal);

        $caller = oxNew(\OxidEsales\PayPalModule\Core\Caller::class);
        $caller->setCurl($curl);

        $communicationService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $communicationService->setCaller($caller);

        $ipnRequestVerifier = oxNew(\OxidEsales\PayPalModule\Model\IPNRequestVerifier::class);
        $ipnRequestVerifier->setCommunicationService($communicationService);

        return $ipnRequestVerifier;
    }

    /**
     * @param $shopOwnerPayPal
     * @param $paymentStatusPayPal
     * @param $transactionId
     * @param $paymentAmountPayPal
     * @param $paymentCurrencyPayPal
     */
    public function preparePayPalRequest($shopOwnerPayPal, $paymentStatusPayPal, $transactionId, $paymentAmountPayPal, $paymentCurrencyPayPal)
    {
        $this->setRequestParameter('receiver_email', $shopOwnerPayPal);
        $this->setRequestParameter('payment_status', $paymentStatusPayPal);
        $this->setRequestParameter('txn_id', $transactionId);
        $this->setRequestParameter('mc_gross', $paymentAmountPayPal);
        $this->setRequestParameter('mc_currency', $paymentCurrencyPayPal);
    }

    /**
     * @return \OxidEsales\PayPalModule\Tests\Integration\Library\CommunicationHelper
     */
    protected function getPayPalCommunicationHelper()
    {
        return oxNew(\OxidEsales\PayPalModule\Tests\Integration\Library\CommunicationHelper::class);
    }
}
