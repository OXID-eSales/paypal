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

if ( !class_exists( 'oePayPalOrder_parent' ) ) {
    class oePayPalOrder_parent extends order
    {
    }
}

/**
 * Testing oePayPaleOrder class.
 */
class Unit_oePayPal_Controllers_oePayPalOrderTest extends OxidTestCase
{
    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        oxDb::getDB()->execute( "delete from oxpayments where OXID = 'oxidpaypal' ");

        parent::tearDown();
    }

    /**
     * Test case for oePayPalOrder::isPayPal()
     *
     * @return null
     */
    public function testIsPayPal()
    {
        $oView = new oePayPalOrder();

        $this->getSession()->setVariable( "paymentid", "oxidpaypal" );
        $this->assertTrue( $oView->isPayPal() );

        $this->getSession()->setVariable( "paymentid", "testPayment" );
        $this->assertFalse( $oView->isPayPal() );
    }


    /**
     * Data provider for getUser test
     *
     * @return array
     */
    public function providerGetUser()
    {
        return array(
            array( 'oxidpaypal', '_testPayPalUser', 'oxdefaultadmin', '_testPayPalUser' ),
            array( 'oxidpaypal', null, 'oxdefaultadmin', 'oxdefaultadmin' ),
            array( 'nonpaypalpayment', '_testPayPalUser', 'oxdefaultadmin', 'oxdefaultadmin' ),
        );
    }

    /**
     * PayPal active, PayPal user is set, PayPal user loaded
     *
     * @dataProvider providerGetUser
     */
    public function testGetUser( $sPaymentId, $sPayPalUserId, $sDefaultUserId, $sExpectedUserId )
    {
        $oUser = new oxUser();
        $oUser->setId($sPayPalUserId);
        $oUser->save();

        $this->getSession()->setVariable( "paymentid", $sPaymentId );
        $this->getSession()->setVariable( "oepaypal-userId", $sPayPalUserId );
        $this->getSession()->setVariable( 'usr', $sDefaultUserId );

        $oOrder = new oePayPalOrder();
        $oUser = $oOrder->getUser();

        $this->assertEquals( $sExpectedUserId, $oUser->oxuser__oxid->value );
    }

    /**
     * PayPal active, PayPal user is set, PayPal user loaded
     *
     * @dataProvider providerGetUser
     */
    public function testGetUser_NonExistingPayPalUser_DefaultUserReturned()
    {
        $this->getSession()->setVariable( "paymentid", 'oxidpaypal' );
        $this->getSession()->setVariable( "oepaypal-userId", 'nonExistingUser' );
        $this->getSession()->setVariable( 'usr', 'oxdefaultadmin' );

        $oOrder = new oePayPalOrder();
        $oUser = $oOrder->getUser();

        $this->assertEquals( 'oxdefaultadmin', $oUser->oxuser__oxid->value );
    }

    /**
     * Test case for oePayPalOrder::getPayment()
     *
     * @return null
     */
    public function testGetPayment()
    {
        $this->getSession()->setVariable( "oepaypal", "0" );

        $oView = new oePayPalOrder();
        $oPayment = $oView->getPayment();
        $this->assertFalse( $oPayment );

        $this->getSession()->setVariable( "paymentid", "oxidpaypal" );

        $sSql = "INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`) VALUES ('oxidpaypal', 1, 'PayPal')";
        oxDb::getDb()->execute( $sSql );

        $oView = new oePayPalOrder();
        $oPayment = $oView->getPayment();

        $this->assertNotNull( $oPayment );
        $this->assertEquals( "oxidpaypal", $oPayment->getId() );
    }
}