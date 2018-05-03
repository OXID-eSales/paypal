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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oePayPalOxState class.
 */
class OrderPaymentStatusCalculatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderTable();
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oepaypal_order`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oepaypal_orderpayments`');
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment()
    {
        $orderPaymentValid = $this->getValidOrderPayment();
        $orderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            array(null),
            array($orderPaymentValid),
            array($orderPaymentNotValid),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentPending()
    {
        $orderPaymentValid = $this->getValidOrderPayment();
        $orderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'pending'),
            // Valid virtual payment do not affect order state.
            array($orderPaymentValid, 'pending'),
            // Order failed if virtual payment failed.
            array($orderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentCompleted()
    {
        $orderPaymentValid = $this->getValidOrderPayment();
        $orderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'completed'),
            // Valid virtual payment do not affect order state.
            array($orderPaymentValid, 'completed'),
            // Order failed if virtual payment failed.
            array($orderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Data provider for testGetSuggestStatus_onVoidNoCapture()
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentCanceled()
    {
        $orderPaymentValid = $this->getValidOrderPayment();
        $orderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'canceled'),
            // Valid virtual payment do not affect order state.
            array($orderPaymentValid, 'canceled'),
            // Order failed if virtual payment failed.
            array($orderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Provides with valid and not valid order payments to be set.
     */
    public function providerPayment_paymentRefund()
    {
        $orderPaymentValid = $this->getValidOrderPayment();
        $orderPaymentNotValid = $this->getNotValidOrderPayment();

        return array(
            // Order state calculation do not change if there is no virtual payment.
            array(null, 'refund_partial'),
            // Valid virtual payment do not affect order state.
            array($orderPaymentValid, 'refund_partial'),
            // Order failed if virtual payment failed.
            array($orderPaymentNotValid, 'failed'),
        );
    }

    /**
     * Testing setting order
     */
    public function testSetGetOrder()
    {
        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder('order');
        $this->assertEquals('order', $manager->getOrder());
    }

    /**
     * Testing setting order payment
     *
     */
    public function testSetGetOrderPayment()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setTransactionId('asdadsd45a4sd5a4sd54a5');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrderPayment($orderPayment);

        $this->assertEquals($orderPayment, $manager->getOrderPayment(), 'Order Payment is not same as set.');
    }

    /**
     * Testing setting payment list
     *
     * @dataProvider providerPayment_paymentRefund
     */
    public function testGetSuggestStatus_orderNotSet($orderPaymentVirtual, $orderStatus)
    {
        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertNull($manager->getSuggestStatus($orderStatus));
    }

    /**
     * Testing setting payment list
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_orderNotSet_orderStatusNull($orderPayment)
    {
        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrderPayment($orderPayment);
        $this->assertNull($manager->getStatus());
    }

    /**
     * Testing status if can not be changes automatically from failed
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_OrderStateFailed($orderPaymentVirtual)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Pending');
        $orderPayment->save();

        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setPaymentStatus('failed');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals('failed', $manager->getStatus());
    }

    /**
     * Testing status if can not be changes automatically from canceled.
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_OrderStateCanceled($orderPaymentVirtual)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Failed');
        $orderPayment->save();

        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setPaymentStatus('canceled');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals('canceled', $manager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status failed
     *
     * @dataProvider providerPayment
     */
    public function testGetStatus_paymentFailed_orderFailed($orderPaymentVirtual)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Failed');
        $orderPayment->save();

        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals('failed', $manager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status pending
     *
     * @dataProvider providerPayment_paymentPending
     */
    public function testGetStatus_paymentPending($orderPaymentVirtual, $orderStatus)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Pending');
        $orderPayment->save();

        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getStatus());
    }

    /**
     * Testing status IPN and order creation - status completed
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetStatus_paymentCompleted($orderPaymentVirtual, $orderStatus)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Completed');
        $orderPayment->save();

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Refunded');
        $orderPayment->save();

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('order');
        $orderPayment->setStatus('Voided');
        $orderPayment->save();

        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getStatus());
    }

    /**
     * Testing suggest status on void with no captured money
     *
     * @dataProvider providerPayment_paymentCanceled
     */
    public function testGetSuggestStatus_onVoidNoCapture($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setCapturedAmount(0);

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('void'));
    }

    /**
     * Testing suggest status on void with some captured money
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onVoidSomeCapture($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setCapturedAmount(10);

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('void'));
    }

    /**
     * Testing suggest status on partial refund
     *
     * @dataProvider providerPayment_paymentPending
     */
    public function testGetSuggestStatus_onRefundPartial($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setPaymentStatus('pending');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('refund_partial'));
    }

    /**
     * Testing suggest status on refund
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onRefund($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('refund'));
    }

    /**
     * Testing suggest status on capture
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onCapture($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('capture'));
    }

    /**
     * Testing suggest status on partial capture
     *
     * @dataProvider providerPayment_paymentCompleted
     */
    public function testGetSuggestStatus_onCapturePartial($orderPaymentVirtual, $orderStatus)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $manager->setOrderPayment($orderPaymentVirtual);
        $this->assertEquals($orderStatus, $manager->getSuggestStatus('capture_partial'));
    }

    /**
     * Testing suggest status on partial capture
     */
    public function testGetSuggestStatus_onReauthorize()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('order');
        $order->setPaymentStatus('pending');

        $manager = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator();
        $manager->setOrder($order);
        $this->assertEquals('pending', $manager->getSuggestStatus('reauthorize'));
    }

    /**
     * Prepare OrderPayment object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function getOrderPayment()
    {
        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderTable();

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setTransactionId('asdadsd45a4sd5a4sd54a5');
        $orderPayment->setOrderId('order');

        return $orderPayment;
    }

    /**
     * Prepare valid OrderPayment object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function getValidOrderPayment()
    {
        $orderPayment = $this->getOrderPayment();

        return $orderPayment;
    }

    /**
     * Prepare not valid OrderPayment object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function getNotValidOrderPayment()
    {
        $orderPayment = $this->getOrderPayment();
        $orderPayment->setIsValid(false);

        return $orderPayment;
    }
}
