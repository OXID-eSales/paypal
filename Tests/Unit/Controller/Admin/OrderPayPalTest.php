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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Controller\Admin;

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

        $oPayPalOrderModel = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $oPayPalOrderModel->setOrderId($soxId);
        $oPayPalOrderModel->save();

        $oPayPalOrderModel->load();

        $oPayPalOxOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array('getPayPalOrder'));
        $oPayPalOxOrder->expects($this->any())->method('getPayPalOrder')->will($this->returnValue($oPayPalOrderModel));

        $oPayPalOrder = $this->getMock('oePayPalOrder_PayPal', array('getEditObject', 'isPayPalOrder'));
        $oPayPalOrder->expects($this->any())->method('getEditObject')->will($this->returnValue($oPayPalOxOrder));
        $oPayPalOrder->expects($this->once())->method('isPayPalOrder')->will($this->returnValue(true));

        $this->assertTrue($oPayPalOrder->isNewPayPalOrder());
    }

    /**
     * Checks if order is created using current PayPal module.
     * Expected result- false
     */
    public function testIsNewPayPalOrder_False()
    {
        $soxId = '_testOrderId';

        $oPayPalOrderModel = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $oPayPalOrderModel->setOrderId($soxId);
        $oPayPalOrderModel->save();

        $oPayPalOxOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Order::class, array('getPayPalOrder'));
        $oPayPalOxOrder->expects($this->any())->method('getPayPalOrder')->will($this->returnValue($oPayPalOrderModel));

        $oPayPalOrder = $this->getMock('oePayPalOrder_PayPal', array('getEditObject', 'isPayPalOrder'));
        $oPayPalOrder->expects($this->any())->method('getEditObject')->will($this->returnValue($oPayPalOxOrder));
        $oPayPalOrder->expects($this->once())->method('isPayPalOrder')->will($this->returnValue(false));

        $this->assertFalse($oPayPalOrder->isNewPayPalOrder());
    }

    /**
     * Checks if order was made using PayPal payment method.
     * Expected result- true
     */
    public function testIsPayPalOrder_True()
    {
        $oPayPalOrder = new oePayPalOrder_PayPal();
        $soxId = '_testOrderId';

        $oSession = new \OxidEsales\Eshop\Core\Session();
        $oSession->setVariable('saved_oxid', $soxId);

        $oOrder = new \OxidEsales\Eshop\Application\Model\Order;
        $oOrder->setId($soxId);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field('oxidpaypal');
        $oOrder->save();

        $this->assertTrue($oPayPalOrder->isPayPalOrder());
    }

    /**
     * Checks if order was made using PayPal payment method.
     * Expected result- false
     */
    public function testIsPayPalOrder_False()
    {
        $oPayPalOrder = new oePayPalOrder_PayPal();
        $soxId = '_testOrderId';

        $oSession = new \OxidEsales\Eshop\Core\Session();
        $oSession->setVariable('saved_oxid', $soxId);

        $oOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $oOrder->setId($soxId);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field('other');
        $oOrder->save();

        $this->assertFalse($oPayPalOrder->isPayPalOrder());
    }
}