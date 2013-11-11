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

/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOrderPaymentListTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        oxDb::getDb()->execute( 'TRUNCATE `oepaypal_orderpayments`' );
        oxDb::getDb()->execute( 'TRUNCATE `oepaypal_order`' );
    }

    /**
     * Test case for oePayPalOrderPayment::oePayPalOrderPaymentList()
     * Gets PayPal Order Payment history list
     *
     * @return null
     */
    public function testLoadOrderPayments()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setPaymentId( 1 );
        $oOrderPayment->setOrderId( "123" );
        $oOrderPayment->setAmount( 50 );
        $oOrderPayment->setAction( "OEPAYPAL_STATUS_COMPLETED" );
        $oOrderPayment->setDate( "2012-04-13 12:13:15" );
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId( 2 );
        $oOrderPayment->setDate( "2012-02-01" );
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId( 3 );
        $oOrderPayment->setDate( "2012-01-15" );
        $oOrderPayment->save();

        $oOrderPaymentList = new oePayPalOrderPaymentList();
        $oOrderPaymentList->load( "123" );

        $this->assertEquals( 3, count( $oOrderPaymentList ) );

        $i = 1;
        foreach ($oOrderPaymentList as $oOrderPayment ){
            $this->assertEquals( $i++, $oOrderPayment->getPaymentId() );
        }

    }

    /**
     * Test case for oePayPalOrderPayment::hasFailedPayment()
     * Checks if list has failed payments
     *
     * @return null
     */
    public function testHasFailedPayment()
    {
        $oOrderPaymentList = new oePayPalOrderPaymentList();

        $oOrderPaymentList->load( "order" );
        $this->assertFalse( $oOrderPaymentList->hasFailedPayment() );


        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId( "order" );
        $oOrderPayment->setStatus( "Completed" );
        $oOrderPayment->save();

        $oOrderPaymentList->load( "order" );
        $this->assertFalse( $oOrderPaymentList->hasFailedPayment() );

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId( "order" );
        $oOrderPayment->setStatus( "Failed" );
        $oOrderPayment->save();

        $oOrderPaymentList->load( "order" );
        $this->assertTrue( $oOrderPaymentList->hasFailedPayment() );
    }

    /**
     * Test case for oePayPalOrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     *
     * @return null
     */
    public function testHasPendingPayment()
    {
        $oOrderPaymentList = new oePayPalOrderPaymentList();

        $oOrderPaymentList->load( "order" );
        $this->assertFalse( $oOrderPaymentList->hasPendingPayment() );


        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId( "order" );
        $oOrderPayment->setStatus( "Completed" );
        $oOrderPayment->save();

        $oOrderPaymentList->load( "order" );
        $this->assertFalse( $oOrderPaymentList->hasPendingPayment() );

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId( "order" );
        $oOrderPayment->setStatus( "Pending" );
        $oOrderPayment->save();

        $oOrderPaymentList->load( "order" );
        $this->assertTrue( $oOrderPaymentList->hasPendingPayment() );
    }

    /**
     * Test case for oePayPalOrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     *
     * @return null
     */
    public function testAddPayment()
    {
        $oOrderPaymentList = new oePayPalOrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(0, count($oOrderPaymentList) );

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId( "order" );
        $oOrderPayment->save();

        $oOrderPaymentList = new oePayPalOrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(1, count($oOrderPaymentList) );

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setDate('2013-01-12');
        $oOrderPayment->setAction( 'Pending' );

        $oOrderPaymentList->addPayment( $oOrderPayment );

        $oOrderPaymentList = new oePayPalOrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(2, count($oOrderPaymentList) );

    }

}