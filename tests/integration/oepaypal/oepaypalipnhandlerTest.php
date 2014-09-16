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

require_once realpath(".") . '/unit/OxidTestCase.php';
require_once realpath(".") . '/unit/test_config.inc.php';
require_once realpath(".") . '/integration/lib/oepaypalcommunicationhelper.php';

class Integration_oePayPal_oePayPalIPNHandlerTest extends OxidTestCase
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
    }

    public function providerHandleRequest()
    {
        $oConfig = new oePayPalConfig();
        $sRealShopOwner = $oConfig->getUserEmail();
        $sNotRealShopOwner = 'some12a1sd5@oxid-esales.com';

        return array(
            // Correct values. Payment status changes to given from PayPal. Order status is calculated from payment.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'completed', false
                  , '__handleRequest_transaction', 124.45, 'EUR', 'completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'completed'),
            // PayPal do not verifies request. Nothing changes.
            array($sRealShopOwner, array('Not-VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 1.45, 'USD', 'completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'pending'),
            // Wrong Shop owner from PayPal. Data do not change.
            array($sNotRealShopOwner, array('VERIFIED' => true), false, 'pending', false
                  , '__handleRequest_transaction', 121.45, 'EUR', 'completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'pending'),
            // Wrong amount. Payment status get updated. Payment amount do not change. Order becomes failed.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 121.45, 'EUR', 'completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'completed'),
            // Wrong currency. Payment status get updated. Payment currency do not change. Order becomes failed.
            array($sRealShopOwner, array('VERIFIED' => true), true, 'failed', true
                  , '__handleRequest_transaction', 124.45, 'USD', 'completed'
                  , '__handleRequest_transaction', 124.45, 'EUR', 'completed'),
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

        $aRequestFromPayPal = $this->_preparePayPalRequest($sShopOwnerPayPal, $sPaymentStatusPayPal, $sTransactionIdPayPal, $dPaymentAmountPayPal, $sPaymentCurrencyPayPal);

        $oOrder = $this->_createOrder($sOrderId);
        $this->_createOrderPayment($sOrderId, $sTransactionIdShop, $dPaymentAmountShop, $sPaymentCurrencyShop);

        // Mock curl so we do not call PayPal to check if request originally from PayPal.
        $oIPNRequestVerifier = $this->_preparePayPalResponseMock($aResponseFromPayPal);

        // Post imitates call from PayPal.
        $_POST = $aRequestFromPayPal;
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

    /**
     * Create order in database by given ID.
     *
     * @param string $sOrderId
     *
     * @return oePayPalPayPalOrder
     */
    protected function _createOrder($sOrderId)
    {
        $oOrder = new oePayPalPayPalOrder();
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
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderid($sOrderId);
        $oOrderPayment->setTransactionId($sTransactionId);
        $oOrderPayment->setAmount($dPaymentAmount);
        $oOrderPayment->setCurrency($sPaymentCurrency);
        $oOrderPayment->setStatus('pending');
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
    protected function _preparePayPalResponseMock($aResponseFromPayPal)
    {
        $oCurl = $this->_getPayPalCommunicationHelper()->getCurl($aResponseFromPayPal);

        $oCaller = new oePayPalCaller();
        $oCaller->setCurl($oCurl);

        $oCommunicationService = new oePayPalService();
        $oCommunicationService->setCaller($oCaller);

        $oIPNRequestVerifier = new oePayPalIPNRequestVerifier();
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
        $aRequestFromPayPal = array();
        $aRequestFromPayPal['receiver_email'] = $sShopOwnerPayPal;
        $aRequestFromPayPal['payment_status'] = $sPaymentStatusPayPal;
        $aRequestFromPayPal['txn_id'] = $sTransactionId;
        $aRequestFromPayPal['mc_gross'] = $dPaymentAmountPayPal;
        $aRequestFromPayPal['mc_currency'] = $sPaymentCurrencyPayPal;

        return $aRequestFromPayPal;
    }

    protected function _getPayPalCommunicationHelper()
    {
        return new oePayPalCommunicationHelper();
    }
}