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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath( '.' ).'/unit/OxidTestCase.php';
require_once realpath( '.' ).'/unit/test_config.inc.php';

if ( ! class_exists( 'oePayPalOxOrder_parent' ) ) {
    class oePayPalOxOrder_parent extends oxOrder {}
}

class Unit_oePayPal_Controllers_Admin_oePayPalDeliverySetMainTest extends OxidTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        oxDb::getDb()->execute( "delete from oxconfig where oxvarname = 'sOEPayPalMECDefaultShippingId' and oxmodule = 'module:oepaypal'" );
    }

    /**
     * Provides with different shipping ids to be set as already stored and result if match current shipment.
     */
    public function providerRender_DefaultShippingSet()
    {
        return array(
            // Same delivery stored as current.
            array( 'standard', true ),
            // Different delivery stored as current.
            array( 'standard2', false ),
        );
    }

    /**
     * Test if checkbox value for PayPal mobile delivery set has correct value.
     * @dataProvider providerRender_DefaultShippingSet
     */
    public function testRender_DefaultShippingSet( $sMobileECDefaultShippingId, $blMarkAsDefaultPaymentExpected )
    {
        $sDeliverySetId = 'standard';

        $sPayPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', $sMobileECDefaultShippingId, null, $sPayPalModuleId );

        $oPayPalDeliverySet_Main = new oePayPalDeliverySet_Main();
        $oPayPalDeliverySet_Main->setEditObjectId( $sDeliverySetId );
        $oPayPalDeliverySet_Main->render();

        $aViewData = $oPayPalDeliverySet_Main->getViewData();
        $this->assertEquals( $blMarkAsDefaultPaymentExpected, $aViewData[ 'blIsPayPalDefaultMobilePayment' ] );
    }

    /**
     */
    public function testSave_SameShippingIdExistsDeliverySetMarkedAsSet_ShippingIdSame()
    {
        $this->setRequestParam( 'isPayPalDefaultMobilePayment', true );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet();
        $sDeliverySetId1 = $oPayPalDeliverySet_Main->getEditObjectId();

        $sPayPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', $sDeliverySetId1, null, $sPayPalModuleId );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet( $sDeliverySetId1 );

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( $sDeliverySetId1, $sMobileECDefaultShippingId );
    }

    /**
     */
    public function testSave_NotSameShippingIdExistsDeliverySetMarkedAsSet_ShippingIdNew()
    {
        $this->setRequestParam( 'isPayPalDefaultMobilePayment', true );

        $sPayPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', 'standard', null, $sPayPalModuleId );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet();
        $sDeliverySetId2 = $oPayPalDeliverySet_Main->getEditObjectId();

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( $sDeliverySetId2, $sMobileECDefaultShippingId );
    }

    /**
     */
    public function testSave_ShippingIdDoNotExistsDeliverySetMarkedAsSet_ShippingIdNew()
    {
        $this->setRequestParam( 'isPayPalDefaultMobilePayment', true );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet();
        $sDeliverySetId2 = $oPayPalDeliverySet_Main->getEditObjectId();

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( $sDeliverySetId2, $sMobileECDefaultShippingId );
    }

    /**
     */
    public function testSave_SameShippingIdExistsDeliverySetNotMarkedAsSet_ShippingIdCleared()
    {
        $oPayPalDeliverySet_Main = $this->_getDeliverySet();
        $sDeliverySetId1 = $oPayPalDeliverySet_Main->getEditObjectId();

        $sPayPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', $sDeliverySetId1, null, $sPayPalModuleId );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet( $sDeliverySetId1 );

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( '', $sMobileECDefaultShippingId );
    }

    /**
     */
    public function testSave_NotSameShippingIdExistsDeliverySetNotMarkedAsSet_ShippingIdSame()
    {
        $sPayPalModuleId = 'module:oepaypal';
        $this->getConfig()->saveShopConfVar( 'string', 'sOEPayPalMECDefaultShippingId', 'standard', null, $sPayPalModuleId );

        $oPayPalDeliverySet_Main = $this->_getDeliverySet();
        $sDeliverySetId2 = $oPayPalDeliverySet_Main->getEditObjectId();

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( 'standard', $sMobileECDefaultShippingId );
    }

    /**
     */
    public function testSave_ShippingIdDoNotExistsDeliverySetNotMarkedAsSet_ShippingIdEmpty()
    {
        $oPayPalDeliverySet_Main = $this->_getDeliverySet();

        $oPayPalConfig = new oePayPalConfig();
        $sMobileECDefaultShippingId = $oPayPalConfig->getMobileECDefaultShippingId();

        $this->assertEquals( '', $sMobileECDefaultShippingId );
    }

    /**
     */
    protected function _getDeliverySet( $sDeliverySetid = -1 )
    {
        $oPayPalDeliverySet_Main = new oePayPalDeliverySet_Main();
        $oPayPalDeliverySet_Main->setEditObjectId( $sDeliverySetid );
        $oPayPalDeliverySet_Main->save();
        return $oPayPalDeliverySet_Main;
    }
}