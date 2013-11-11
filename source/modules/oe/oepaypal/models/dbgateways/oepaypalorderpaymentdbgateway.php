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
 * Order payment db gateway class
 */
class oePayPalOrderPaymentDbGateway extends oePayPalModelDbGateway
{
    /**
     * Save PayPal order payment data to database
     *
     * @param array $aData
     *
     * return int
     */
    public function save( $aData )
    {
        $oDb = $this->_getDb();

        foreach ( $aData as $sField => $sData ) {
            $aSql[] =  '`' . $sField . '` = ' . $oDb->quote( $sData );
        }

        $sSql = 'INSERT INTO `oepaypal_orderpayments` SET ';
        $sSql .= implode( ', ', $aSql );
        $sSql .= ' ON DUPLICATE KEY UPDATE ';
        $sSql .= ' `oepaypal_paymentid`=LAST_INSERT_ID(`oepaypal_paymentid`), ';
        $sSql .= implode( ', ', $aSql );
        $oDb->execute( $sSql );

        $iId = $aData['oepaypal_paymentid'];
        if ( empty( $iId ) ){
            $iId = $oDb->getOne( 'SELECT LAST_INSERT_ID()' );
        }

        return $iId;
    }

    /**
     * Load PayPal order payment data from Db
     *
     * @param string $sPaymentId order id
     *
     * return array
     */
    public function load( $sPaymentId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getRow( 'SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_paymentid` = ' . $oDb->quote( $sPaymentId ) );

        return $aData;
    }

    /**
     * Load PayPal order payment data from Db
     *
     * @param string $sTransactionId order id
     *
     * return array
     */
    public function loadByTransactionId( $sTransactionId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getRow( 'SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_transactionid` = ' . $oDb->quote( $sTransactionId ) );
        return $aData;
    }

    /**
     * Delete PayPal order payment data from database
     *
     * @param string $sPaymentId order id
     *
     * @return bool
     */
    public function delete( $sPaymentId )
    {
        $oDb = $this->_getDb();
        $oDb->startTransaction();

        $blDeleteResult = $oDb->execute( 'DELETE FROM `oepaypal_orderpayments` WHERE `oepaypal_paymentid` = ' . $oDb->quote( $sPaymentId ) );
        $blDeleteCommentResult = $oDb->execute( 'DELETE FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_paymentid` = ' . $oDb->quote( $sPaymentId ) );

        $blResult = ( $blDeleteResult !== false ) || ( $blDeleteCommentResult !== false );

        if ( $blResult ) {
            $oDb->commitTransaction();
        } else {
            $oDb->rollbackTransaction();
        }

        return $blResult;
    }


    /**
     * Load PayPal order payment data from Db
     *
     * @param string $sOrderId order id
     *
     * return array
     */
    public function getList( $sOrderId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getAll( 'SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_orderid` = ' . $oDb->quote( $sOrderId ) . ' ORDER BY `oepaypal_date` DESC'  );
        return $aData;
    }
}