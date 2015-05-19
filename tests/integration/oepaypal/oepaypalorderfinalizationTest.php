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

class Integration_oePayPal_oePayPalOrderFinalizationTest extends OxidTestCase
{

    public function providerFinalizeOrder_TransStatusNotChange()
    {
        return array(
            array('Pending', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
            array('Failed', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED),
            array('Complete', oePayPalOxOrder::OEPAYPAL_TRANSACTION_STATUS_OK)
        );
    }

    /**
     * After order is finalized and PayPal order status is not 'complete',
     * order transaction status should also stay 'NOT FINISHED'.
     *
     * @dataProvider providerFinalizeOrder_TransStatusNotChange
     *
     * @param string $payPalReturnStatus
     * @param string $transactionStatus
     */
    public function testFinalizeOrder_TransactionStatus($payPalReturnStatus, $transactionStatus)
    {
        $this->getSession()->setVariable('sess_challenge', '_testOrderId');
        $this->getSession()->setVariable('paymentid', 'oxidpaypal');

        /** @var oePayPalOxBasket $oBasket */
        $oBasket = oxNew('oxBasket');

        $paymentGateway = $this->getPaymentGateway($payPalReturnStatus);

        /** @var oePayPalOxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMock('oePayPalOxOrder', array('_getGateway', '_sendOrderByEmail', 'validateOrder'));
        $oOrder->expects($this->any())->method('_getGateway')->will($this->returnValue($paymentGateway));

        $oOrder->setId('_testOrderId');
        $oOrder->finalizeOrder($oBasket, $this->getUser());

        $oUpdatedOrder = oxNew('oxOrder');
        $oUpdatedOrder->load('_testOrderId');
        $this->assertEquals($transactionStatus, $oUpdatedOrder->getFieldData('oxtransstatus'));
        $oUpdatedOrder->delete();
    }

    /**
     * Returns Payment Gateway with mocked PayPal call. Result returns provided return status.
     *
     * @param string $payPalReturnStatus
     *
     * @return oePayPalOxPaymentGateway
     */
    protected function getPaymentGateway($payPalReturnStatus)
    {
        /** @var oePayPalResponseDoExpressCheckoutPayment $result */
        $result = oxNew('oePayPalResponseDoExpressCheckoutPayment');
        $result->setData(array('PAYMENTINFO_0_PAYMENTSTATUS' => $payPalReturnStatus));

        /** @var oePayPalService|PHPUnit_Framework_MockObject_MockObject $oService */
        $oService = $this->getMock('oePayPalService', array('doExpressCheckoutPayment'));
        $oService->expects($this->any())->method('doExpressCheckoutPayment')->will($this->returnValue($result));

        /** @var oePayPalOxPaymentGateway $oPayPalPaymentGateway */
        $oPayPalPaymentGateway = oxNew('oxPaymentGateway');
        $oPayPalPaymentGateway->setPayPalCheckoutService($oService);

        return $oPayPalPaymentGateway;
    }

    /**
     * @return oePayPalOxUser
     */
    protected function getUser()
    {
        /** @var oePayPalOxUser $oUser */
        $oUser = oxNew('oxUser');
        $oUser->load('oxdefaultadmin');

        return $oUser;
    }
}
