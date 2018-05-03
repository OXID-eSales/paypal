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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing \OxidEsales\PayPalModule\Core\Events class.
 */
class EventsTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function setUp()
    {
        // Dropping order payments table
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("DROP TABLE IF EXISTS `oepaypal_orderpayments`");
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("DROP TABLE IF EXISTS `oepaypal_order`");
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`");

        // Deleting PayPal payment method
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load('oxidpaypal');
        $payment->delete();

        // Deleting enabled PayPal RDFA
        $query = "DELETE FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa'";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        parent::setUp();
    }

    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_order`');

        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsCommentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderTable();

        parent::tearDown();
    }

    /**
     * \OxidEsales\PayPalModule\Core\Events::onActivate()
     */
    public function testOnActivate()
    {
        \OxidEsales\PayPalModule\Core\Events::onActivate();

        $dbMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        // PayPal order table extends \OxidEsales\Eshop\Application\Model\Order table
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_order'));

        // Payment history table created
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_orderpayments'));

        // Payment comments table created
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_orderpaymentcomments'));

        // Payment method exist and enabled
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load('oxidpaypal');
        $this->assertEquals(1, $payment->oxpayments__oxactive->value);

        //Enabled PayPal RDFa
        $this->assertTrue($this->getPayPalRDFaFromOxObject2PaymentTable());
    }

    /**
     * \OxidEsales\PayPalModule\Core\Events::onActivate()
     */
    public function testOnActivateAddMissingFields()
    {
        $this->createTables();

        \OxidEsales\PayPalModule\Core\Events::onActivate();

        $dbMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        // PayPal order table extends \OxidEsales\Eshop\Application\Model\Order table
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_order'));

        // Payment history table created
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_orderpayments'));

        // Payment comments table created
        $this->assertTrue($dbMetaDataHandler->tableExists('oepaypal_orderpaymentcomments'));

        $tableFields = array(
            'oepaypal_order'                => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpayments'        => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpaymentcomments' => 'OEPAYPAL_TIMESTAMP',
        );

        $dbMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        foreach ($tableFields as $tableName => $fieldName) {
            $this->assertTrue($dbMetaDataHandler->fieldExists($fieldName, $tableName));
        }

        // Payment method exist and enabled
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load('oxidpaypal');
        $this->assertEquals(1, $payment->oxpayments__oxactive->value);

        //Enabled PayPal RDFa
        $this->assertTrue($this->getPayPalRDFaFromOxObject2PaymentTable());
    }

    protected function createTables()
    {
        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpayments` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpaymentcomments` (
              `OEPAYPAL_COMMENTID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `OEPAYPAL_PAYMENTID` int(11) unsigned NOT NULL,
              `OEPAYPAL_COMMENT` varchar(256) NOT NULL,
              `OEPAYPAL_DATE` datetime NOT NULL,
              PRIMARY KEY (`OEPAYPAL_COMMENTID`),
              KEY `OEPAYPAL_ORDERID` (`OEPAYPAL_PAYMENTID`),
              KEY `OEPAYPAL_DATE` (`OEPAYPAL_DATE`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_order` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * \OxidEsales\PayPalModule\Core\Events::onActivate()
     */
    public function testOnDeactivate()
    {
        \OxidEsales\PayPalModule\Core\Events::onActivate();
        \OxidEsales\PayPalModule\Core\Events::onDeactivate();

        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load('oxidpaypal');
        $this->assertEquals(0, $payment->oxpayments__oxactive->value);

        //Check RDFa
        $this->assertFalse($this->getPayPalRDFaFromOxObject2PaymentTable());
    }

    /**
     * Checks if method inserts additional row.
     */
    public function testEnablePayPalRDFA()
    {
        \OxidEsales\PayPalModule\Core\Events::enablePayPalRDFA();
        $this->assertTrue($this->getPayPalRDFaFromOxObject2PaymentTable());
    }

    /**
     * Checks if method deletes row.
     */
    public function testDisablePayPalRDFA()
    {
        \OxidEsales\PayPalModule\Core\Events::enablePayPalRDFA();
        \OxidEsales\PayPalModule\Core\Events::disablePayPalRDFA();
        $this->assertFalse($this->getPayPalRDFaFromOxObject2PaymentTable());
    }

    /**
     * Check if record for RDF setting is inserted
     *
     * @return mixed
     */
    protected function getPayPalRDFaFromOxObject2PaymentTable()
    {
        $query = "SELECT 1 FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa' LIMIT 1";

        return (bool) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($query);
    }
}
