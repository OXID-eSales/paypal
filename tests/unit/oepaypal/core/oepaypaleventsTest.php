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
    protected function setUp()
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

        parent::setUp();
    }

    public function tearDown()
    {
        oxDb::getDb()->execute( 'DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`' );
        oxDb::getDb()->execute( 'DROP TABLE IF EXISTS `oepaypal_orderpayments`' );
        oxDb::getDb()->execute( 'DROP TABLE IF EXISTS `oepaypal_order`' );

        oePayPalEvents::addOrderPaymentsCommentsTable();
        oePayPalEvents::addOrderPaymentsTable();
        oePayPalEvents::addOrderTable();

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
    public function testOnActivateAddMissingFields()
    {
        $this->_createTables();

        oePayPalEvents::onActivate();

        $oDbMetaDataHandler = oxNew( 'oxDbMetaDataHandler' );

        // PayPal order table extends oxOrder table
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_order'));

        // Payment history table created
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_orderpayments'));

        // Payment comments table created
        $this->assertTrue($oDbMetaDataHandler->tableExists('oepaypal_orderpaymentcomments'));

        $aTableFields = array(
            'oepaypal_order' => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpayments' => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpaymentcomments' => 'OEPAYPAL_TIMESTAMP',
        );

        $oDbMetaDataHandler = oxNew('oxDbMetaDataHandler');

        foreach($aTableFields as $sTableName => $sFieldName){
            $this->assertTrue($oDbMetaDataHandler->fieldExists($sFieldName,$sTableName));
        }

        // Payment method exist and enabled
        $oPayment = oxNew( 'oxPayment' );
        $oPayment->load( 'oxidpaypal' );
        $this->assertEquals( 1 , $oPayment->oxpayments__oxactive->value);

        //Enabled PayPal RDFa
        $this->assertTrue( $this->_getPayPalRDFaFromOxObject2PaymentTable() );
    }

    protected function _createTables()
    {
        $sSql = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpayments` (
              `OEPAYPAL_PAYMENTID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `OEPAYPAL_ACTION` enum('capture', 'authorization', 're-authorization', 'refund', 'void') NOT NULL DEFAULT 'capture',
              `OEPAYPAL_ORDERID` char(32) NOT NULL,
              `OEPAYPAL_TRANSACTIONID` varchar(32) NOT NULL,
              `OEPAYPAL_CORRELATIONID` varchar(32) NOT NULL,
              `OEPAYPAL_AMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_CURRENCY` varchar(3) NOT NULL,
              `OEPAYPAL_REFUNDEDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_DATE` datetime NOT NULL,
              `OEPAYPAL_STATUS` varchar(20) NOT NULL,
              PRIMARY KEY (`OEPAYPAL_PAYMENTID`),
              KEY `OEPAYPAL_ORDERID` (`OEPAYPAL_ORDERID`),
              KEY `OEPAYPAL_DATE` (`OEPAYPAL_DATE`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        oxDb::getDb()->execute( $sSql );

        $sSql = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpaymentcomments` (
              `OEPAYPAL_COMMENTID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `OEPAYPAL_PAYMENTID` int(11) unsigned NOT NULL,
              `OEPAYPAL_COMMENT` varchar(256) NOT NULL,
              `OEPAYPAL_DATE` datetime NOT NULL,
              PRIMARY KEY (`OEPAYPAL_COMMENTID`),
              KEY `OEPAYPAL_ORDERID` (`OEPAYPAL_PAYMENTID`),
              KEY `OEPAYPAL_DATE` (`OEPAYPAL_DATE`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        oxDb::getDb()->execute( $sSql );

        $sSql = "CREATE TABLE IF NOT EXISTS `oepaypal_order` (
              `OEPAYPAL_ORDERID` char(32) character set latin1 collate latin1_general_ci NOT NULL,
              `OEPAYPAL_PAYMENTSTATUS` enum('pending','completed','failed','canceled') NOT NULL DEFAULT 'pending',
              `OEPAYPAL_CAPTUREDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_REFUNDEDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_VOIDEDAMOUNT`   decimal(9,2) NOT NULL,
              `OEPAYPAL_TOTALORDERSUM`  decimal(9,2) NOT NULL,
              `OEPAYPAL_CURRENCY` varchar(32) NOT NULL,
              `OEPAYPAL_TRANSACTIONMODE` enum('Sale','Authorization') NOT NULL DEFAULT 'Sale',
              PRIMARY KEY (`OEPAYPAL_ORDERID`),
              KEY `OEPAYPAL_PAYMENTSTATUS` (`OEPAYPAL_PAYMENTSTATUS`)
            ) ENGINE=InnoDB;";

        oxDb::getDb()->execute( $sSql );
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
        oePayPalEvents::enablePayPalRDFA();
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
