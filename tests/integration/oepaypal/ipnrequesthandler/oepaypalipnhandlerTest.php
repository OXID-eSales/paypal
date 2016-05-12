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
 * @copyright (C) OXID eSales AG 2003-2014
 */

require_once __DIR__ . '/../../lib/oepaypalcommunicationhelper.php';

class Integration_oePayPal_IpnRequestHandler_oePayPalIpnHandlerTest extends OxidTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        oxDb::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_order`');
        oxDb::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpayments`');
        oxDb::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`');

        oePayPalEvents::addOrderPaymentsTable();
        oePayPalEvents::addOrderTable();
        oePayPalEvents::addOrderPaymentsCommentsTable();

        parent::setUp();
    }

    public function providerHandleRequest()
    {
        $oConfig = new oePayPalConfig();
        $sRealShopOwner = $oConfig->getUserEmail();
        $sNotRealShopOwner = 'some12a1sd5@oxid-esales.com';

        return array(
            // Correct values. Payment status changes to given from PayPal. Order status is calculated from payment.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'completed', false
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),

            // PayPal do not verifies request. Nothing changes.
            array($sRealShopOwner, array('Not-VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 1.45, 'USD', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Pending'),
            // Wrong Shop owner from PayPal. Data do not change.
            array($sNotRealShopOwner, array('VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 121.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Pending'),

            // Wrong amount. Payment status get updated. Payment amount do not change. Order becomes failed.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 121.45, 'EUR', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),
            // Wrong currency. Payment status get updated. Payment currency do not change. Order becomes failed.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 124.45, 'USD', 'Completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'Completed'),
        );
    }

    /**
     * @dataProvider providerHandleRequest
     */
    public function testHandleRequest($sShopOwnerPayPal, $aResponseFromPayPal, $blRequestHandledExpected, $OrderStatusAfterRequest, $blFailureMessageExist
        , $sTransactionIdPayPal, $dPaymentAmountPayPal, $sPaymentCurrencyPayPal, $sPaymentStatusPayPal
        , $sTransactionIdShop, $dPaymentAmountShop, $sPaymentCurrencyShop, $PaymentStatusAfterRequest)
    {
        $sOrderId = '__handleRequest_order';

        $this->_preparePayPalRequest($sShopOwnerPayPal, $sPaymentStatusPayPal, $sTransactionIdPayPal, $dPaymentAmountPayPal, $sPaymentCurrencyPayPal);

        $oOrder = $this->_createPayPalOrder($sOrderId);
        $this->_createOrderPayment($sOrderId, $sTransactionIdShop, $dPaymentAmountShop, $sPaymentCurrencyShop);

        // Mock curl so we do not call PayPal to check if request originally from PayPal.
        $oIPNRequestVerifier = $this->_createPayPalResponse($aResponseFromPayPal);

        $oPayPalIPNHandler = new oePayPalIPNHandler();
        $oPayPalIPNHandler->setIPNRequestVerifier($oIPNRequestVerifier);
        $blRequestHandled = $oPayPalIPNHandler->handleRequest();

        $oOrder->load();
        $oPayment = new oePayPalOrderPayment();
        $oPayment->loadByTransactionId($sTransactionIdShop);
        $this->assertEquals($blRequestHandledExpected, $blRequestHandled, 'Request is not handled as expected.');
        $this->assertEquals($PaymentStatusAfterRequest, $oPayment->getStatus(), 'Status did not change to one returned from PayPal.');
        $this->assertEquals($OrderStatusAfterRequest, $oOrder->getPaymentStatus(), 'Status did not change to one returned from PayPal.');
        $this->assertEquals($dPaymentAmountShop, $oPayment->getAmount(), 'Payment amount should not change to get from PayPal.');
        $this->assertEquals($sPaymentCurrencyShop, $oPayment->getCurrency(), 'Payment currency should not change to get from PayPal.');
        if (!$blFailureMessageExist) {
            $this->assertEquals(0, count($oPayment->getCommentList()), 'There should be no failure comment.');
        } else {
            $aComments = $oPayment->getCommentList();
            $aComments = $aComments->getArray();
            $sComment = $aComments[0]->getComment();
            // Failure comment should have all information about request and original payment.
            $blCommentHasAllInformation = strpos($sComment, (string) $dPaymentAmountPayPal) !== false
                                          && strpos($sComment, (string) $sPaymentCurrencyPayPal) !== false
                                          && strpos($sComment, (string) $dPaymentAmountShop) !== false
                                          && strpos($sComment, (string) $sPaymentCurrencyShop) !== false;
            $this->assertEquals(1, count($aComments), 'There should failure comment.');
            $this->assertTrue($blCommentHasAllInformation, 'Failure comment should have all information about request and original payment: ' . $sComment);
        }
    }

    public function providerHandlingPendingRequest()
    {
        return array(
            array('Completed', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_OK),
            array('Pending', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
            array('Failed', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
        );
    }

    /**
     * @param string $sPayPalResponseStatus PayPal response status. Order transaction status depends on it.
     * @param string $sTransactionStatus    Order transaction status. Will be checked if as expected.
     *
     * @dataProvider providerHandlingPendingRequest
     */
    public function testHandlingTransactionStatusChange($sPayPalResponseStatus, $sTransactionStatus)
    {
        $oConfig = oxNew('oePayPalConfig');
        $sShopOwner = $oConfig->getUserEmail();
        $sTransId = '__handleRequest_transaction';
        $sOrderId = '__handleRequest_order';

        $this->_preparePayPalRequest($sShopOwner, $sPayPalResponseStatus, $sTransId, 0, '');

        $this->_createOrder($sOrderId, oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED);
        $this->_createPayPalOrder($sOrderId);
        $this->_createOrderPayment($sOrderId, $sTransId, 0, '');

        // Mock curl so we do not call PayPal to check if request originally from PayPal.
        $oIPNRequestVerifier = $this->_createPayPalResponse(array('VERIFIED' => true));

        // Post imitates call from PayPal.
        $oPayPalIPNHandler = oxNew('oePayPalIPNHandler');
        $oPayPalIPNHandler->setIPNRequestVerifier($oIPNRequestVerifier);
        $oPayPalIPNHandler->handleRequest();

        $oOrder = oxNew('oxOrder');
        $oOrder->load($sOrderId);
        $this->assertEquals($sTransactionStatus, $oOrder->getFieldData('oxtransstatus'));
    }

    /**
     * Create order in database by given ID.
     *
     * @param string $sOrderId
     * @param string $sStatus
     *
     * @return oxOrder
     */
    protected function _createOrder($sOrderId, $sStatus)
    {
        $oOrder = oxNew('oxOrder');
        $oOrder->setId($sOrderId);
        $oOrder->oxorder__oxtransstatus = new oxField($sStatus);
        $oOrder->save();

        return $oOrder;
    }

    /**
     * Create order in database by given ID.
     *
     * @param string $sOrderId
     *
     * @return oePayPalPayPalOrder
     */
    protected function _createPayPalOrder($sOrderId)
    {
        /** @var oePayPalPayPalOrder $oOrder */
        $oOrder = oxNew('oePayPalPayPalOrder');
        $oOrder->setOrderId($sOrderId);
        $oOrder->setPaymentStatus('pending');
        $oOrder->save();

        return $oOrder;
    }

    /**
     * Create order payment related with given order and with specific transaction id.
     *
     * @param string $sOrderId
     * @param string $sTransactionId
     * @param double $dPaymentAmount
     * @param string $sPaymentCurrency
     *
     * @return oePayPalOrderPayment
     */
    protected function _createOrderPayment($sOrderId, $sTransactionId, $dPaymentAmount, $sPaymentCurrency)
    {
        $oOrderPayment = oxNew('oePayPalOrderPayment');
        $oOrderPayment->setOrderid($sOrderId);
        $oOrderPayment->setTransactionId($sTransactionId);
        $oOrderPayment->setAmount($dPaymentAmount);
        $oOrderPayment->setCurrency($sPaymentCurrency);
        $oOrderPayment->setStatus('Pending');
        $oOrderPayment->save();

        return $oOrderPayment;
    }

    /**
     * Communication service do not call PayPal to check if request is from it.
     *
     * @param array $aResponseFromPayPal
     *
     * @return oePayPalIPNRequestVerifier
     */
    protected function _createPayPalResponse($aResponseFromPayPal)
    {
        $oCurl = $this->_getPayPalCommunicationHelper()->getCurl($aResponseFromPayPal);

        $oCaller = oxNew('oePayPalCaller');
        $oCaller->setCurl($oCurl);

        $oCommunicationService = oxNew('oePayPalService');
        $oCommunicationService->setCaller($oCaller);

        $oIPNRequestVerifier = oxNew('oePayPalIPNRequestVerifier');
        $oIPNRequestVerifier->setCommunicationService($oCommunicationService);

        return $oIPNRequestVerifier;
    }

    /**
     * @param $sShopOwnerPayPal
     * @param $sPaymentStatusPayPal
     * @param $sTransactionId
     * @param $dPaymentAmountPayPal
     * @param $sPaymentCurrencyPayPal
     *
     * @return array
     */
    public function _preparePayPalRequest($sShopOwnerPayPal, $sPaymentStatusPayPal, $sTransactionId, $dPaymentAmountPayPal, $sPaymentCurrencyPayPal)
    {
        $this->setRequestParameter('receiver_email', $sShopOwnerPayPal);
        $this->setRequestParameter('payment_status', $sPaymentStatusPayPal);
        $this->setRequestParameter('txn_id', $sTransactionId);
        $this->setRequestParameter('mc_gross', $dPaymentAmountPayPal);
        $this->setRequestParameter('mc_currency', $sPaymentCurrencyPayPal);
    }

    /**
     * @return oePayPalCommunicationHelper
     */
    protected function _getPayPalCommunicationHelper()
    {
        return oxNew('oePayPalCommunicationHelper');
    }
}