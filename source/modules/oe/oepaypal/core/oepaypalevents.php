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

if ( !class_exists('oePayPalExtensionChecker') ) {
    require_once getShopBasePath().'modules/oe/oepaypal/core/oepaypalextensionchecker.php';
}

class oePayPalEvents
{
    /**
     * Add additional fields: payment status, captured amount, refunded amount in oxOrder table
     */
    public static function addOrderTableFields()
    {
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
     * Add PayPal payment method set EN and DE long descriptions
     */
    public static function addPaymentMethod()
    {
        $aPaymentDescriptions = array(
            'en' => '<div>When selecting this payment method you are being redirected to PayPal where you can login into your account or open a new account. In PayPal you are able to authorize the payment. As soon you have authorized the payment, you are again redirected to our shop where you can confirm your order.</div> <div style="margin-top: 5px">Only after confirming the order, transfer of money takes place.</div>',
            'de' => '<div>Bei Auswahl der Zahlungsart PayPal werden Sie im nächsten Schritt zu PayPal weitergeleitet. Dort können Sie sich in Ihr PayPal-Konto einloggen oder ein neues PayPal-Konto eröffnen und die Zahlung autorisieren. Sobald Sie Ihre Daten für die Zahlung bestätigt haben, werden Sie automatisch wieder zurück in den Shop geleitet, um die Bestellung abzuschließen.</div> <div style="margin-top: 5px">Erst dann wird die Zahlung ausgeführt.</div>'
        );

        $oPayment = oxNew( 'oxPayment' );
        if( ! $oPayment->load( 'oxidpaypal' ) ) {
            $oPayment->setId( 'oxidpaypal' );
            $oPayment->oxpayments__oxactive = new oxField( 1 );
            $oPayment->oxpayments__oxdesc = new oxField( 'PayPal' );
            $oPayment->oxpayments__oxaddsum = new oxField( 0 );
            $oPayment->oxpayments__oxaddsumtype = new oxField( 'abs' );
            $oPayment->oxpayments__oxfromboni = new oxField( 0 );
            $oPayment->oxpayments__oxfromamount = new oxField( 0 );
            $oPayment->oxpayments__oxtoamount = new oxField( 10000 );

            $oLanguage = oxRegistry::get( 'oxLang' );
            $aLanguages = $oLanguage->getLanguageIds();
            foreach ( $aPaymentDescriptions as $sLanguageAbbreviation => $sDescription ) {
                $iLanguageId = array_search( $sLanguageAbbreviation, $aLanguages);
                if ( $iLanguageId !== false ) {
                    $oPayment->setLanguage( $iLanguageId );
                    $oPayment->oxpayments__oxlongdesc = new oxField( $sDescription  );
                    $oPayment->save();
                }
            }
        }
    }

    /**
     * Check if PayPal is used for sub-shops
     */
    public static function isPayPalActiveOnSubShops()
    {
        $blActive = false;
        $oConfig = oxRegistry::getConfig();
        $oExtensionChecker = oxNew( 'oePayPalExtensionChecker' );
        $aShops = $oConfig->getShopIds();
        $sActiveShopId = $oConfig->getShopId();

        foreach ( $aShops as $sShopId ) {
            if ( $sShopId != $sActiveShopId ) {
                $oExtensionChecker->setShopId( $sShopId );
                $oExtensionChecker->setExtensionId( 'oepaypal' );
                if( $oExtensionChecker->isActive() ) {
                    $blActive = true;
                    break;
                }
            }
        }

        return $blActive;
    }

    /**
     * Disables PayPal payment method
     */
    public static function disablePaymentMethod()
    {
        $oPayment = oxNew( 'oxpayment' );
        $oPayment->load( 'oxidpaypal' );
        $oPayment->oxpayments__oxactive = new oxField( 0 );
        $oPayment->save();
    }

    /**
     * Activates PayPal payment method
     */
    public static function enablePaymentMethod()
    {
        $oPayment = oxNew( 'oxpayment' );
        $oPayment->load( 'oxidpaypal' );
        $oPayment->oxpayments__oxactive = new oxField( 1 );
        $oPayment->save();
    }

    /**
     * Creates Order payments table in to database if not exist
     */
    public static function addOrderPaymentsTable()
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
    }

    /**
     * Creates Order payments Comments table in to database if not exist
     */
    public static function addOrderPaymentsCommentsTable()
    {
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
    }

    /**
     * Enables PayPal RDF
     */
    public static function enablePayPalRDFA()
    {
        // If PayPal activated on other sub shops do not change global RDF setting.
        if ( 'EE' == oxRegistry::getConfig()->getEdition() && self::isPayPalActiveOnSubShops() ) {
            return;
        }

        $sSql = "INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES('oepaypalrdfa', 'oxidpaypal', 'PayPal', 'rdfapayment')";
        oxDb::getDb()->execute( $sSql );
    }

    /**
     * Disable PayPal RDF
     */
    public static function disablePayPalRDFA()
    {
        $sSql = "DELETE FROM `oxobject2payment` WHERE `OXID` = 'oepaypalrdfa'";

        oxDb::getDb()->execute( $sSql );
    }

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        // add additional field to order
        self::addOrderTableFields();

        // create orders payments table
        self::addOrderPaymentsTable();

        // payment comments
        self::addOrderPaymentsCommentsTable();

        // adding record to oxPayment table
        self::addPaymentMethod();

        // enabling PayPal payment method
        self::enablePaymentMethod();

        // enable PayPal RDF
        self::enablePayPalRDFA();
    }

    /**
     * Execute action on deactivate event
     */
    public static function onDeactivate()
    {
        // If PayPal activated on other sub shops do not remove payment method and RDF setting
        if ( 'EE' == oxRegistry::getConfig()->getEdition() && self::isPayPalActiveOnSubShops() ) {
            return;
        }
        self::disablePaymentMethod();
        self::disablePayPalRDFA();
    }
}
