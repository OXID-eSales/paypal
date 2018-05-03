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

class DeliverySetMainTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function tearDown()
    {
        parent::tearDown();

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("delete from oxconfig where oxvarname = 'sOEPayPalMECDefaultShippingId' and oxmodule = 'module:oepaypal'");
    }

    /**
     * Provides with different shipping ids to be set as already stored and result if match current shipment.
     */
    public function providerRender_DefaultShippingSet()
    {
        return array(
            // Same delivery stored as current.
            array('standard', true),
            // Different delivery stored as current.
            array('standard2', false),
        );
    }

    /**
     * Test if checkbox value for PayPal mobile delivery set has correct value.
     *
     * @dataProvider providerRender_DefaultShippingSet
     */
    public function testRender_DefaultShippingSet($mobileECDefaultShippingId, $markAsDefaultPaymentExpected)
    {
        $deliverySetId = 'standard';

        $payPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', $mobileECDefaultShippingId, null, $payPalModuleId);

        $payPalDeliverySet_Main = oxNew(\OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain::class);
        $payPalDeliverySet_Main->setEditObjectId($deliverySetId);
        $payPalDeliverySet_Main->render();

        $viewData = $payPalDeliverySet_Main->getViewData();
        $this->assertEquals($markAsDefaultPaymentExpected, $viewData['isPayPalDefaultMobilePayment']);
    }

    /**
     */
    public function testSave_SameShippingIdExistsDeliverySetMarkedAsSet_ShippingIdSame()
    {
        $this->setRequestParameter('isPayPalDefaultMobilePayment', true);

        $payPalDeliverySet_Main = $this->getDeliverySet();
        $deliverySetId1 = $payPalDeliverySet_Main->getEditObjectId();

        $payPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', $deliverySetId1, null, $payPalModuleId);

        $payPalDeliverySet_Main = $this->getDeliverySet($deliverySetId1);

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals($deliverySetId1, $mobileECDefaultShippingId);
    }

    /**
     */
    public function testSave_NotSameShippingIdExistsDeliverySetMarkedAsSet_ShippingIdNew()
    {
        $this->setRequestParameter('isPayPalDefaultMobilePayment', true);

        $payPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', 'standard', null, $payPalModuleId);

        $payPalDeliverySet_Main = $this->getDeliverySet();
        $deliverySetId2 = $payPalDeliverySet_Main->getEditObjectId();

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals($deliverySetId2, $mobileECDefaultShippingId);
    }

    /**
     */
    public function testSave_ShippingIdDoNotExistsDeliverySetMarkedAsSet_ShippingIdNew()
    {
        $this->setRequestParameter('isPayPalDefaultMobilePayment', true);

        $payPalDeliverySet_Main = $this->getDeliverySet();
        $deliverySetId2 = $payPalDeliverySet_Main->getEditObjectId();

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals($deliverySetId2, $mobileECDefaultShippingId);
    }

    /**
     */
    public function testSave_SameShippingIdExistsDeliverySetNotMarkedAsSet_ShippingIdCleared()
    {
        $payPalDeliverySet_Main = $this->getDeliverySet();
        $deliverySetId1 = $payPalDeliverySet_Main->getEditObjectId();

        $payPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', $deliverySetId1, null, $payPalModuleId);

        $payPalDeliverySet_Main = $this->getDeliverySet($deliverySetId1);

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals('', $mobileECDefaultShippingId);
    }

    /**
     */
    public function testSave_NotSameShippingIdExistsDeliverySetNotMarkedAsSet_ShippingIdSame()
    {
        $payPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar('string', 'sOEPayPalMECDefaultShippingId', 'standard', null, $payPalModuleId);

        $payPalDeliverySet_Main = $this->getDeliverySet();
        $deliverySetId2 = $payPalDeliverySet_Main->getEditObjectId();

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals('standard', $mobileECDefaultShippingId);
    }

    /**
     */
    public function testSave_ShippingIdDoNotExistsDeliverySetNotMarkedAsSet_ShippingIdEmpty()
    {
        $payPalDeliverySet_Main = $this->getDeliverySet();

        $payPalConfig = new \OxidEsales\PayPalModule\Core\Config();
        $mobileECDefaultShippingId = $payPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals('', $mobileECDefaultShippingId);
    }

    /**
     */
    protected function getDeliverySet($deliverySetid = -1)
    {
        $payPalDeliverySet_Main = oxNew(\OxidEsales\PayPalModule\Controller\Admin\DeliverySetMain::class);
        $payPalDeliverySet_Main->setEditObjectId($deliverySetid);
        $payPalDeliverySet_Main->save();

        return $payPalDeliverySet_Main;
    }
}
