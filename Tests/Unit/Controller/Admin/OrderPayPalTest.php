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

namespace OxidEsales\PayPalModule\Tests\Unit\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;

class OrderPayPalTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Adds order to oepaypal_order and oxorder tables and checks if order is created using current PayPal module.
     * Expected result- true
     */
    public function testIsNewPayPalOrder_True()
    {
        $soxId = '_testOrderId';

        $payPalOrderModel = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $payPalOrderModel->setOrderId($soxId);
        $payPalOrderModel->save();

        $payPalOrderModel->load();

        $mockBuilder = $this->getMockBuilder(Order::class);
        $mockBuilder->setMethods(['getPayPalOrder']);
        $payPalOxOrder = $mockBuilder->getMock();
        $payPalOxOrder->expects($this->any())->method('getPayPalOrder')->will($this->returnValue($payPalOrderModel));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\Admin\OrderController::class);
        $mockBuilder->setMethods(['getEditObject', 'isPayPalOrder']);
        $payPalOrder = $mockBuilder->getMock();
        $payPalOrder->expects($this->any())->method('getEditObject')->will($this->returnValue($payPalOxOrder));
        $payPalOrder->expects($this->once())->method('isPayPalOrder')->will($this->returnValue(true));

        $this->assertTrue($payPalOrder->isNewPayPalOrder());
    }

    /**
     * Checks if order is created using current PayPal module.
     * Expected result- false
     */
    public function testIsNewPayPalOrder_False()
    {
        $soxId = '_testOrderId';

        $payPalOrderModel = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $payPalOrderModel->setOrderId($soxId);
        $payPalOrderModel->save();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Order::class);
        $mockBuilder->setMethods(['getPayPalOrder']);
        $payPalOxOrder = $mockBuilder->getMock();
        $payPalOxOrder->expects($this->any())->method('getPayPalOrder')->will($this->returnValue($payPalOrderModel));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Controller\Admin\OrderController::class);
        $mockBuilder->setMethods(['getEditObject', 'isPayPalOrder']);
        $payPalOrder = $mockBuilder->getMock();
        $payPalOrder->expects($this->any())->method('getEditObject')->will($this->returnValue($payPalOxOrder));
        $payPalOrder->expects($this->once())->method('isPayPalOrder')->will($this->returnValue(false));

        $this->assertFalse($payPalOrder->isNewPayPalOrder());
    }

    /**
     * Checks if order was made using PayPal payment method.
     * Expected result- true
     */
    public function testIsPayPalOrder_True()
    {
        $payPalOrder = new \OxidEsales\PayPalModule\Controller\Admin\OrderController();
        $soxId = '_testOrderId';

        $session = oxNew(\OxidEsales\Eshop\Core\Session::class);
        $session->setVariable('saved_oxid', $soxId);

        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->setId($soxId);
        $order->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field('oxidpaypal');
        $order->save();

        $this->assertTrue($payPalOrder->isPayPalOrder());
    }

    /**
     * Checks if order was made using PayPal payment method.
     * Expected result- false
     */
    public function testIsPayPalOrder_False()
    {
        $payPalOrder = new \OxidEsales\PayPalModule\Controller\Admin\OrderController();
        $soxId = '_testOrderId';

        $session = oxNew(\OxidEsales\Eshop\Core\Session::class);
        $session->setVariable('saved_oxid', $soxId);

        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->setId($soxId);
        $order->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field('other');
        $order->save();

        $this->assertFalse($payPalOrder->isPayPalOrder());
    }
}
