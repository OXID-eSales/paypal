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

/**
 * Order db gateway class
 */
class oePayPalPayPalOrderDbGateway extends oePayPalModelDbGateway
{
    /**
     * Save PayPal order data to database
     *
     * @param array $aData
     *
     * return bool
     */
    public function save( $aData )
    {
        $oDb = $this->_getDb();

        foreach ( $aData as $sField => $sData ) {
            $aSql[] = '`' . $sField . '` = ' . $oDb->quote( $sData );
        }

        $sSql = 'INSERT INTO `oepaypal_order` SET ';
        $sSql .= implode( ', ', $aSql );
        $sSql .= ' ON DUPLICATE KEY UPDATE ';
        $sSql .= ' `oepaypal_orderid`=LAST_INSERT_ID(`oepaypal_orderid`), ';
        $sSql .= implode( ', ', $aSql );

        $oDb->execute( $sSql );

        $iId = $aData['oepaypal_orderid'];
        if ( empty( $iId ) ){
            $iId = $oDb->getOne( 'SELECT LAST_INSERT_ID()' );
        }

        return $iId;
    }

    /**
     * Load PayPal order data from Db
     *
     * @param string $sOrderId order id
     *
     * return array
     */
    public function load( $sOrderId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getRow( 'SELECT * FROM `oepaypal_order` WHERE `oepaypal_orderid` = ' . $oDb->quote( $sOrderId ) );
        return $aData;
    }

    /**
     * Delete PayPal order data from database
     *
     * @param string $sOrderId order id
     *
     * @return bool
     */
    public function delete( $sOrderId )
    {
        $oDb = $this->_getDb();
        $oDb->startTransaction();

        $blDeleteCommentsResult = $oDb->execute('
            DELETE
                `oepaypal_orderpaymentcomments`
            FROM `oepaypal_orderpaymentcomments`
                INNER JOIN `oepaypal_orderpayments` ON `oepaypal_orderpayments`.`oepaypal_paymentid` = `oepaypal_orderpaymentcomments`.`oepaypal_paymentid`
            WHERE `oepaypal_orderpayments`.`oepaypal_orderid` = '. $oDb->quote( $sOrderId )
        );
        $blDeleteOrderPaymentResult = $oDb->execute( 'DELETE FROM `oepaypal_orderpayments` WHERE `oepaypal_orderid` = ' . $oDb->quote( $sOrderId ) );
        $blDeleteOrderResult = $oDb->execute( 'DELETE FROM `oepaypal_order` WHERE `oepaypal_orderid` = ' . $oDb->quote( $sOrderId ) );

        $blResult = ( $blDeleteOrderResult !== false ) || ( $blDeleteOrderPaymentResult !== false ) || ( $blDeleteCommentsResult !== false );

        if ( $blResult ) {
            $oDb->commitTransaction();
        } else {
            $oDb->rollbackTransaction();
        }

        return $blResult;
    }
}