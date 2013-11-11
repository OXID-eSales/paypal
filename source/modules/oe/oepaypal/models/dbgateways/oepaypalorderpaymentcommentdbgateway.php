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
 * Order payment comment db gateway class
 */
class oePayPalOrderPaymentCommentDbGateway extends oePayPalModelDbGateway
{
    /**
     * Save PayPal order payment comment data to database
     *
     * @param array $aData
     *
     * return bool
     */
    public function save( $aData )
    {
        $oDb = $this->_getDb();

        foreach ( $aData as $sField => $sData ) {
            $aSql[] =  '`' . $sField . '` = ' . $oDb->quote( $sData );
        }

        $sSql = 'INSERT INTO `oepaypal_orderpaymentcomments` SET ';
        $sSql .= implode( ', ', $aSql );
        $sSql .= ' ON DUPLICATE KEY UPDATE ';
        $sSql .= ' `oepaypal_commentid`=LAST_INSERT_ID(`oepaypal_commentid`), ';
        $sSql .= implode( ', ', $aSql );
        $oDb->execute( $sSql );

        $iCommentId = $aData['oepaypal_commentid'];
        if ( empty( $iCommentId ) ){
            $iCommentId = $oDb->getOne( 'SELECT LAST_INSERT_ID()' );
        }

        return $iCommentId;
    }

    /**
     * Load PayPal order payment comment data from Db
     *
     * @param string $sPaymentId order id
     *
     * return array
     */
    public function getList( $sPaymentId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getAll( 'SELECT * FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_paymentid` = ' . $oDb->quote( $sPaymentId ) . ' ORDER BY `oepaypal_date` DESC' );
        return $aData;
    }

    /**
     * Load PayPal order payment comment data from Db
     *
     * @param string $sCommentId order id
     *
     * return array
     */
    public function load( $sCommentId )
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getRow( 'SELECT * FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_commentid` = ' . $oDb->quote( $sCommentId ) );
        return $aData;
    }

    /**
     * Delete PayPal order payment comment data from database
     *
     * @param string $sCommentId order id
     *
     * @return bool
     */
    public function delete( $sCommentId )
    {
        $oDb = $this->_getDb();
        $oDb->startTransaction();

        $blDeleteResult = $oDb->execute( 'DELETE FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_commentid` = ' . $oDb->quote( $sCommentId ) );

        $blResult = ( $blDeleteResult !== false );

        if ( $blResult ) {
            $oDb->commitTransaction();
        } else {
            $oDb->rollbackTransaction();
        }

        return $blResult;
    }
}