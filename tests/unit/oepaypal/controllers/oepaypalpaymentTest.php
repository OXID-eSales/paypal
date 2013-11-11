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

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';

if ( !class_exists( 'oePayPalPayment_parent' ) ) {
    class oePayPalPayment_parent extends payment {}
}

/**
 * Testing oePayPalPayment class.
 */
class Unit_oePayPal_Controllers_oePayPalPaymentTest extends OxidTestCase
{
    /**
     * Test case for oePayPalPayment::validatePayment()
     * Test validatePayment
     *
     * @return null
     */
    public function testValidatePayment()
    {
        // forcing payment id
        $this->setRequestParam( "paymentid", null );

        $oView = new oePayPalPayment();
        $this->assertNull( $oView->validatePayment() );

        // forcing payment id
        $this->setRequestParam( "paymentid", "oxidpaypal" );
        $this->setRequestParam( "displayCartInPayPal", 1 );

        $oView = new oePayPalPayment();
        $this->assertEquals( "oePayPalStandardDispatcher?fnc=setExpressCheckout&displayCartInPayPal=1", $oView->validatePayment() );
        $this->assertEquals( "oxidpaypal", $this->getSession()->getVariable( "paymentid") );
    }

    /**
     * Test case for oePayPalPayment::validatePayment()
     * Test validatePayment if Order was already checked by paypal
     *
     * @return null
     */
    public function testValidatePaymentIfCheckedByPayPal()
    {
        // forcing payment id
        $this->setRequestParam( "paymentid", "oxidpaypal" );
        $this->setRequestParam( "displayCartInPayPal", 1 );

        $oView = $this->getMock( "oePayPalPayment", array( "isConfirmedByPayPal" ) );
        $oView->expects( $this->once() )->method( "isConfirmedByPayPal" )->will( $this->returnValue( true ) );

        $this->assertNull( $oView->validatePayment() );
        $this->assertNull( $this->getSession()->getVariable( "paymentid" ) );
    }

    /**
     * Test case for oePayPalPayment::isConfirmedByPayPal()
     * Test isConfirmedByPayPal
     *
     * @return null
     */
    public function testIsConfirmedByPayPal()
    {
        // forcing payment id
        $this->setRequestParam( "paymentid", "oxidpaypal" );
        $this->getSession()->setVariable( "oepaypal-basketAmount", 129.00 );

        $oPrice = $this->getMock( "oxPrice", array( "getBruttoPrice" ) );
        $oPrice->expects( $this->once() )->method( "getBruttoPrice" )->will( $this->returnValue( 129.00 ) );

        $oBasket = $this->getMock( "oxBasket", array( "getPrice" ) );
        $oBasket->expects( $this->once() )->method( "getPrice" )->will( $this->returnValue( $oPrice ) );

        $oView = new oePayPalPayment();

        $this->assertTrue( $oView->isConfirmedByPayPal( $oBasket ) );
    }
}