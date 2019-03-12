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

namespace OxidEsales\PayPalModule\Core;

/**
 * Class defines what module does on Shop events.
 */
class Events
{
    /**
     * Add additional fields: payment status, captured amount, refunded amount in oxOrder table
     */
    public static function addOrderTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_order` (
              `OEPAYPAL_ORDERID` char(32) character set latin1 collate latin1_general_ci NOT NULL,
              `OEPAYPAL_PAYMENTSTATUS` enum('pending','completed','failed','canceled') NOT NULL DEFAULT 'pending',
              `OEPAYPAL_CAPTUREDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_REFUNDEDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_VOIDEDAMOUNT`   decimal(9,2) NOT NULL,
              `OEPAYPAL_TOTALORDERSUM`  decimal(9,2) NOT NULL,
              `OEPAYPAL_CURRENCY` varchar(32) NOT NULL,
              `OEPAYPAL_TRANSACTIONMODE` enum('Sale','Authorization') NOT NULL DEFAULT 'Sale',
              `OEPAYPAL_TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`OEPAYPAL_ORDERID`),
              KEY `OEPAYPAL_PAYMENTSTATUS` (`OEPAYPAL_PAYMENTSTATUS`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Add PayPal payment method set EN and DE long descriptions
     */
    public static function addPaymentMethod()
    {
        $paymentDescriptions = array(
            'en' => '<div>When selecting this payment method you are being redirected to PayPal where you can login into your account or open a new account. In PayPal you are able to authorize the payment. As soon you have authorized the payment, you are again redirected to our shop where you can confirm your order.</div> <div style="margin-top: 5px">Only after confirming the order, transfer of money takes place.</div>',
            'de' => '<div>Bei Auswahl der Zahlungsart PayPal werden Sie im nächsten Schritt zu PayPal weitergeleitet. Dort können Sie sich in Ihr PayPal-Konto einloggen oder ein neues PayPal-Konto eröffnen und die Zahlung autorisieren. Sobald Sie Ihre Daten für die Zahlung bestätigt haben, werden Sie automatisch wieder zurück in den Shop geleitet, um die Bestellung abzuschließen.</div> <div style="margin-top: 5px">Erst dann wird die Zahlung ausgeführt.</div>'
        );

        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        if (!$payment->load('oxidpaypal')) {
            $payment->setId('oxidpaypal');
            $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
            $payment->oxpayments__oxdesc = new \OxidEsales\Eshop\Core\Field('PayPal');
            $payment->oxpayments__oxaddsum = new \OxidEsales\Eshop\Core\Field(0);
            $payment->oxpayments__oxaddsumtype = new \OxidEsales\Eshop\Core\Field('abs');
            $payment->oxpayments__oxfromboni = new \OxidEsales\Eshop\Core\Field(0);
            $payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field(0);
            $payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field(10000);

            $language = \OxidEsales\Eshop\Core\Registry::getLang();
            $languages = $language->getLanguageIds();
            foreach ($paymentDescriptions as $languageAbbreviation => $description) {
                $languageId = array_search($languageAbbreviation, $languages);
                if ($languageId !== false) {
                    $payment->setLanguage($languageId);
                    $payment->oxpayments__oxlongdesc = new \OxidEsales\Eshop\Core\Field($description);
                    $payment->save();
                }
            }
        }
    }

    /**
     * Check if PayPal is used for sub-shops.
     *
     * @return bool
     */
    public static function isPayPalActiveOnSubShops()
    {
        $active = false;
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $extensionChecker = oxNew(\OxidEsales\PayPalModule\Core\ExtensionChecker::class);
        $shops = $config->getShopIds();
        $activeShopId = $config->getShopId();

        foreach ($shops as $shopId) {
            if ($shopId != $activeShopId) {
                $extensionChecker->setShopId($shopId);
                $extensionChecker->setExtensionId('oepaypal');
                if ($extensionChecker->isActive()) {
                    $active = true;
                    break;
                }
            }
        }

        return $active;
    }

    /**
     * Disables PayPal payment method
     */
    public static function disablePaymentMethod()
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        if ($payment->load('oxidpaypal')) {
            $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(0);
            $payment->save();
        }
    }

    /**
     * Activates PayPal payment method
     */
    public static function enablePaymentMethod()
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load('oxidpaypal');
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $payment->save();
    }

    /**
     * Creates Order payments table in to database if not exist
     */
    public static function addOrderPaymentsTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpayments` (
              `OEPAYPAL_PAYMENTID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `OEPAYPAL_ACTION` enum('capture', 'authorization', 're-authorization', 'refund', 'void') NOT NULL DEFAULT 'capture',
              `OEPAYPAL_ORDERID` char(32) character set latin1 collate latin1_general_ci NOT NULL,
              `OEPAYPAL_TRANSACTIONID` varchar(32) NOT NULL,
              `OEPAYPAL_CORRELATIONID` varchar(32) NOT NULL,
              `OEPAYPAL_AMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_CURRENCY` varchar(3) NOT NULL,
              `OEPAYPAL_REFUNDEDAMOUNT` decimal(9,2) NOT NULL,
              `OEPAYPAL_DATE` datetime NOT NULL,
              `OEPAYPAL_STATUS` varchar(20) NOT NULL,
              `OEPAYPAL_TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`OEPAYPAL_PAYMENTID`),
              KEY `OEPAYPAL_ORDERID` (`OEPAYPAL_ORDERID`),
              KEY `OEPAYPAL_DATE` (`OEPAYPAL_DATE`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Creates Order payments Comments table in to database if not exist
     */
    public static function addOrderPaymentsCommentsTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS `oepaypal_orderpaymentcomments` (
              `OEPAYPAL_COMMENTID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `OEPAYPAL_PAYMENTID` int(11) unsigned NOT NULL,
              `OEPAYPAL_COMMENT` varchar(256) NOT NULL,
              `OEPAYPAL_DATE` datetime NOT NULL,
              `OEPAYPAL_TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`OEPAYPAL_COMMENTID`),
              KEY `OEPAYPAL_ORDERID` (`OEPAYPAL_PAYMENTID`),
              KEY `OEPAYPAL_DATE` (`OEPAYPAL_DATE`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Enables PayPal RDF
     *
     * @return null
     */
    public static function enablePayPalRDFA()
    {
        // If PayPal activated on other sub shops do not change global RDF setting.
        if ('EE' == \OxidEsales\Eshop\Core\Registry::getConfig()->getEdition() && self::isPayPalActiveOnSubShops()) {
            return;
        }

        $query = "INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES('oepaypalrdfa', 'oxidpaypal', 'PayPal', 'rdfapayment')";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Disable PayPal RDF
     */
    public static function disablePayPalRDFA()
    {
        $query = "DELETE FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa'";

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Add missing field if it activates on old DB
     */
    public static function addMissingFieldsOnUpdate()
    {
        $dbMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);

        $tableFields = array(
            'oepaypal_order'                => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpayments'        => 'OEPAYPAL_TIMESTAMP',
            'oepaypal_orderpaymentcomments' => 'OEPAYPAL_TIMESTAMP',
        );

        foreach ($tableFields as $tableName => $fieldName) {
            if (!$dbMetaDataHandler->fieldExists($fieldName, $tableName)) {
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute(
                    "ALTER TABLE `" . $tableName
                    . "` ADD `" . $fieldName . "` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;"
                );
            }
        }
    }

    /**
     * Update tables and its fields encoding/collation if activated on old DB
     */
    public static function ensureCorrectFieldsEncodingOnUpdate()
    {
        $dbMetaDataHandler = oxNew(\OxidEsales\Eshop\Core\DbMetaDataHandler::class);
        if ($dbMetaDataHandler->tableExists("oepaypal_order")) {
            $query = "ALTER TABLE `oepaypal_order` DEFAULT CHARACTER SET utf8 collate utf8_general_ci;";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

            $query = "ALTER TABLE `oepaypal_orderpaymentcomments` DEFAULT CHARACTER SET utf8 collate utf8_general_ci;";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

            $query = "ALTER TABLE `oepaypal_orderpayments`  DEFAULT CHARACTER SET utf8 collate utf8_general_ci;";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

            $query = "ALTER TABLE `oepaypal_order` 
              MODIFY `OEPAYPAL_CURRENCY` varchar(32) character set utf8 collate utf8_general_ci NOT NULL,
              MODIFY `OEPAYPAL_PAYMENTSTATUS` enum('pending','completed','failed','canceled') CHARACTER SET utf8 collate utf8_general_ci NOT NULL DEFAULT 'pending',
              MODIFY `OEPAYPAL_TRANSACTIONMODE` enum('Sale','Authorization') CHARACTER SET utf8 collate utf8_general_ci NOT NULL DEFAULT 'Sale';";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

            $query = "ALTER TABLE `oepaypal_orderpaymentcomments` 
              MODIFY `OEPAYPAL_COMMENT` varchar(256) character set utf8 collate utf8_general_ci NOT NULL;";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);

            $query = "ALTER TABLE `oepaypal_orderpayments` 
                MODIFY `OEPAYPAL_ACTION` enum('capture','authorization','re-authorization','refund','void') CHARACTER SET utf8 collate utf8_general_ci NOT NULL DEFAULT 'capture',
                MODIFY `OEPAYPAL_TRANSACTIONID` varchar(32) character set utf8 collate utf8_general_ci NOT NULL,
                MODIFY `OEPAYPAL_CORRELATIONID` varchar(32) character set utf8 collate utf8_general_ci NOT NULL,
                MODIFY `OEPAYPAL_CURRENCY` varchar(3) character set utf8 collate utf8_general_ci NOT NULL,
                MODIFY `OEPAYPAL_STATUS` varchar(20) character set utf8 collate utf8_general_ci NOT NULL;";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
        }
    }

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        // add additional field to order
        self::addOrderTable();

        // create orders payments table
        self::addOrderPaymentsTable();

        // payment comments
        self::addOrderPaymentsCommentsTable();

        self::addMissingFieldsOnUpdate();
        self::ensureCorrectFieldsEncodingOnUpdate();

        // adding record to oxPayment table
        self::addPaymentMethod();

        // enabling PayPal payment method
        self::enablePaymentMethod();

        // enable PayPal RDF
        self::enablePayPalRDFA();
    }

    /**
     * Delete the basket object, which is saved in the session, as it is an instance of \OxidEsales\PayPalModule\Model\Basket
     * and it is no longer a valid object after the module has been deactivated.
     */
    public static function deleteSessionBasket()
    {
        \OxidEsales\Eshop\Core\Registry::getSession()->delBasket();
    }

    /**
     * Execute action on deactivate event
     *
     * @return null
     */
    public static function onDeactivate()
    {
        // If PayPal activated on other sub shops do not remove payment method and RDF setting
        if ('EE' == \OxidEsales\Eshop\Core\Registry::getConfig()->getEdition() && self::isPayPalActiveOnSubShops()) {
            return;
        }
        self::disablePaymentMethod();
        self::disablePayPalRDFA();
        self::deleteSessionBasket();
    }
}
