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

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOxOrderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $sDelete = 'TRUNCATE TABLE `oxorder`';
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($sDelete);

        $this->getSession()->setVariable('sess_challenge', null);
    }

    /**
     * Test case for oePayPalOxOrder::loadPayPalOrder()
     */
    public function testLoadPayPalOrder()
    {
        // creating order
        $oOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $oOrder->setId('_testOrderId');
        $oOrder->save();

        // checking load from session
        $this->getSession()->setVariable('sess_challenge', '_testOrderId');
        $oOrder = new oePayPalOxOrder();
        $oOrder->loadPayPalOrder();
        $this->assertEquals('_testOrderId', $oOrder->oxorder__oxid->value);

        // checking order creation if not exist in session order id
        $this->getSession()->setVariable('sess_challenge', null);
        $oOrder = new oePayPalOxOrder();
        $oOrder->loadPayPalOrder();
        $this->assertTrue((bool) $oOrder->oxorder__oxid->value);
    }

    /**
     * Test case for oePayPalOxOrder::finalizePayPalOrder()
     */
    public function testFinalizePayPalOrder()
    {
        // creating order
        $oOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $oOrder->setId('_testOrderId');
        $oOrder->oxorder__oxtransstatus = new \OxidEsales\Eshop\Core\Field('NOT_FINISHED');
        $oOrder->save();

        /** @var \OxidEsales\Eshop\Application\Model\Basket $oBasket */
        $oBasket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);

        $this->getSession()->setVariable('sess_challenge', '_testOrderId');

        $oOrder = new oePayPalOxOrder();
        $oOrder->loadPayPalOrder();

        $aResult = array(
            'PAYMENTINFO_0_TRANSACTIONID' => '_testTranzactionId'
        );
        $oDetails = new oePayPalResponseDoExpressCheckoutPayment();
        $oDetails->setData($aResult);

        $oOrder->finalizePayPalOrder($oDetails, $oBasket, 'Sale');

        $this->assertEquals('NOT_FINISHED', $oOrder->oxorder__oxtransstatus->value);
        $this->assertEquals('_testTranzactionId', $oOrder->oxorder__oxtransid->value);

        $this->assertEquals('0000-00-00', substr($oOrder->oxorder__oxpaid->value, 0, 10));
    }

    /**
     * Test case for oePayPalOxOrder::finalizePayPalOrder() - when processing order with other payment method
     * (not PayPal), order status should not be changed.
     */
    public function testFinalizeOrder_notPayPalPayment()
    {
        $oTestOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oTestOrder->setId('_testOrderId');
        $oTestOrder->oxorder__oxtransstatus = new \OxidEsales\Eshop\Core\Field("OK");
        $oTestOrder->save();

        $this->getSession()->setVariable('sess_challenge', '_testOrderId');

        /** @var \OxidEsales\Eshop\Application\Model\Basket|PHPUnit_Framework_MockObject_MockObject $oBasket */
        $oBasket = $this->getMock(\OxidEsales\Eshop\Application\Model\Basket::class, array('getPaymentId'));
        $oBasket->expects($this->any())->method('getPaymentId')->will($this->returnValue("anotherPayment"));

        /** @var \OxidEsales\Eshop\Application\Model\User $oUser */
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        /** @var \OxidEsales\Eshop\Application\Model\Order $oOrder */
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId('_testOrderId');
        $oOrder->finalizeOrder($oBasket, $oUser);

        $oUpdatedOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $oUpdatedOrder->load('_testOrderId');
        $this->assertEquals("OK", $oUpdatedOrder->oxorder__oxtransstatus->value);
    }

    /**
     * Test case for oePayPalOxOrder::deletePayPalOrder()
     */
    public function testDeletePayPalOrder()
    {
        $oTestOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $oTestOrder->setId('_testOrderId');
        $oTestOrder->save();

        $this->getSession()->setVariable('sess_challenge', '_testOrderId');

        $oOrder = new oePayPalOxOrder();
        $oOrder->deletePayPalOrder();

        $oUpdatedOrder = new \OxidEsales\Eshop\Application\Model\Order();
        $this->assertFalse($oUpdatedOrder->load('_testOrderId'));
    }

    /**
     * Tests getAuthorizationId
     */
    public function testGetAuthorizationId()
    {
        $oTestOrder = new oePayPalOxOrder();
        $oTestOrder->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field('testAuthorizationId');

        $this->assertEquals('testAuthorizationId', $oTestOrder->getAuthorizationId());
    }

    /**
     *
     */
    public function testValidateDelivery_EmptyPaymentValid_PaymentValid()
    {
        $aBasketMethods = array(
            'getPaymentId'  => 'oxidpaypal',
            'getShippingId' => 'oxidstandard',
        );
        $oBasket = $this->createStub('oePayPalOxBasket', $aBasketMethods);

        $oEmptyPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oEmptyPayment->load('oxempty');
        $oEmptyPayment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $oEmptyPayment->save();

        $deliverySetList = $this->getMock(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, array('getDeliverySetList'));
        $deliverySetList->expects($this->any())->method('getDeliveryList')->will($this->returnValue(array()));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, $deliverySetList);

        /** @var oePayPalOxUser $oUser */
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        $oOrder = new oePayPalOxOrder();
        $oOrder->setUser($oUser);

        $this->assertNull($oOrder->validateDelivery($oBasket));
    }

    /**
     * Asserts that order is updated
     *
     */
    public function testUpdateOrderNumber()
    {
        $oOrder = new oePayPalOxOrder();
        $oOrder->oxorder__oxid = new \OxidEsales\Eshop\Core\Field('_test_order');
        $oOrder->save();
        $this->assertTrue($oOrder->oePayPalUpdateOrderNumber());
    }

    /**
     * Asserts that number is set next than existing one
     */
    public function testUpdateOrderNumber_OrderNumberNotSet()
    {
        $sCounterIdent = 'orderTestCounter';
        $oOrder = $this->getMock('oePayPalOxOrder', array('_getCounterIdent'));
        $oOrder->expects($this->any())->method('_getCounterIdent')->will($this->returnValue($sCounterIdent));
        $oOrder->oxorder__oxid = new \OxidEsales\Eshop\Core\Field('_test_order');
        $oOrder->save();

        $oCounter = new \OxidEsales\Eshop\Core\Counter();
        $iOrderNumber = $oCounter->getNext($sCounterIdent);

        $oOrder->oePayPalUpdateOrderNumber();

        $this->assertEquals($iOrderNumber + 1, $oOrder->oxorder__oxordernr->value);
    }

    /**
     *
     */
    public function testUpdateOrderNumber_OrderNumberSet()
    {
        $sCounterIdent = 'orderTestCounter';
        $oOrder = $this->getMock('oePayPalOxOrder', array('_getCounterIdent'));
        $oOrder->expects($this->any())->method('_getCounterIdent')->will($this->returnValue($sCounterIdent));

        $oCounter = new \OxidEsales\Eshop\Core\Counter();
        $oCounter->getNext($sCounterIdent);

        $oOrder->oxorder__oxordernr = new \OxidEsales\Eshop\Core\Field(5);
        $oOrder->oePayPalUpdateOrderNumber();

        $this->assertEquals(5, $oOrder->oxorder__oxordernr->value);
    }
}
