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


/**
 * Testing oePayPalEvents class.
 */
class Unit_oePayPal_core_oePayPalEventsTest extends OxidTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        // Dropping order payments table
        oxDb::getDB()->execute( "DROP TABLE IF EXISTS `oepaypal_orderpayments`");
        oxDb::getDB()->execute( "DROP TABLE IF EXISTS `oepaypal_order`");
        oxDb::getDB()->execute( "DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`");

        // Deleting PayPal payment method
        $oPayment = oxNew( 'oxPayment' );
        $oPayment->load( 'oxidpaypal' );
        $oPayment->delete();

        // Deleting enabled PayPal RDFA
        $sSql = "DELETE FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa'";
        oxDb::getDb()->execute( $sSql );

        parent::tearDown();
    }

    /**
     * oePayPalEvents::onActivate()
     */
    public function testOnActivate()
    {
        oePayPalEvents::onActivate();

        $oDbMetaDataHandler = oxNew( 'oxDbMetaDataHandler' );

        // PayPal order table extends oxOrder table
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_order'));

        // Payment history table created
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_orderpayments'));

        // Payment comments table created
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_orderpaymentcomments'));

        // Payment method exist and enabled
        $oPayment = oxNew( 'oxPayment' );
        $oPayment->load( 'oxidpaypal' );
        $this->assertEquals( 1 , $oPayment->oxpayments__oxactive->value);

        //Enabled PayPal RDFa
        $this->assertTrue( $this->_getPayPalRDFaFromOxObject2PaymentTable() );
    }

    /**
     * oePayPalEvents::onActivate()
     */
    public function testOnDeactivate()
    {
        oePayPalEvents::onActivate();
        oePayPalEvents::onDeactivate();

        $oPayment = oxNew( 'oxPayment' );
        $oPayment->load( 'oxidpaypal' );
        $this->assertEquals( 0 , $oPayment->oxpayments__oxactive->value);

        //Check RDFa
        $this->assertFalse( $this->_getPayPalRDFaFromOxObject2PaymentTable() );
    }

    /**
     * Checks if method inserts additional row.
     */
    public function testEnablePayPalRDFA()
    {
        oePayPalEvents::enablePayPalRDFA();
        $this->assertTrue( $this->_getPayPalRDFaFromOxObject2PaymentTable() );
    }

    /**
     * Checks if method deletes row.
     */
    public function testDisablePayPalRDFA()
    {
        $sInsertSql = "INSERT INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES('oepaypalrdfa', 'oxidpaypal', 'PayPal', 'rdfapayment')";
        oxDb::getDb()->execute( $sInsertSql );
        oePayPalEvents::disablePayPalRDFA();
        $this->assertFalse( $this->_getPayPalRDFaFromOxObject2PaymentTable() );
    }

    /**
     * Check if record for RDF setting is inserted
     *
     * @return mixed
     */
    protected function _getPayPalRDFaFromOxObject2PaymentTable()
    {
        $sSql = "SELECT 1 FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa' LIMIT 1";

        return (bool) oxDb::getDb()->getOne( $sSql );
    }
}
