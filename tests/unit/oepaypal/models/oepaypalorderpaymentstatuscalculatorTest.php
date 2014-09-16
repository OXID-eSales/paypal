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

/**
 * Testing oePayPalOxState class.
 */
class Unit_oePayPal_models_oePayPalOrderPaymentStatusCalculatorTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        oePayPalEvents::addOrderPaymentsTable();
        oePayPalEvents::addOrderTable();
        oxDb::getDb()->execute('TRUNCATE TABLE `oepaypal_order`');
        oxDb::getDb()->execute('TRUNCATE TABLE `oepaypal_orderpayments`');
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment()
    {
        $oOrderPaymentValid = $this->getValidOrderPayment();
        $oOrderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            array(null),
            array($oOrderPaymentValid),
            array($oOrderPaymentNotValid),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentPending()
    {
        $oOrderPaymentValid = $this->getValidOrderPayment();
        $oOrderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'pending'),
            // Valid virtual payment do not affect order state.
            array($oOrderPaymentValid, 'pending'),
            // Order failed if virtual payment failed.
            array($oOrderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentCompleted()
    {
        $oOrderPaymentValid = $this->getValidOrderPayment();
        $oOrderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'completed'),
            // Valid virtual payment do not affect order state.
            array($oOrderPaymentValid, 'completed'),
            // Order failed if virtual payment failed.
            array($oOrderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Data provider for testGetSuggestStatus_onVoidNoCapture()
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentCanceled()
    {
        $oOrderPaymentValid = $this->getValidOrderPayment();
        $oOrderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'canceled'),
            // Valid virtual payment do not affect order state.
            array($oOrderPaymentValid, 'canceled'),
            // Order failed if virtual payment failed.
            array($oOrderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentRefund()
    {
        $oOrderPaymentValid = $this->getValidOrderPayment();
        $oOrderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'refund_partial'),
            // Valid virtual payment do not affect order state.
            array($oOrderPaymentValid, 'refund_partial'),
            // Order failed if virtual payment failed.
            array($oOrderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Testing setting order
     */
    public function testSetGetOrder()
    {
        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder('order');
        $this->assertEquals('order', $oManager->getOrder());
    }

    /**
     * Testing setting order payment
     *
     */
    public function testSetGetOrderPayment()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setTransactionId('asdadsd45a4sd5a4sd54a5');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrderPayment($oOrderPayment);

        $this->assertEquals($oOrderPayment, $oManager->getOrderPayment(), 'Order Payment is not same as set.');
    }

    /**
     * Testing setting payment list
     *
     * @dataProvider providerPayment_paymentRefund
     */
    public function testGetSuggestStatus_orderNotSet($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertNull($oManager->getSuggestStatus($sOrderStatus));
    }

    /**
     * Testing setting payment list
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_orderNotSet_orderStatusNull($oOrderPayment)
    {
        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrderPayment($oOrderPayment);
        $this->assertNull($oManager->getStatus());
    }

    /**
     * Testing status if can not be changes automatically from failed
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_OrderStateFailed($oOrderPaymentVirtual)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Pending');
        $oOrderPayment->save();

        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setPaymentStatus('failed');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals('failed', $oManager->getStatus());
    }

    /**
     * Testing status if can not be changes automatically from canceled.
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_OrderStateCanceled($oOrderPaymentVirtual)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Failed');
        $oOrderPayment->save();

        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setPaymentStatus('canceled');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals('canceled', $oManager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status failed
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_paymentFailed_orderFailed($oOrderPaymentVirtual)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Failed');
        $oOrderPayment->save();

        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals('failed', $oManager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status pending
     *
     * @dataProvider providerPayment_paymentPending
     */
    public function testGetStatus_paymentPending($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Pending');
        $oOrderPayment->save();

        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status completed
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetStatus_paymentCompleted($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Completed');
        $oOrderPayment->save();

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Refunded');
        $oOrderPayment->save();

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('order');
        $oOrderPayment->setStatus('Voided');
        $oOrderPayment->save();

        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getStatus());
    }

    /**
     * Testing suggest status on void with no captured money
     *
     * @dataProvider providerPayment_paymentCanceled
     */
    public function testGetSuggestStatus_onVoidNoCapture($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setCapturedAmount(0);

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('void'));
    }

    /**
     * Testing suggest status on void with some captured money
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onVoidSomeCapture($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setCapturedAmount(10);

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('void'));
    }

    /**
     * Testing suggest status on partial refund
     *
     * @dataProvider providerPayment_paymentPending
     */
    public function testGetSuggestStatus_onRefundPartial($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setPaymentStatus('pending');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('refund_partial'));
    }

    /**
     * Testing suggest status on refund
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onRefund($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('refund'));
    }

    /**
     * Testing suggest status on capture
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onCapture($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('capture'));
    }

    /**
     * Testing suggest status on partial capture
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onCapturePartial($oOrderPaymentVirtual, $sOrderStatus)
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $oManager->setOrderPayment($oOrderPaymentVirtual);
        $this->assertEquals($sOrderStatus, $oManager->getSuggestStatus('capture_partial'));
    }

    /**
     * Testing suggest status on partial capture
     */
    public function testGetSuggestStatus_onReauthorize()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('order');
        $oOrder->setPaymentStatus('pending');

        $oManager = new oePayPalOrderPaymentStatusCalculator();
        $oManager->setOrder($oOrder);
        $this->assertEquals('pending', $oManager->getSuggestStatus('reauthorize'));
    }

    /**
     * Prepare OrderPayment object.
     *
     * @return oePayPalOrderPayment
     */
    protected function getOrderPayment()
    {
        oePayPalEvents::addOrderPaymentsTable();
        oePayPalEvents::addOrderTable();

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setTransactionId('asdadsd45a4sd5a4sd54a5');
        $oOrderPayment->setOrderId('order');

        return $oOrderPayment;
    }

    /**
     * Prepare valid OrderPayment object.
     *
     * @return oePayPalOrderPayment
     */
    protected function getValidOrderPayment()
    {
        $oOrderPayment = $this->getOrderPayment();

        return $oOrderPayment;
    }

    /**
     * Prepare not valid OrderPayment object.
     *
     * @return oePayPalOrderPayment
     */
    protected function getNotValidOrderPayment()
    {
        $oOrderPayment = $this->getOrderPayment();
        $oOrderPayment->setIsValid(false);

        return $oOrderPayment;
    }
}
